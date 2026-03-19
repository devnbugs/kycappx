<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\VerificationService;
use App\Services\Billing\WalletService;
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
        private VerificationOrchestrator $verificationOrchestrator
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
        $services = VerificationService::query()
            ->active()
            ->orderBy('name')
            ->get();

        $selectedService = $services->firstWhere('id', (int) $request->query('service'));

        return view('dashboard.verifications.create', [
            'wallet' => $this->walletService->ensureWallet($request->user()->id),
            'services' => $services,
            'selectedService' => $selectedService,
            'fieldBlueprints' => $selectedService ? $this->fieldBlueprintsFor($selectedService) : [],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
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
        $price = (float) $service->default_price;

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
            default => [
                ['name' => 'identifier', 'label' => 'Identifier', 'type' => 'text', 'placeholder' => 'Enter the request value', 'helper' => 'Provide the primary lookup value for this service'],
            ],
        };
    }
}
