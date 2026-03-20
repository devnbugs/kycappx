<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Services\Billing\Gateways\SquadGateway;
use App\Services\Messaging\SquadSmsService;
use App\Services\Providers\ProviderFeatureService;
use App\Services\SiteSettings;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SmsController extends Controller
{
    public function __construct(
        private SquadSmsService $smsService,
        private SquadGateway $squadGateway,
        private ProviderFeatureService $providerFeatures,
        private SiteSettings $siteSettings,
    ) {
    }

    public function index(Request $request): View
    {
        return view('dashboard.sms.index', [
            'dispatches' => $this->smsService->paginateForUser($request->user()->id),
            'smsEnabled' => $this->smsService->enabled(),
            'templatesEnabled' => $this->smsService->templateEnabled(),
            'gatewayConfigured' => $this->squadGateway->isConfigured(),
            'defaultSenderId' => config('services.squad.sms_sender_id', 'S-Alert'),
            'siteSettings' => $this->siteSettings->current(),
            'providerProducts' => [
                'sms_messages' => $this->providerFeatures->isProductEnabled('squad', 'sms_messages', true),
                'sms_templates' => $this->providerFeatures->isProductEnabled('squad', 'sms_templates', false),
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'sender_id' => ['required', 'string', 'max:20'],
            'recipients' => ['required', 'string'],
            'message' => ['required', 'string', 'max:1000'],
        ]);

        $messages = collect(preg_split('/[\r\n,]+/', $validated['recipients']) ?: [])
            ->map(fn (string $phone) => trim($phone))
            ->filter()
            ->unique()
            ->map(fn (string $phone) => [
                'phone_number' => $phone,
                'message' => $validated['message'],
            ])
            ->values()
            ->all();

        if ($messages === []) {
            return back()->withErrors([
                'recipients' => 'Provide at least one phone number to send this SMS batch.',
            ]);
        }

        $dispatch = $this->smsService->sendInstant(
            user: $request->user(),
            senderId: $validated['sender_id'],
            messages: $messages,
            title: 'Instant SMS batch',
        );

        return back()->with(
            $dispatch->status === 'success' ? 'status' : 'sms_status',
            $dispatch->status === 'success'
                ? 'SMS batch submitted successfully.'
                : 'The SMS batch could not be sent. Check the activity log below.'
        );
    }

    public function storeTemplate(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'description' => ['required', 'string', 'max:255'],
            'template_message' => ['required', 'string', 'max:1000'],
        ]);

        $dispatch = $this->smsService->createTemplate(
            user: $request->user(),
            name: $validated['name'],
            description: $validated['description'],
            message: $validated['template_message'],
        );

        return back()->with(
            $dispatch->status === 'success' ? 'status' : 'sms_status',
            $dispatch->status === 'success'
                ? 'SMS template created successfully.'
                : 'The SMS template could not be created. Check the activity log below.'
        );
    }
}
