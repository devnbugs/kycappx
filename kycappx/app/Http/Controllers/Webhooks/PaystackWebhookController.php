<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessPaystackWebhookJob;
use App\Models\WebhookLog;
use App\Services\Billing\Gateways\PaystackGateway;
use Illuminate\Http\Request;

class PaystackWebhookController extends Controller
{
    public function handle(Request $request, PaystackGateway $paystackGateway)
    {
        $rawPayload = $request->getContent();
        $payload = $request->json()->all();
        $signature = $request->header('x-paystack-signature');
        $signatureValid = $paystackGateway->verifyWebhookSignature($rawPayload, $signature);

        $log = WebhookLog::create([
            'provider' => 'paystack',
            'event' => $payload['event'] ?? null,
            'reference' => data_get($payload, 'data.reference'),
            'signature_valid' => $signatureValid,
            'payload' => $payload,
            'processed' => false,
        ]);

        if (! $signatureValid) {
            return response()->json(['ok' => true], 200);
        }

        ProcessPaystackWebhookJob::dispatch($log->id);

        return response()->json(['ok' => true], 200);
    }
}
