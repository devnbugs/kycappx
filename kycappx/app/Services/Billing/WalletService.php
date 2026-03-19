<?php

namespace App\Services\Billing;

use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class WalletService
{
    public function ensureWallet(int $userId, string $currency = 'NGN'): Wallet
    {
        return Wallet::firstOrCreate(
            ['user_id' => $userId],
            ['currency' => strtoupper($currency), 'balance' => 0, 'status' => 'active']
        );
    }

    public function currentBalance(int $userId): float
    {
        return (float) $this->ensureWallet($userId)->balance;
    }

    public function credit(
        int $userId,
        float $amount,
        string $reference,
        string $source = 'internal',
        ?string $description = null,
        array $meta = []
    ): WalletTransaction {
        return $this->recordTransaction(
            type: 'credit',
            userId: $userId,
            amount: $amount,
            reference: $reference,
            source: $source,
            description: $description,
            meta: $meta
        );
    }

    public function debit(
        int $userId,
        float $amount,
        string $reference,
        string $source = 'internal',
        ?string $description = null,
        array $meta = []
    ): WalletTransaction {
        return $this->recordTransaction(
            type: 'debit',
            userId: $userId,
            amount: $amount,
            reference: $reference,
            source: $source,
            description: $description,
            meta: $meta
        );
    }

    private function recordTransaction(
        string $type,
        int $userId,
        float $amount,
        string $reference,
        string $source,
        ?string $description,
        array $meta
    ): WalletTransaction {
        $normalizedAmount = round($amount, 2);

        if ($normalizedAmount <= 0) {
            throw new RuntimeException('Transaction amount must be greater than zero.');
        }

        return DB::transaction(function () use ($type, $userId, $normalizedAmount, $reference, $source, $description, $meta) {
            $wallet = Wallet::where('user_id', $userId)->lockForUpdate()->first();

            if (! $wallet) {
                $wallet = $this->ensureWallet($userId);
                $wallet->refresh();
            }

            $existingTransaction = WalletTransaction::where('reference', $reference)->first();

            if ($existingTransaction) {
                return $existingTransaction;
            }

            if ($type === 'debit' && (float) $wallet->balance < $normalizedAmount) {
                throw new RuntimeException('Insufficient wallet balance.');
            }

            $wallet->balance = round(
                (float) $wallet->balance + ($type === 'credit' ? $normalizedAmount : ($normalizedAmount * -1)),
                2
            );
            $wallet->save();

            return WalletTransaction::create([
                'wallet_id' => $wallet->id,
                'type' => $type,
                'amount' => $normalizedAmount,
                'currency' => $wallet->currency,
                'reference' => $reference,
                'status' => 'success',
                'source' => $source,
                'description' => $description,
                'meta' => $meta,
            ]);
        });
    }
}
