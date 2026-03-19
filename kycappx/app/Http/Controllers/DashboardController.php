<?php

namespace App\Http\Controllers;

use App\Models\ApiKey;
use App\Models\FundingRequest;
use App\Models\VerificationRequest;
use App\Models\VerificationService;
use App\Models\WalletTransaction;
use App\Services\Billing\WalletService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(private WalletService $walletService)
    {
    }

    public function index(Request $request): View
    {
        $user = $request->user();
        $wallet = $this->walletService->ensureWallet($user->id);

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
                ->limit(4)
                ->get(),
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
                'paystack' => false,
            ],
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
