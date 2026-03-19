<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\FundingRequest;
use App\Services\Billing\Gateways\KoraGateway;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class KoraFundingController extends Controller
{
    public function initialize(Request $request, KoraGateway $kora)
    {
        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:100'],
            'currency' => ['nullable', 'string', 'size:3'],
        ]);

        $user = Auth::user();
        $amount = (float) $data['amount'];
        $currency = strtoupper($data['currency'] ?? 'NGN');

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

        if (!$res['ok']) {
            return response()->json($res, 422);
        }

        // Your Kora response should include a checkout URL according to Checkout Redirect docs. :contentReference[oaicite:4]{index=4}
        // Adjust the JSON path to your actual response shape.
        $checkoutUrl = data_get($res['body'], 'data.checkout_url') ?? data_get($res['body'], 'data.checkoutUrl');

        if (!$checkoutUrl) {
            return response()->json([
                'ok' => false,
                'message' => 'No checkout URL returned from Kora',
                'body' => $res['body'],
            ], 422);
        }

        return response()->json([
            'ok' => true,
            'reference' => $reference,
            'checkout_url' => $checkoutUrl,
        ]);
    }

    public function return(Request $request)
    {
        // User returns here after payment. Webhook is source-of-truth.
        // You can show a "Payment processing" screen and poll funding status.
        return redirect()->route('dashboard')->with('status', 'Payment received. Confirming...');
    }
}