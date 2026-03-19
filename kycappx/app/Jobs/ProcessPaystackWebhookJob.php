<?php

namespace App\Jobs;

use App\Models\WebhookLog;
use App\Services\Billing\VirtualAccountService;
use App\Services\Billing\WalletService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class ProcessPaystackWebhookJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $webhookLogId)
    {
    }

    public function handle(VirtualAccountService $virtualAccounts, WalletService $walletService): void
    {
        $log = WebhookLog::find($this->webhookLogId);

        if (! $log || $log->processed) {
            return;
        }

        $payload = $log->payload ?? [];
        $event = (string) ($payload['event'] ?? $log->event);
        $data = $payload['data'] ?? [];

        DB::transaction(function () use ($event, $data, $log, $virtualAccounts, $walletService) {
            if ($event === 'dedicatedaccount.assign.success') {
                $virtualAccounts->syncPaystackAssignment($data);
                $log->update(['processed' => true]);

                return;
            }

            if ($event === 'dedicatedaccount.assign.failed') {
                $virtualAccounts->markPaystackAssignmentFailed($data);
                $log->update([
                    'processed' => true,
                    'error_message' => data_get($data, 'reason', 'Dedicated account assignment failed.'),
                ]);

                return;
            }

            if ($event !== 'charge.success' || data_get($data, 'authorization.channel') !== 'dedicated_nuban') {
                $log->update(['processed' => true]);

                return;
            }

            $account = $virtualAccounts->locateAccountFromPayment('paystack', $data);

            if (! $account) {
                $log->update([
                    'processed' => true,
                    'error_message' => 'Dedicated account payment did not match any user account.',
                ]);

                return;
            }

            $walletService->credit(
                userId: $account->user_id,
                amount: round(((float) ($data['amount'] ?? 0)) / 100, 2),
                reference: 'PAYSTACK_'.$data['reference'],
                source: 'paystack_dva',
                description: 'Wallet top-up via Paystack dedicated account',
                meta: $data,
            );

            $account->forceFill([
                'status' => 'active',
                'meta' => array_merge($account->meta ?? [], ['last_payment' => $data]),
            ])->save();

            $log->update(['processed' => true]);
        });
    }
}
