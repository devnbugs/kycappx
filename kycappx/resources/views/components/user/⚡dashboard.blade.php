<?php

use Livewire\Component;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Models\VerificationRequest;

new class extends Component
{
    public float $balance = 0.0;
    public int $txCount = 0;
    public int $verificationCount = 0;

    public function mount()
    {
        $userId = auth()->id();

        $wallet = Wallet::where('user_id', $userId)->first();
        $this->balance = (float) ($wallet?->balance ?? 0);

        $this->txCount = WalletTransaction::whereHas('wallet', fn($q) => $q->where('user_id', $userId))->count();
        $this->verificationCount = VerificationRequest::where('user_id', $userId)->count();
    }
};
?>

<x-layouts.dashboard-user title="Dashboard" header="Dashboard">
    <div class="space-y-6">
        <div class="grid gap-4 md:grid-cols-3">
            <x-ui.card class="p-5">
                <div class="text-xs text-gray-500">Wallet Balance</div>
                <div class="mt-2 text-2xl font-bold">₦{{ number_format($balance, 2) }}</div>
                <div class="mt-4">
                    <a href="{{ route('wallet') }}"><x-ui.button variant="secondary">Manage Wallet</x-ui.button></a>
                </div>
            </x-ui.card>

            <x-ui.card class="p-5">
                <div class="text-xs text-gray-500">Transactions</div>
                <div class="mt-2 text-2xl font-bold">{{ number_format($txCount) }}</div>
                <div class="mt-4">
                    <a href="{{ route('transactions') }}"><x-ui.button variant="secondary">View Transactions</x-ui.button></a>
                </div>
            </x-ui.card>

            <x-ui.card class="p-5">
                <div class="text-xs text-gray-500">Verifications</div>
                <div class="mt-2 text-2xl font-bold">{{ number_format($verificationCount) }}</div>
                <div class="mt-4">
                    <a href="{{ route('verifications.index') }}"><x-ui.button variant="secondary">View Verifications</x-ui.button></a>
                </div>
            </x-ui.card>
        </div>

        <x-ui.card class="p-6">
            <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                <div>
                    <div class="text-lg font-semibold">Quick Actions</div>
                    <div class="text-sm text-gray-500">Start a verification or fund your wallet.</div>
                </div>
                <div class="flex gap-2">
                    <a href="{{ route('verifications.create') }}"><x-ui.button>New Verification</x-ui.button></a>
                    <a href="{{ route('wallet') }}"><x-ui.button variant="secondary">Fund Wallet</x-ui.button></a>
                </div>
            </div>
        </x-ui.card>
    </div>
</x-layouts.dashboard-user>