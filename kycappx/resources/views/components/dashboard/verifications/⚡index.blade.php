<?php

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\VerificationRequest;

new class extends Component
{
    use WithPagination;

    public function getRowsProperty()
    {
        return VerificationRequest::where('user_id', auth()->id())
            ->latest()
            ->paginate(10);
    }
};
?>

<x-layouts.dashboard-user title="Verifications" header="Verifications">
    <div class="space-y-4">
        <div class="flex items-center justify-between">
            <div>
                <div class="text-lg font-semibold">Verifications</div>
                <div class="text-sm text-gray-500">History of KYC/KYB checks.</div>
            </div>
            <a href="{{ route('verifications.create') }}"><x-ui.button>New Verification</x-ui.button></a>
        </div>

        <x-ui.card class="overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-gray-600">
                    <tr>
                        <th class="px-4 py-3 text-left">Date</th>
                        <th class="px-4 py-3 text-left">Reference</th>
                        <th class="px-4 py-3 text-left">Status</th>
                        <th class="px-4 py-3 text-left">Provider</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @foreach($this->rows as $r)
                        <tr class="bg-white">
                            <td class="px-4 py-3">{{ $r->created_at->format('Y-m-d H:i') }}</td>
                            <td class="px-4 py-3 font-mono text-xs">{{ $r->reference }}</td>
                            <td class="px-4 py-3">{{ $r->status }}</td>
                            <td class="px-4 py-3">{{ $r->provider_used }}</td>
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