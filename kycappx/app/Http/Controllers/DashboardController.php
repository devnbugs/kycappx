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
use App\Services\Kyc\KycStrengthService;
use App\Services\Providers\ProviderFeatureService;
use App\Services\SiteSettings;
use App\Services\Verification\IdentityEngineRegistry;
use App\Services\Verification\VerificationCatalogService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(
        private WalletService $walletService,
        private VirtualAccountService $virtualAccounts,
        private KycStrengthService $kycStrength,
        private ProviderFeatureService $providerFeatures,
        private SiteSettings $siteSettings,
        private VerificationCatalogService $verificationCatalog,
        private IdentityEngineRegistry $identityEngines,
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
            'activeServices' => $this->verificationCatalog
                ->filterLaunchable(VerificationService::query()
                    ->active()
                    ->orderBy('name')
                    ->get())
                ->sortBy(fn (VerificationService $service) => sprintf(
                    '%s-%s',
                    data_get($this->verificationCatalog->definitionFor($service), 'service.featured', false) ? '0' : '1',
                    $service->name
                ))
                ->take(6)
                ->values(),
            'virtualAccounts' => DedicatedVirtualAccount::query()
                ->where('user_id', $user->id)
                ->orderByDesc('is_primary')
                ->orderBy('provider')
                ->get(),
            'virtualAccountProviders' => $this->virtualAccounts->providers(),
            'discountRate' => $user->currentDiscountRate((float) $siteSettings->user_pro_discount_rate),
            'kycSnapshot' => $this->kycStrength->snapshot($user),
            'verificationProviderLabels' => collect($this->identityEngines->providerCodes())
                ->mapWithKeys(fn (string $provider) => [$provider => $this->identityEngines->publicLabel($provider)])
                ->all(),
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
                'squad' => filled(config('services.squad.secret_key')),
            ],
            'providerProducts' => [
                'kora_checkout' => $this->providerFeatures->isProductEnabled('kora', 'checkout', true),
                'paystack_dedicated_accounts' => $this->providerFeatures->isProductEnabled('paystack', 'dedicated_accounts', true),
                'kora_virtual_accounts' => $this->providerFeatures->isProductEnabled('kora', 'virtual_accounts', true),
                'squad_virtual_accounts' => $this->providerFeatures->isProductEnabled('squad', 'virtual_accounts', true),
            ],
            'virtualAccounts' => DedicatedVirtualAccount::query()
                ->where('user_id', $user->id)
                ->orderByDesc('is_primary')
                ->orderBy('provider')
                ->get(),
            'virtualAccountProviders' => $this->virtualAccounts->providers(),
            'squadRequirements' => $this->virtualAccounts->squadRequirements($user),
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
