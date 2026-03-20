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

class ProcessSquadWebhookJob implements ShouldQueue
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

        DB::transaction(function () use ($payload, $log, $virtualAccounts, $walletService) {
            $account = $virtualAccounts->locateAccountFromPayment('squad', $payload);

            if (! $account) {
                $log->update([
                    'processed' => true,
                    'error_message' => 'Squad virtual account payment did not match any user account.',
                ]);

                return;
            }

            $amount = (float) (data_get($payload, 'principal_amount') ?? data_get($payload, 'settled_amount') ?? 0);

            $walletService->credit(
                userId: $account->user_id,
                amount: $amount,
                reference: 'SQUAD_'.data_get($payload, 'transaction_reference'),
                source: 'squad_dva',
                description: 'Wallet top-up via Squad virtual account',
                meta: $payload,
            );

            $account->forceFill([
                'status' => 'active',
                'meta' => array_merge($account->meta ?? [], ['last_payment' => $payload]),
            ])->save();

            $log->update(['processed' => true]);
        });
    }
}
