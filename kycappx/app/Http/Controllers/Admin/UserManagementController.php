<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use App\Services\Billing\WalletService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Spatie\Permission\Models\Role;

class UserManagementController extends Controller
{
    public function __construct(private WalletService $walletService)
    {
    }

    public function index(Request $request): View
    {
        $filters = [
            'search' => trim((string) $request->query('search')),
            'status' => $request->query('status', 'all'),
            'role' => $request->query('role', 'all'),
        ];

        $users = User::query()
            ->with(['wallet', 'roles', 'dedicatedVirtualAccounts', 'socialAccounts'])
            ->withCount(['verificationRequests', 'apiKeys'])
            ->when($filters['search'] !== '', function ($query) use ($filters) {
                $query->where(function ($inner) use ($filters) {
                    $inner
                        ->where('name', 'like', '%'.$filters['search'].'%')
                        ->orWhere('username', 'like', '%'.$filters['search'].'%')
                        ->orWhere('email', 'like', '%'.$filters['search'].'%');
                });
            })
            ->when(
                in_array($filters['status'], ['active', 'suspended', 'pending'], true),
                fn ($query) => $query->where('status', $filters['status'])
            )
            ->when(
                $filters['role'] !== 'all' && Role::query()->where('name', $filters['role'])->exists(),
                fn ($query) => $query->role($filters['role'])
            )
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('admin.customers.index', [
            'customers' => $users,
            'filters' => $filters,
            'roles' => Role::query()->orderBy('name')->get(),
        ]);
    }

    public function edit(User $user): View
    {
        $wallet = $this->walletService->ensureWallet($user->id);

        return view('admin.customers.edit', [
            'customer' => $user->load(['roles', 'wallet', 'socialAccounts', 'dedicatedVirtualAccounts']),
            'wallet' => $wallet,
            'roles' => Role::query()->orderBy('name')->get(),
            'recentApiKeys' => $user->apiKeys()->latest()->limit(8)->get(),
            'recentVerifications' => $user->verificationRequests()->with('service')->latest()->limit(6)->get(),
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => [
                'required',
                'string',
                'min:3',
                'max:40',
                'regex:/^[A-Za-z0-9._-]+$/',
                Rule::unique('users', 'username')->ignore($user->id),
            ],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:30'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'timezone' => ['required', 'timezone'],
            'status' => ['required', Rule::in(['active', 'suspended', 'pending'])],
            'theme_preference' => ['required', Rule::in(['light', 'dark', 'system'])],
            'preferred_funding_provider' => ['nullable', Rule::in(['paystack', 'kora', 'squad'])],
            'service_discount_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'roles' => ['nullable', 'array'],
            'roles.*' => ['string', Rule::exists('roles', 'name')],
            'wallet_status' => ['required', Rule::in(['active', 'frozen', 'closed'])],
            'wallet_adjustment' => ['nullable', 'numeric', 'between:-100000000,100000000'],
            'wallet_adjustment_note' => ['nullable', 'string', 'max:255'],
            'password' => ['nullable', 'string', 'confirmed', 'min:8'],
            'settings' => ['nullable', 'array'],
            'settings.security_alerts' => ['nullable', 'boolean'],
            'settings.monthly_reports' => ['nullable', 'boolean'],
            'settings.marketing_emails' => ['nullable', 'boolean'],
            'deactivate_api_keys' => ['nullable', 'boolean'],
            'reset_two_factor' => ['nullable', 'boolean'],
        ]);

        $selectedRoles = collect($validated['roles'] ?? [])->values();
        if ($selectedRoles->isEmpty()) {
            Role::findOrCreate('customer');
            $selectedRoles = collect(['customer']);
        }

        $this->guardCriticalAdminChanges($request->user(), $user, $validated['status'], $selectedRoles->all());

        $settings = [
            'security_alerts' => (bool) data_get($validated, 'settings.security_alerts', false),
            'monthly_reports' => (bool) data_get($validated, 'settings.monthly_reports', false),
            'marketing_emails' => (bool) data_get($validated, 'settings.marketing_emails', false),
        ];

        try {
            DB::transaction(function () use ($request, $user, $validated, $selectedRoles, $settings) {
                $user->fill([
                    'name' => $validated['name'],
                    'username' => Str::lower($validated['username']),
                    'email' => Str::lower($validated['email']),
                    'phone' => $validated['phone'] ?? null,
                    'company_name' => $validated['company_name'] ?? null,
                    'timezone' => $validated['timezone'],
                    'status' => $validated['status'],
                    'theme_preference' => $validated['theme_preference'],
                    'preferred_funding_provider' => $validated['preferred_funding_provider'] ?? null,
                    'service_discount_rate' => (float) ($validated['service_discount_rate'] ?? 0),
                    'settings' => $settings,
                ]);

                if (! empty($validated['password'])) {
                    $user->password = Hash::make($validated['password']);
                }

                if ($user->isDirty('email')) {
                    $user->email_verified_at = null;
                }

                $user->save();
                $user->syncRoles($selectedRoles->all());

                $wallet = $this->walletService->ensureWallet($user->id);
                $wallet->update(['status' => $validated['wallet_status']]);

                $adjustment = round((float) ($validated['wallet_adjustment'] ?? 0), 2);
                if ($adjustment !== 0.0) {
                    $reference = 'ADMIN_ADJ_'.Str::upper(Str::random(12));
                    $note = $validated['wallet_adjustment_note'] ?? 'Manual wallet adjustment from admin workspace.';

                    if ($adjustment > 0) {
                        $this->walletService->credit($user->id, $adjustment, $reference, 'admin_adjustment', $note, [
                            'actor_id' => $request->user()->id,
                        ]);
                    } else {
                        $this->walletService->debit($user->id, abs($adjustment), $reference, 'admin_adjustment', $note, [
                            'actor_id' => $request->user()->id,
                        ]);
                    }
                }

                if ($request->boolean('deactivate_api_keys')) {
                    $user->apiKeys()->where('is_active', true)->update(['is_active' => false]);
                }

                if ($request->boolean('reset_two_factor')) {
                    $user->forceFill([
                        'two_factor_secret' => null,
                        'two_factor_recovery_codes' => null,
                        'two_factor_confirmed_at' => null,
                    ])->save();
                }

                AuditLog::create([
                    'user_id' => $request->user()->id,
                    'action' => 'admin.user.updated',
                    'target_type' => User::class,
                    'target_id' => (string) $user->id,
                    'meta' => [
                        'roles' => $selectedRoles->all(),
                        'status' => $validated['status'],
                        'wallet_status' => $validated['wallet_status'],
                        'deactivated_api_keys' => $request->boolean('deactivate_api_keys'),
                        'reset_two_factor' => $request->boolean('reset_two_factor'),
                    ],
                ]);
            });
        } catch (RuntimeException $exception) {
            throw ValidationException::withMessages([
                'wallet_adjustment' => $exception->getMessage(),
            ]);
        }

        return redirect()
            ->route('admin.users.edit', $user)
            ->with('status', 'User account, access, and settings updated.');
    }

    private function guardCriticalAdminChanges(User $actor, User $target, string $status, array $roles): void
    {
        $adminRoles = ['super-admin', 'admin', 'support'];
        $keepsAdminAccess = collect($roles)->intersect($adminRoles)->isNotEmpty();

        if ($actor->is($target) && $status !== 'active') {
            throw ValidationException::withMessages([
                'status' => 'You cannot suspend your own account from the admin workspace.',
            ]);
        }

        if ($actor->is($target) && ! $keepsAdminAccess) {
            throw ValidationException::withMessages([
                'roles' => 'You cannot remove your own admin access.',
            ]);
        }

        if (
            $target->hasRole('super-admin')
            && ! in_array('super-admin', $roles, true)
            && User::role('super-admin')->count() <= 1
        ) {
            throw ValidationException::withMessages([
                'roles' => 'At least one super-admin must remain assigned.',
            ]);
        }
    }
}
