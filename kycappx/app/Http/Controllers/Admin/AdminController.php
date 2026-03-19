<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProviderConfig;
use App\Models\User;
use App\Models\VerificationRequest;
use App\Models\VerificationService;
use App\Models\WebhookLog;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Cache;
use Spatie\Permission\Models\Role;

class AdminController extends Controller
{
    public function dashboard(): View
    {
        $metrics = Cache::remember('admin.dashboard.metrics', now()->addMinute(), function () {
            $customerRoleExists = Role::query()->where('name', 'customer')->exists();

            return [
                'users' => User::count(),
                'customers' => $customerRoleExists ? User::role('customer')->count() : 0,
                'verifications' => VerificationRequest::count(),
                'successful_verifications' => VerificationRequest::where('status', 'success')->count(),
                'webhooks' => WebhookLog::count(),
                'failed_webhooks' => WebhookLog::where('processed', false)->count(),
            ];
        });

        return view('admin.dashboard', [
            'metrics' => $metrics,
            'recentCustomers' => User::query()
                ->with(['wallet', 'roles'])
                ->latest()
                ->limit(6)
                ->get(),
            'recentWebhooks' => WebhookLog::query()
                ->latest()
                ->limit(6)
                ->get(),
            'recentVerifications' => VerificationRequest::query()
                ->with(['user', 'service'])
                ->latest()
                ->limit(6)
                ->get(),
        ]);
    }

    public function customers(): View
    {
        return view('admin.customers.index', [
            'customers' => User::query()
                ->with(['wallet', 'roles'])
                ->withCount(['verificationRequests', 'apiKeys'])
                ->latest()
                ->paginate(12),
        ]);
    }

    public function services(): View
    {
        return view('admin.services.index', [
            'services' => VerificationService::query()
                ->withCount('verificationRequests')
                ->orderByDesc('is_active')
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function providers(): View
    {
        $providerHealth = collect([
            [
                'name' => 'Prembly',
                'code' => 'prembly',
                'configured' => filled(config('services.prembly.app_id')) && filled(config('services.prembly.secret_key')),
                'base_url' => config('services.prembly.base_url'),
            ],
            [
                'name' => 'Youverify',
                'code' => 'youverify',
                'configured' => filled(config('services.youverify.token')),
                'base_url' => config('services.youverify.base_url'),
            ],
            [
                'name' => 'Kora',
                'code' => 'kora',
                'configured' => filled(config('services.kora.secret_key')) && filled(config('services.kora.redirect_url')),
                'base_url' => config('services.kora.base_url'),
            ],
        ]);

        return view('admin.providers.index', [
            'providerHealth' => $providerHealth,
            'providerConfigs' => ProviderConfig::query()
                ->orderBy('priority')
                ->get(),
        ]);
    }

    public function webhookLogs(): View
    {
        return view('admin.logs.webhooks', [
            'logs' => WebhookLog::query()
                ->latest()
                ->paginate(15),
        ]);
    }

    public function verificationLogs(): View
    {
        return view('admin.logs.verifications', [
            'verifications' => VerificationRequest::query()
                ->with(['user', 'service'])
                ->latest()
                ->paginate(15),
        ]);
    }
}
