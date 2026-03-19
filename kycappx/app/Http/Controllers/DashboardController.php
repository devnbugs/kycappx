<?php

namespace App\Http\Controllers;

use App\Models\ApiKey;
use App\Models\DedicatedVirtualAccount;
use App\Models\FundingRequest;
use App\Models\VerificationRequest;
use App\Models\VerificationService;
use App\Models\WalletTransaction;
use App\Services\Billing\VirtualAccountService;
use App\Services\Billing\WalletService;
use App\Services\SiteSettings;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(
        private WalletService $walletService,
        private VirtualAccountService $virtualAccounts,
        private SiteSettings $siteSettings,
    ) {
    }

    public function index(Request $request): View
    {
        $user = $request->user();
        $wallet = $this->walletService->ensureWallet($user->id);
        $siteSettings = $this->siteSettings->current();

        return view('dashboard.home', [
            'wallet' => $wallet,
            'stats' => [
                'wallet_balance' => (float) $wallet->balance,
                'transaction_count' => WalletTransaction::query()
                    ->where('wallet_id', $wallet->id)
                    ->count(),
                'verification_count' => VerificationRequest::query()
                    ->where('user_id', $user->id)
                    ->count(),
                'active_api_keys' => ApiKey::query()
                    ->where('user_id', $user->id)
                    ->where('is_active', true)
                    ->count(),
            ],
            'recentTransactions' => WalletTransaction::query()
                ->where('wallet_id', $wallet->id)
                ->latest()
                ->limit(5)
                ->get(),
            'recentVerifications' => VerificationRequest::query()
                ->with('service')
                ->where('user_id', $user->id)
                ->latest()
                ->limit(5)
                ->get(),
            'activeServices' => VerificationService::query()
                ->active()
                ->orderBy('name')
                ->limit(6)
                ->get(),
            'virtualAccounts' => DedicatedVirtualAccount::query()
                ->where('user_id', $user->id)
                ->orderByDesc('is_primary')
                ->orderBy('provider')
                ->get(),
            'virtualAccountProviders' => $this->virtualAccounts->providers(),
            'discountRate' => $user->currentDiscountRate((float) $siteSettings->user_pro_discount_rate),
        ]);
    }

    public function wallet(Request $request): View
    {
        $user = $request->user();
        $wallet = $this->walletService->ensureWallet($user->id);

        return view('dashboard.wallet', [
            'wallet' => $wallet,
            'recentFundingRequests' => FundingRequest::query()
                ->where('user_id', $user->id)
                ->latest()
                ->limit(6)
                ->get(),
            'recentTransactions' => $wallet->transactions()
                ->latest()
                ->limit(8)
                ->get(),
            'gatewayStatus' => [
                'kora' => filled(config('services.kora.secret_key')) && filled(config('services.kora.redirect_url')),
                'paystack' => filled(config('services.paystack.secret_key')),
            ],
            'virtualAccounts' => DedicatedVirtualAccount::query()
                ->where('user_id', $user->id)
                ->orderByDesc('is_primary')
                ->orderBy('provider')
                ->get(),
            'virtualAccountProviders' => $this->virtualAccounts->providers(),
        ]);
    }

    public function transactions(Request $request): View
    {
        $wallet = $this->walletService->ensureWallet($request->user()->id);
        $type = $request->query('type', 'all');

        $transactions = WalletTransaction::query()
            ->where('wallet_id', $wallet->id)
            ->when(
                in_array($type, ['credit', 'debit', 'refund'], true),
                fn ($query) => $query->where('type', $type)
            )
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('dashboard.transactions', [
            'wallet' => $wallet,
            'transactions' => $transactions,
            'type' => $type,
        ]);
    }
}
