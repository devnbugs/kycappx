<?php

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\WalletTransaction;

new class extends Component
{
    use WithPagination;

    public string $type = 'all';

    public function updatingType()
    {
        $this->resetPage();
    }

    public function getRowsProperty()
    {
        $userId = auth()->id();

        $q = WalletTransaction::query()
            ->whereHas('wallet', fn($w) => $w->where('user_id', $userId))
            ->latest();

        if ($this->type !== 'all') {
            $q->where('type', $this->type);
        }

        return $q->paginate(10);
    }
};
?>

<x-layouts.dashboard-user title="Transactions" header="Transactions">
    <div class="space-y-4">
        <div class="flex items-center justify-between">
            <div>
                <div class="text-lg font-semibold">Transactions</div>
                <div class="text-sm text-gray-500">Wallet ledger history.</div>
            </div>

            <select wire:model="type" class="rounded-xl border-gray-200">
                <option value="all">All</option>
                <option value="credit">Credit</option>
                <option value="debit">Debit</option>
                <option value="refund">Refund</option>
            </select>
        </div>

        <x-ui.card class="overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-gray-600">
                    <tr>
                        <th class="px-4 py-3 text-left">Date</th>
                        <th class="px-4 py-3 text-left">Type</th>
                        <th class="px-4 py-3 text-left">Amount</th>
                        <th class="px-4 py-3 text-left">Reference</th>
                        <th class="px-4 py-3 text-left">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @foreach($this->rows as $tx)
                        <tr class="bg-white">
                            <td class="px-4 py-3">{{ $tx->created_at->format('Y-m-d H:i') }}</td>
                            <td class="px-4 py-3 font-medium">{{ strtoupper($tx->type) }}</td>
                            <td class="px-4 py-3">₦{{ number_format($tx->amount, 2) }}</td>
                            <td class="px-4 py-3 font-mono text-xs">{{ $tx->reference }}</td>
                            <td class="px-4 py-3">{{ $tx->status }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="p-4 bg-white">
                {{ $this->rows->links() }}
            </div>
        </x-ui.card>
    </div>
</x-layouts.dashboard-user>