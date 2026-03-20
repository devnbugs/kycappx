<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessSquadWebhookJob;
use App\Models\WebhookLog;
use App\Services\Billing\Gateways\SquadGateway;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SquadWebhookController extends Controller
{
    public function handle(Request $request, SquadGateway $squadGateway): JsonResponse
    {
        $rawPayload = $request->getContent();
        $payload = $request->json()->all();
        $encryptedBody = $request->header('x-squad-encrypted-body');
        $signature = $request->header('x-squad-signature');

        $signatureValid = $squadGateway->verifyWebhookSignature($rawPayload, $encryptedBody, $signature);

        $log = WebhookLog::create([
            'provider' => 'squad',
            'event' => data_get($payload, 'channel', 'virtual-account'),
            'reference' => data_get($payload, 'transaction_reference'),
            'signature_valid' => $signatureValid,
            'payload' => $payload,
            'processed' => false,
        ]);

        if ($signatureValid) {
            ProcessSquadWebhookJob::dispatch($log->id);
        }

        return response()->json([
            'response_code' => 200,
            'transaction_reference' => data_get($payload, 'transaction_reference'),
            'response_description' => $signatureValid ? 'Success' : 'Ignored invalid signature',
        ], 200);
    }
}
