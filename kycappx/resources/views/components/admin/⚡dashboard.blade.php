<?php

use Livewire\Component;
use App\Models\User;
use App\Models\FundingRequest;
use App\Models\WebhookLog;
use App\Models\VerificationRequest;

new class extends Component
{
    public int $users = 0;
    public int $fundings = 0;
    public int $webhooks = 0;
    public int $verifications = 0;

    public function mount()
    {
        $this->users = User::count();
        $this->fundings = FundingRequest::count();
        $this->webhooks = WebhookLog::count();
        $this->verifications = VerificationRequest::count();
    }
};
?>

<x-layouts.dashboard-admin title="Admin Overview" header="Admin Overview">
    <div class="grid gap-4 md:grid-cols-4">
        <x-ui.card class="p-5">
            <div class="text-xs text-gray-500">Users</div>
            <div class="mt-2 text-2xl font-bold">{{ number_format($users) }}</div>
        </x-ui.card>

        <x-ui.card class="p-5">
            <div class="text-xs text-gray-500">Fundings</div>
            <div class="mt-2 text-2xl font-bold">{{ number_format($fundings) }}</div>
        </x-ui.card>

        <x-ui.card class="p-5">
            <div class="text-xs text-gray-500">Webhooks</div>
            <div class="mt-2 text-2xl font-bold">{{ number_format($webhooks) }}</div>
        </x-ui.card>

        <x-ui.card class="p-5">
            <div class="text-xs text-gray-500">Verifications</div>
            <div class="mt-2 text-2xl font-bold">{{ number_format($verifications) }}</div>
        </x-ui.card>
    </div>

    <x-ui.card class="p-6 mt-6">
        <div class="text-lg font-semibold">Next Admin Pages</div>
        <div class="text-sm text-gray-500">
            Add Customers list, Providers, Services, Pricing, Logs pages (we’ll build them next).
        </div>
    </x-ui.card>
</x-layouts.dashboard-admin>