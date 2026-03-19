<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\VerificationService;
use App\Services\Billing\WalletService;
use App\Services\Kyc\KycStrengthService;
use App\Services\Security\TurnstileService;
use App\Services\SiteSettings;
use App\Services\Verification\VerificationOrchestrator;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Throwable;

class VerificationController extends Controller
{
    public function __construct(
        private WalletService $walletService,
        private SiteSettings $siteSettings,
        private VerificationOrchestrator $verificationOrchestrator,
        private KycStrengthService $kycStrength,
        private TurnstileService $turnstile,
    ) {
    }

    public function index(Request $request): View
    {
        return view('dashboard.verifications.index', [
            'wallet' => $this->walletService->ensureWallet($request->user()->id),
            'verifications' => $request->user()
                ->verificationRequests()
                ->with('service')
                ->latest()
                ->paginate(10),
        ]);
    }

    public function create(Request $request): View
    {
        abort_unless($this->siteSettings->current()->verification_enabled, 403, 'Verification requests are currently disabled.');

        $user = $request->user();
        $services = VerificationService::query()
            ->active()
            ->orderBy('name')
            ->get();

        $selectedService = $services->firstWhere('id', (int) $request->query('service'));
        $discountRate = $user->currentDiscountRate((float) $this->siteSettings->current()->user_pro_discount_rate);

        return view('dashboard.verifications.create', [
            'wallet' => $this->walletService->ensureWallet($user->id),
            'services' => $services,
            'selectedService' => $selectedService,
            'fieldBlueprints' => $selectedService ? $this->fieldBlueprintsFor($selectedService) : [],
            'fieldDefaults' => $this->fieldDefaultsFor($user, $selectedService),
            'discountRate' => $discountRate,
            'selectedPrice' => $selectedService ? $this->priceFor($user, $selectedService) : 0,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless($this->siteSettings->current()->verification_enabled, 403, 'Verification requests are currently disabled.');
        $this->turnstile->ensureValidRequest($request, 'verification_request');

        $service = VerificationService::query()
            ->active()
            ->whereKey($request->integer('service_id'))
            ->firstOrFail();

        $validated = $request->validate(
            $this->rulesFor($service),
            [],
            $this->attributeNamesFor($service)
        );

        $wallet = $this->walletService->ensureWallet($request->user()->id);
        $price = $this->priceFor($request->user(), $service);

        if ($price > 0 && (float) $wallet->balance < $price) {
            throw ValidationException::withMessages([
                'service_id' => 'Your wallet balance is too low for this verification.',
            ]);
        }

        try {
            $verificationRequest = $this->verificationOrchestrator->submit(
                user: $request->user(),
                service: $service,
                payload: $this->payloadFor($service, $validated),
            );
        } catch (Throwable $exception) {
            report($exception);

            return back()
                ->withInput()
                ->withErrors([
                    'service_id' => 'We could not submit your verification right now. Please try again shortly.',
                ]);
        }

        $message = match ($verificationRequest->status) {
            'success' => 'Verification completed successfully.',
            'failed' => 'The provider could not verify this request.',
            default => 'Verification submitted and moved into review.',
        };

        return redirect()
            ->route('verifications.index')
            ->with('status', $message);
    }

    private function rulesFor(VerificationService $service): array
    {
        $rules = [
            'service_id' => ['required', 'integer', 'exists:verification_services,id'],
            'first_name' => ['nullable', 'string', 'max:100'],
            'middle_name' => ['nullable', 'string', 'max:100'],
            'last_name' => ['nullable', 'string', 'max:100'],
            'dob' => ['nullable', 'date'],
            'phone' => ['nullable', 'string', 'max:30'],
            'address_line1' => ['nullable', 'string', 'max:255'],
            'address_line2' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:120'],
            'state' => ['nullable', 'string', 'max:120'],
            'zip' => ['nullable', 'string', 'max:20'],
        ];

        return match (strtoupper($service->code)) {
            'BVN' => $rules + [
                'identifier' => ['required', 'digits:11'],
            ],
            'NIN' => $rules + [
                'identifier' => ['required', 'digits:11'],
            ],
            'CAC' => [
                'service_id' => ['required', 'integer', 'exists:verification_services,id'],
                'identifier' => ['required', 'string', 'max:40'],
                'company_name' => ['nullable', 'string', 'max:160'],
            ],
            'PHONE' => [
                'service_id' => ['required', 'integer', 'exists:verification_services,id'],
                'identifier' => ['required', 'string', 'max:20'],
            ],
            'US_PHONE' => $rules + [
                'identifier' => ['required', 'string', 'max:20'],
            ],
            'US_BIODATA' => [
                'service_id' => ['required', 'integer', 'exists:verification_services,id'],
                'first_name' => ['required', 'string', 'max:100'],
                'middle_name' => ['nullable', 'string', 'max:100'],
                'last_name' => ['required', 'string', 'max:100'],
                'dob' => ['required', 'date'],
                'address_line1' => ['required', 'string', 'max:255'],
                'address_line2' => ['nullable', 'string', 'max:255'],
                'city' => ['required', 'string', 'max:120'],
                'state' => ['required', 'string', 'max:120'],
                'zip' => ['nullable', 'string', 'max:20'],
            ],
            'US_ADDRESS' => [
                'service_id' => ['required', 'integer', 'exists:verification_services,id'],
                'address_line1' => ['required', 'string', 'max:255'],
                'address_line2' => ['nullable', 'string', 'max:255'],
                'city' => ['required', 'string', 'max:120'],
                'state' => ['required', 'string', 'max:120'],
                'zip' => ['required', 'string', 'max:20'],
            ],
            'US_SSN' => $rules + [
                'identifier' => ['required', 'string', 'max:20'],
            ],
            default => [
                'service_id' => ['required', 'integer', 'exists:verification_services,id'],
                'identifier' => ['required', 'string', 'max:120'],
            ],
        };
    }

    private function attributeNamesFor(VerificationService $service): array
    {
        return match (strtoupper($service->code)) {
            'BVN' => ['identifier' => 'BVN'],
            'NIN' => ['identifier' => 'NIN'],
            'CAC' => ['identifier' => 'registration number'],
            'PHONE' => ['identifier' => 'phone number'],
            'US_PHONE' => ['identifier' => 'US phone number'],
            'US_SSN' => ['identifier' => 'SSN'],
            default => ['identifier' => 'identifier'],
        };
    }

    private function payloadFor(VerificationService $service, array $validated): array
    {
        return match (strtoupper($service->code)) {
            'BVN' => [
                'bvn' => $validated['identifier'],
                'first_name' => $validated['first_name'] ?? null,
                'middle_name' => $validated['middle_name'] ?? null,
                'last_name' => $validated['last_name'] ?? null,
                'dob' => $validated['dob'] ?? null,
            ],
            'NIN' => [
                'nin' => $validated['identifier'],
                'first_name' => $validated['first_name'] ?? null,
                'middle_name' => $validated['middle_name'] ?? null,
                'last_name' => $validated['last_name'] ?? null,
                'dob' => $validated['dob'] ?? null,
            ],
            'CAC' => [
                'registration_number' => $validated['identifier'],
                'company_name' => $validated['company_name'] ?? null,
            ],
            'PHONE' => [
                'phone' => $validated['identifier'],
                'first_name' => $validated['first_name'] ?? null,
                'middle_name' => $validated['middle_name'] ?? null,
                'last_name' => $validated['last_name'] ?? null,
                'dob' => $validated['dob'] ?? null,
            ],
            'US_PHONE' => [
                'phone' => $validated['identifier'],
                'first_name' => $validated['first_name'] ?? null,
                'middle_name' => $validated['middle_name'] ?? null,
                'last_name' => $validated['last_name'] ?? null,
                'dob' => $validated['dob'] ?? null,
                'country' => 'US',
            ],
            'US_BIODATA' => [
                'first_name' => $validated['first_name'],
                'middle_name' => $validated['middle_name'] ?? null,
                'last_name' => $validated['last_name'],
                'dob' => $validated['dob'],
                'address_line1' => $validated['address_line1'],
                'address_line2' => $validated['address_line2'] ?? null,
                'city' => $validated['city'],
                'state' => $validated['state'],
                'zip' => $validated['zip'] ?? null,
                'country' => 'US',
            ],
            'US_ADDRESS' => [
                'address_line1' => $validated['address_line1'],
                'address_line2' => $validated['address_line2'] ?? null,
                'city' => $validated['city'],
                'state' => $validated['state'],
                'zip' => $validated['zip'],
                'country' => 'US',
            ],
            'US_SSN' => [
                'ssn' => $validated['identifier'],
                'first_name' => $validated['first_name'] ?? null,
                'middle_name' => $validated['middle_name'] ?? null,
                'last_name' => $validated['last_name'] ?? null,
                'dob' => $validated['dob'] ?? null,
                'address_line1' => $validated['address_line1'] ?? null,
                'address_line2' => $validated['address_line2'] ?? null,
                'city' => $validated['city'] ?? null,
                'state' => $validated['state'] ?? null,
                'zip' => $validated['zip'] ?? null,
                'country' => 'US',
            ],
            default => [
                'identifier' => $validated['identifier'],
            ],
        };
    }

    private function fieldBlueprintsFor(VerificationService $service): array
    {
        return match (strtoupper($service->code)) {
            'BVN' => [
                ['name' => 'identifier', 'label' => 'BVN', 'type' => 'text', 'placeholder' => '22123456789', 'helper' => '11-digit Bank Verification Number'],
                ['name' => 'first_name', 'label' => 'First Name', 'type' => 'text', 'placeholder' => 'Ada', 'helper' => 'Optional but improves provider matching'],
                ['name' => 'middle_name', 'label' => 'Middle Name', 'type' => 'text', 'placeholder' => 'N.', 'helper' => 'Optional'],
                ['name' => 'last_name', 'label' => 'Last Name', 'type' => 'text', 'placeholder' => 'Okafor', 'helper' => 'Optional but recommended'],
                ['name' => 'dob', 'label' => 'Date of Birth', 'type' => 'date', 'placeholder' => '', 'helper' => 'Optional, use YYYY-MM-DD'],
            ],
            'NIN' => [
                ['name' => 'identifier', 'label' => 'NIN', 'type' => 'text', 'placeholder' => '12345678901', 'helper' => '11-digit National Identification Number'],
                ['name' => 'first_name', 'label' => 'First Name', 'type' => 'text', 'placeholder' => 'Chinonso', 'helper' => 'Optional'],
                ['name' => 'middle_name', 'label' => 'Middle Name', 'type' => 'text', 'placeholder' => 'K.', 'helper' => 'Optional'],
                ['name' => 'last_name', 'label' => 'Last Name', 'type' => 'text', 'placeholder' => 'Eze', 'helper' => 'Optional'],
                ['name' => 'dob', 'label' => 'Date of Birth', 'type' => 'date', 'placeholder' => '', 'helper' => 'Optional, use YYYY-MM-DD'],
            ],
            'CAC' => [
                ['name' => 'identifier', 'label' => 'Registration Number', 'type' => 'text', 'placeholder' => 'RC1234567', 'helper' => 'Company registration number'],
                ['name' => 'company_name', 'label' => 'Company Name', 'type' => 'text', 'placeholder' => 'Kycappx Labs Limited', 'helper' => 'Optional but useful for reviews'],
            ],
            'PHONE' => [
                ['name' => 'identifier', 'label' => 'Phone Number', 'type' => 'text', 'placeholder' => '08030000000', 'helper' => 'Use a valid Nigerian phone number'],
            ],
            'US_PHONE' => [
                ['name' => 'identifier', 'label' => 'US Phone Number', 'type' => 'text', 'placeholder' => '+14155550123', 'helper' => 'Use a valid US phone number'],
                ['name' => 'first_name', 'label' => 'First Name', 'type' => 'text', 'placeholder' => 'Jordan', 'helper' => 'Optional but useful for phone-owner matching'],
                ['name' => 'last_name', 'label' => 'Last Name', 'type' => 'text', 'placeholder' => 'Cole', 'helper' => 'Optional'],
                ['name' => 'dob', 'label' => 'Date of Birth', 'type' => 'date', 'placeholder' => '', 'helper' => 'Optional, use YYYY-MM-DD'],
            ],
            'US_BIODATA' => [
                ['name' => 'first_name', 'label' => 'First Name', 'type' => 'text', 'placeholder' => 'Avery', 'helper' => 'Required for biodata screening'],
                ['name' => 'middle_name', 'label' => 'Middle Name', 'type' => 'text', 'placeholder' => 'J.', 'helper' => 'Optional'],
                ['name' => 'last_name', 'label' => 'Last Name', 'type' => 'text', 'placeholder' => 'Smith', 'helper' => 'Required for biodata screening'],
                ['name' => 'dob', 'label' => 'Date of Birth', 'type' => 'date', 'placeholder' => '', 'helper' => 'Required'],
                ['name' => 'address_line1', 'label' => 'Address Line 1', 'type' => 'text', 'placeholder' => '123 Main Street', 'helper' => 'Residential address'],
                ['name' => 'address_line2', 'label' => 'Address Line 2', 'type' => 'text', 'placeholder' => 'Apt 4B', 'helper' => 'Optional'],
                ['name' => 'city', 'label' => 'City', 'type' => 'text', 'placeholder' => 'Houston', 'helper' => 'Required'],
                ['name' => 'state', 'label' => 'State', 'type' => 'text', 'placeholder' => 'Texas', 'helper' => 'Required'],
                ['name' => 'zip', 'label' => 'ZIP Code', 'type' => 'text', 'placeholder' => '77002', 'helper' => 'Optional but recommended'],
            ],
            'US_ADDRESS' => [
                ['name' => 'address_line1', 'label' => 'Address Line 1', 'type' => 'text', 'placeholder' => '456 Market Street', 'helper' => 'Street address'],
                ['name' => 'address_line2', 'label' => 'Address Line 2', 'type' => 'text', 'placeholder' => 'Suite 300', 'helper' => 'Optional'],
                ['name' => 'city', 'label' => 'City', 'type' => 'text', 'placeholder' => 'San Francisco', 'helper' => 'Required'],
                ['name' => 'state', 'label' => 'State', 'type' => 'text', 'placeholder' => 'California', 'helper' => 'Required'],
                ['name' => 'zip', 'label' => 'ZIP Code', 'type' => 'text', 'placeholder' => '94105', 'helper' => 'Required'],
            ],
            'US_SSN' => [
                ['name' => 'identifier', 'label' => 'SSN', 'type' => 'text', 'placeholder' => '123456789', 'helper' => 'Social Security Number'],
                ['name' => 'first_name', 'label' => 'First Name', 'type' => 'text', 'placeholder' => 'Taylor', 'helper' => 'Required for stronger matching'],
                ['name' => 'last_name', 'label' => 'Last Name', 'type' => 'text', 'placeholder' => 'Brown', 'helper' => 'Required for stronger matching'],
                ['name' => 'dob', 'label' => 'Date of Birth', 'type' => 'date', 'placeholder' => '', 'helper' => 'Recommended'],
                ['name' => 'address_line1', 'label' => 'Address Line 1', 'type' => 'text', 'placeholder' => '789 Pine Avenue', 'helper' => 'Optional but helpful for manual review'],
                ['name' => 'city', 'label' => 'City', 'type' => 'text', 'placeholder' => 'Atlanta', 'helper' => 'Optional'],
                ['name' => 'state', 'label' => 'State', 'type' => 'text', 'placeholder' => 'Georgia', 'helper' => 'Optional'],
                ['name' => 'zip', 'label' => 'ZIP Code', 'type' => 'text', 'placeholder' => '30303', 'helper' => 'Optional'],
            ],
            default => [
                ['name' => 'identifier', 'label' => 'Identifier', 'type' => 'text', 'placeholder' => 'Enter the request value', 'helper' => 'Provide the primary lookup value for this service'],
            ],
        };
    }

    private function fieldDefaultsFor($user, ?VerificationService $service = null): array
    {
        $defaults = $this->kycStrength->profile($user);

        if (! $service) {
            return $defaults;
        }

        $defaults['identifier'] = match (strtoupper($service->code)) {
            'BVN' => data_get($defaults, 'bvn'),
            'NIN' => data_get($defaults, 'nin'),
            'PHONE', 'US_PHONE' => data_get($defaults, 'phone'),
            'US_SSN' => data_get($defaults, 'ssn'),
            default => data_get($defaults, 'identifier'),
        };

        return $defaults;
    }

    private function priceFor($user, VerificationService $service): float
    {
        $discountRate = $user->currentDiscountRate((float) $this->siteSettings->current()->user_pro_discount_rate);

        return round(max(0, (float) $service->default_price * (1 - ($discountRate / 100))), 2);
    }
}
