<?php

use Livewire\Component;
use App\Models\VerificationService;

new class extends Component
{
    public ?int $serviceId = null;
    public string $bvn = '';
    public string $nin = '';

    public function submit()
    {
        $this->validate([
            'serviceId' => ['required','integer','exists:verification_services,id'],
        ]);

        // Later: call Orchestrator here.
        session()->flash('status', 'Captured. Next: connect Orchestrator to run verification.');
        return redirect()->route('verifications.index');
    }

    public function getServicesProperty()
    {
        return VerificationService::where('is_active', true)->orderBy('name')->get();
    }
};
?>

<x-layouts.dashboard-user title="New Verification" header="New Verification">
    <x-ui.card class="p-6 space-y-4">
        <div>
            <div class="text-lg font-semibold">Run a Verification</div>
            <div class="text-sm text-gray-500">Choose service and provide required fields.</div>
        </div>

        <div class="grid gap-4 md:grid-cols-2">
            <div>
                <label class="text-sm font-medium">Service</label>
                <select wire:model="serviceId" class="w-full mt-1 rounded-xl border-gray-200">
                    <option value="">Select...</option>
                    @foreach($this->services as $s)
                        <option value="{{ $s->id }}">{{ $s->name }} ({{ $s->code }})</option>
                    @endforeach
                </select>
                @error('serviceId') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
            </div>

            <div class="text-sm text-gray-500 md:pt-6">
                You’ll configure required fields per service later in Admin → Services.
            </div>

            <div>
                <label class="text-sm font-medium">BVN</label>
                <input wire:model="bvn" class="w-full mt-1 rounded-xl border-gray-200" placeholder="12345678901">
            </div>

            <div>
                <label class="text-sm font-medium">NIN</label>
                <input wire:model="nin" class="w-full mt-1 rounded-xl border-gray-200" placeholder="12345678901">
            </div>
        </div>

        <div class="flex gap-2">
            <x-ui.button wire:click="submit">Submit</x-ui.button>
            <a href="{{ route('verifications.index') }}"><x-ui.button variant="secondary">Cancel</x-ui.button></a>
        </div>
    </x-ui.card>
</x-layouts.dashboard-user>