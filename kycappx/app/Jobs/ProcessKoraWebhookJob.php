<?php

namespace App\Jobs;

use App\Models\FundingRequest;
use App\Models\WebhookLog;
use App\Services\Billing\WalletService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class ProcessKoraWebhookJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $webhookLogId) {}

    public function handle(WalletService $walletService): void
    {
        $log = WebhookLog::find($this->webhookLogId);
        if (!$log || $log->processed) return;

        $event = $log->payload['event'] ?? '';
        $data = $log->payload['data'] ?? [];
        $reference = $data['reference'] ?? null;
        $status = $data['status'] ?? null;

        DB::transaction(function () use ($log, $walletService, $reference, $status) {
            $funding = FundingRequest::where('reference', $reference)->lockForUpdate()->first();

            if (!$funding) {
                $log->update([
                    'processed' => true,
                    'error_message' => 'Funding reference not found',
                ]);
                return;
            }

            // Idempotency: If already success, stop.
            if ($funding->status === 'success') {
                $log->update(['processed' => true]);
                return;
            }

            if ($status === 'success') {
                $funding->update([
                    'status' => 'success',
                    'gateway_reference' => $data['payment_reference'] ?? $data['transaction_reference'] ?? null,
                    'paid_at' => now(),
                    'meta' => $data,
                ]);

                $walletService->credit(
                    userId: $funding->user_id,
                    amount: (float) ($data['amount'] ?? $funding->amount),
                    reference: $funding->reference,
                    source: 'kora',
                    description: 'Wallet top-up (Kora)',
                    meta: $data
                );
            } else {
                $funding->update([
                    'status' => 'failed',
                    'gateway_reference' => $data['payment_reference'] ?? $data['transaction_reference'] ?? null,
                    'meta' => $data,
                ]);
            }

            $log->update(['processed' => true]);
        });
    }
}