<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\VerificationService;
use App\Services\Verification\IdentityEngineRegistry;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class VerificationServiceController extends Controller
{
    public function __construct(private IdentityEngineRegistry $identityEngines)
    {
    }

    public function index(): View
    {
        $services = VerificationService::query()
            ->withCount('verificationRequests')
            ->orderByDesc('is_active')
            ->orderBy('name')
            ->get();

        return view('admin.services.index', [
            'services' => $services,
            'canManageServices' => auth()->user()?->can('admin.services.manage') ?? false,
            'supportedProviders' => $services
                ->mapWithKeys(fn (VerificationService $service) => [
                    $service->id => collect($this->identityEngines->supportedProvidersForService($service))
                        ->map(fn (string $provider) => [
                            'code' => $provider,
                            'label' => $this->identityEngines->adminLabel($provider),
                            'public' => $this->identityEngines->publicLabel($provider),
                        ])
                        ->values()
                        ->all(),
                ])
                ->all(),
            'responseTemplates' => [
                'auto' => 'Auto',
                'ninSlip' => 'NIN Slip',
                'bvnSlip' => 'BVN Slip',
                'vehicleSlip' => 'Vehicle Slip',
                'report' => 'Detailed Report',
            ],
        ]);
    }

    public function update(Request $request, VerificationService $verificationService): RedirectResponse
    {
        abort_unless($request->user()?->can('admin.services.manage'), 403);
        $supportedProviders = $this->identityEngines->supportedProvidersForService($verificationService);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', 'max:50'],
            'country' => ['required', 'string', 'size:2'],
            'default_price' => ['required', 'numeric', 'min:0'],
            'default_cost' => ['required', 'numeric', 'min:0'],
            'required_fields' => ['nullable', 'string', 'max:500'],
            'is_active' => ['nullable', 'boolean'],
            'primaryEngine' => ['nullable', Rule::in($supportedProviders)],
            'secondaryEngine' => ['nullable', 'different:primaryEngine', Rule::in($supportedProviders)],
            'responseTemplate' => ['required', Rule::in(['auto', 'ninSlip', 'bvnSlip', 'vehicleSlip', 'report'])],
        ]);

        $requiredFields = collect(explode(',', (string) ($validated['required_fields'] ?? '')))
            ->map(fn ($field) => trim($field))
            ->filter()
            ->values()
            ->all();

        $enginePreferences = collect([
            $validated['primaryEngine'] ?? null,
            $validated['secondaryEngine'] ?? null,
        ])->filter()->values()->all();

        $verificationService->update([
            'name' => $validated['name'],
            'type' => strtolower($validated['type']),
            'country' => strtoupper($validated['country']),
            'default_price' => $validated['default_price'],
            'default_cost' => $validated['default_cost'],
            'required_fields' => $requiredFields,
            'is_active' => $request->boolean('is_active'),
            'engine_preferences' => $enginePreferences,
            'response_template' => $validated['responseTemplate'],
        ]);

        AuditLog::create([
            'user_id' => $request->user()->id,
            'action' => 'admin.service.updated',
            'target_type' => VerificationService::class,
            'target_id' => (string) $verificationService->id,
            'meta' => [
                'code' => $verificationService->code,
                'is_active' => $verificationService->is_active,
                'default_price' => $verificationService->default_price,
                'engines' => $enginePreferences,
                'responseTemplate' => $validated['responseTemplate'],
            ],
        ]);

        return redirect()
            ->route('admin.services.index')
            ->with('status', $verificationService->name.' updated successfully.');
    }
}
