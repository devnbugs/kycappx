<?php

use Livewire\Component;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use App\Models\ApiKey;

new class extends Component
{
    public string $name = 'Default Key';
    public ?string $newKey = null;

    public function create()
    {
        $this->validate(['name' => ['required','string','max:50']]);

        $raw = 'kyc_' . Str::random(40);
        $prefix = substr($raw, 0, 12);

        ApiKey::create([
            'user_id' => auth()->id(),
            'name' => $this->name,
            'key_hash' => Hash::make($raw),
            'prefix' => $prefix,
            'abilities' => ['verification:create', 'verification:read', 'wallet:read'],
            'is_active' => true,
        ]);

        $this->newKey = $raw;
        $this->name = 'Default Key';
    }

    public function deactivate(int $id)
    {
        $k = ApiKey::where('user_id', auth()->id())->findOrFail($id);
        $k->update(['is_active' => false]);
    }

    public function getKeysProperty()
    {
        return ApiKey::where('user_id', auth()->id())->latest()->get();
    }
};
?>

<x-layouts.dashboard-user title="API Keys" header="API Keys">
    <div class="space-y-6">
        <x-ui.card class="p-6 space-y-4">
            <div>
                <div class="text-lg font-semibold">Create API Key</div>
                <div class="text-sm text-gray-500">Key is shown only once. Copy it now.</div>
            </div>

            <div class="flex flex-col gap-3 md:flex-row md:items-end">
                <div class="flex-1">
                    <label class="text-sm font-medium">Key Name</label>
                    <input wire:model="name" class="w-full mt-1 rounded-xl border-gray-200" placeholder="e.g. Production Key">
                    @error('name') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
                </div>
                <x-ui.button wire:click="create">Generate</x-ui.button>
            </div>

            @if($newKey)
                <div class="p-3 text-sm border rounded-xl bg-yellow-50 border-yellow-200">
                    <div class="font-semibold">New Key (copy now):</div>
                    <div class="mt-1 font-mono break-all">{{ $newKey }}</div>
                </div>
            @endif
        </x-ui.card>

        <x-ui.card class="overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-gray-600">
                    <tr>
                        <th class="px-4 py-3 text-left">Name</th>
                        <th class="px-4 py-3 text-left">Prefix</th>
                        <th class="px-4 py-3 text-left">Active</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @foreach($this->keys as $k)
                        <tr class="bg-white">
                            <td class="px-4 py-3">{{ $k->name }}</td>
                            <td class="px-4 py-3 font-mono text-xs">{{ $k->prefix }}******</td>
                            <td class="px-4 py-3">{{ $k->is_active ? 'Yes' : 'No' }}</td>
                            <td class="px-4 py-3 text-right">
                                @if($k->is_active)
                                    <x-ui.button variant="danger" wire:click="deactivate({{ $k->id }})">Deactivate</x-ui.button>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </x-ui.card>
    </div>
</x-layouts.dashboard-user>