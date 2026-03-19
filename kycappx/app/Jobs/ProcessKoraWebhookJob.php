<?php

namespace App\Jobs;

use App\Models\FundingRequest;
use App\Models\WebhookLog;
use App\Services\Billing\Gateways\KoraGateway;
use App\Services\Billing\VirtualAccountService;
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

    public function handle(
        WalletService $walletService,
        VirtualAccountService $virtualAccounts,
        KoraGateway $koraGateway
    ): void
    {
        $log = WebhookLog::find($this->webhookLogId);
        if (! $log || $log->processed) {
            return;
        }

        $event = $log->payload['event'] ?? '';
        $data = $log->payload['data'] ?? [];
        $reference = $data['reference'] ?? null;
        $status = $data['status'] ?? null;

        DB::transaction(function () use ($log, $walletService, $reference, $status) {
            $funding = FundingRequest::where('reference', $reference)->lockForUpdate()->first();

            if ($funding) {
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

                return;
            }

            if ($event !== 'charge.success' || $status !== 'success') {
                $log->update([
                    'processed' => true,
                    'error_message' => 'Funding reference not found',
                ]);

                return;
            }

            $account = $virtualAccounts->locateAccountFromPayment('kora', $data);

            if (! $account) {
                $log->update([
                    'processed' => true,
                    'error_message' => 'Dedicated account payment did not match any Kora account.',
                ]);

                return;
            }

            $charge = $reference ? $koraGateway->queryCharge($reference) : ['ok' => false, 'body' => []];
            $chargeData = $charge['ok'] ? (data_get($charge, 'body.data', []) ?: []) : [];
            $amount = (float) (data_get($chargeData, 'amount_paid') ?? data_get($data, 'amount') ?? 0);

            $walletService->credit(
                userId: $account->user_id,
                amount: $amount,
                reference: 'KORA_'.$reference,
                source: 'kora_dva',
                description: 'Wallet top-up via Kora virtual account',
                meta: array_filter([
                    'webhook' => $data,
                    'charge' => $chargeData,
                ]),
            );

            $account->forceFill([
                'status' => 'active',
                'meta' => array_merge($account->meta ?? [], ['last_payment' => $data]),
            ])->save();

            $log->update(['processed' => true]);
        });
    }
}
