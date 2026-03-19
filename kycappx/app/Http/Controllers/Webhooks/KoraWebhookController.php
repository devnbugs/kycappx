<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessKoraWebhookJob;
use App\Models\WebhookLog;
use App\Services\Billing\Gateways\KoraGateway;
use Illuminate\Http\Request;

class KoraWebhookController extends Controller
{
    public function handle(Request $request, KoraGateway $kora)
    {
        $payload = $request->all();
        $signature = $request->header('x-korapay-signature');

        $signatureValid = $kora->verifyWebhookSignature($payload, $signature);

        // Always store log for audit/debug
        $log = WebhookLog::create([
            'provider' => 'kora',
            'event' => $payload['event'] ?? null,
            'reference' => data_get($payload, 'data.reference'),
            'signature_valid' => $signatureValid,
            'payload' => $payload,
            'processed' => false,
        ]);

        // Kora expects 200 regardless; they retry on non-200/timeouts. :contentReference[oaicite:5]{index=5}
        if (!$signatureValid) {
            // Acknowledge but ignore invalid signature
            return response()->json(['ok' => true], 200);
        }

        ProcessKoraWebhookJob::dispatch($log->id);

        return response()->json(['ok' => true], 200);
    }
}