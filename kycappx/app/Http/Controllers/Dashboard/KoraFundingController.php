<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\FundingRequest;
use App\Services\Providers\ProviderFeatureService;
use App\Services\SiteSettings;
use App\Services\Billing\Gateways\KoraGateway;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class KoraFundingController extends Controller
{
    public function __construct(
        private SiteSettings $siteSettings,
        private ProviderFeatureService $providerFeatures,
    ) {
    }

    public function initialize(Request $request, KoraGateway $kora): RedirectResponse|JsonResponse
    {
        if (! $this->siteSettings->current()->wallet_funding_enabled) {
            return $this->failureResponse($request, 'Wallet funding is currently disabled by the site administrator.');
        }

        if (! $this->providerFeatures->isProductEnabled('kora', 'checkout', true)) {
            return $this->failureResponse($request, 'Kora checkout has been disabled from the admin provider controls.');
        }

        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:100'],
            'currency' => ['nullable', 'string', 'size:3'],
        ]);

        $user = Auth::user();
        $amount = (float) $data['amount'];
        $currency = strtoupper($data['currency'] ?? $this->siteSettings->current()->default_currency);

        if (! filled(config('services.kora.secret_key')) || ! filled(config('services.kora.redirect_url'))) {
            return $this->failureResponse($request, 'Kora funding is not configured in the environment.');
        }

        $reference = 'FUNDKORA_' . Str::upper(Str::random(12));

        FundingRequest::create([
            'user_id' => $user->id,
            'gateway' => 'kora',
            'amount' => $amount,
            'currency' => $currency,
            'reference' => $reference,
            'status' => 'pending',
        ]);

        $payload = [
            'amount' => (int) round($amount),      // Kora often expects integer amount; follow your account docs
            'currency' => $currency,
            'reference' => $reference,
            'redirect_url' => config('services.kora.redirect_url'),
            'customer' => [
                'name' => $user->name ?? 'Customer',
                'email' => $user->email,
            ],
        ];

        $res = $kora->initializeCheckout($payload);

        if (! $res['ok']) {
            return $this->failureResponse($request, $res['message'] ?? 'Kora initialize failed.', $res);
        }

        $checkoutUrl = data_get($res['body'], 'data.checkout_url') ?? data_get($res['body'], 'data.checkoutUrl');

        if (! $checkoutUrl) {
            return $this->failureResponse($request, 'Kora did not return a checkout URL.', [
                'body' => $res['body'],
            ]);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'reference' => $reference,
                'checkout_url' => $checkoutUrl,
            ]);
        }

        return redirect()->away($checkoutUrl);
    }

    public function handleReturn(Request $request): RedirectResponse
    {
        return redirect()
            ->route('wallet')
            ->with('status', 'Payment received. We are confirming the transaction with Kora now.');
    }

    private function failureResponse(Request $request, string $message, array $context = []): RedirectResponse|JsonResponse
    {
        if ($request->expectsJson()) {
            return response()->json([
                'ok' => false,
                'message' => $message,
            ] + $context, 422);
        }

        return back()
            ->withInput()
            ->withErrors(['amount' => $message]);
    }
}
