<?php

use Livewire\Component;
use App\Models\Wallet;

new class extends Component
{
    public float $balance = 0;
    public string $currency = 'NGN';
    public float $amount = 1000;

    public function mount()
    {
        $wallet = Wallet::firstOrCreate(
            ['user_id' => auth()->id()],
            ['currency' => 'NGN', 'balance' => 0, 'status' => 'active']
        );

        $this->balance = (float) $wallet->balance;
        $this->currency = $wallet->currency;
    }

    public function refreshBalance()
    {
        $wallet = Wallet::where('user_id', auth()->id())->first();
        $this->balance = (float) ($wallet?->balance ?? 0);
    }
};
?>

<x-layouts.dashboard-user title="Wallet" header="Wallet">
    <div class="space-y-6">
        <x-ui.card class="p-6">
            <div class="text-xs text-gray-500">Current Balance</div>
            <div class="mt-2 text-3xl font-bold">₦{{ number_format($balance, 2) }}</div>
            <div class="mt-1 text-sm text-gray-500">Currency: {{ $currency }}</div>

            <div class="mt-4">
                <x-ui.button variant="secondary" wire:click="refreshBalance">Refresh</x-ui.button>
            </div>
        </x-ui.card>

        <x-ui.card class="p-6 space-y-4">
            <div>
                <div class="text-lg font-semibold">Fund Wallet</div>
                <div class="text-sm text-gray-500">Buttons are UI-ready. We’ll wire to Paystack/Kora initialize endpoints later.</div>
            </div>

            <div class="grid gap-3 md:grid-cols-3">
                <div class="md:col-span-1">
                    <label class="text-sm font-medium">Amount (NGN)</label>
                    <input type="number" min="100" step="50" wire:model="amount"
                        class="w-full mt-1 rounded-xl border-gray-200 focus:border-gray-400 focus:ring-gray-400" />
                </div>

                <div class="flex items-end gap-2 md:col-span-2">
                    <x-ui.button variant="secondary"
                        onclick="alert('Wire Paystack initialize endpoint here (Step 8)')">
                        Fund with Paystack
                    </x-ui.button>

                    <x-ui.button
                        onclick="alert('Wire Kora initialize endpoint here (Step 9)')">
                        Fund with Kora
                    </x-ui.button>
                </div>
            </div>

            <div class="text-xs text-gray-500">
                Tip: Wallet credits should happen only via webhook confirmation.
            </div>
        </x-ui.card>
    </div>
</x-layouts.dashboard-user>