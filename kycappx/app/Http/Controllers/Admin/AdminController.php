<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\ProviderConfig;
use App\Models\Wallet;
use App\Models\User;
use App\Models\VerificationRequest;
use App\Models\VerificationService;
use App\Models\WebhookLog;
use App\Services\SiteSettings;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Cache;
use Spatie\Permission\Models\Role;

class AdminController extends Controller
{
    public function __construct(private SiteSettings $siteSettings)
    {
    }

    public function dashboard(): View
    {
        $metrics = Cache::remember('admin.dashboard.metrics', now()->addMinute(), function () {
            $customerRoleExists = Role::query()->where('name', 'customer')->exists();
            $adminCount = User::query()
                ->whereHas('roles', fn ($query) => $query->whereIn('name', ['super-admin', 'admin', 'support']))
                ->count();

            return [
                'users' => User::count(),
                'customers' => $customerRoleExists ? User::role('customer')->count() : 0,
                'admins' => $adminCount,
                'verifications' => VerificationRequest::count(),
                'pending_verifications' => VerificationRequest::whereIn('status', ['pending', 'processing', 'manual_review'])->count(),
                'successful_verifications' => VerificationRequest::where('status', 'success')->count(),
                'webhooks' => WebhookLog::count(),
                'failed_webhooks' => WebhookLog::where('processed', false)->count(),
                'active_services' => VerificationService::query()->where('is_active', true)->count(),
                'active_providers' => ProviderConfig::query()->where('is_active', true)->count(),
                'wallet_balance' => (float) Wallet::query()->sum('balance'),
            ];
        });

        return view('admin.dashboard', [
            'metrics' => $metrics,
            'siteSettingsSnapshot' => $this->siteSettings->current(),
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
            'recentAdminActions' => AuditLog::query()
                ->with('user')
                ->latest()
                ->limit(8)
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

    public function auditLogs(): View
    {
        return view('admin.logs.audit', [
            'logs' => AuditLog::query()
                ->with('user')
                ->latest()
                ->paginate(15),
        ]);
    }
}
