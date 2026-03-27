<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\VerificationRequest;
use App\Models\VerificationService;
use App\Services\Billing\WalletService;
use App\Services\Kyc\KycStrengthService;
use App\Services\SiteSettings;
use App\Services\Verification\IdentityEngineRegistry;
use App\Services\Verification\VerificationCatalogService;
use App\Services\Verification\VerificationOrchestrator;
use App\Services\Verification\VerificationResultPresenter;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class VerificationController extends Controller
{
    public function __construct(
        private WalletService $walletService,
        private SiteSettings $siteSettings,
        private VerificationOrchestrator $verificationOrchestrator,
        private KycStrengthService $kycStrength,
        private VerificationCatalogService $verificationCatalog,
        private IdentityEngineRegistry $identityEngines,
        private VerificationResultPresenter $resultPresenter,
    ) {
    }

    public function index(Request $request): View
    {
        return view('dashboard.verifications.index', [
            'wallet' => $this->walletService->ensureWallet($request->user()->id),
            'verifications' => $request->user()
                ->verificationRequests()
                ->with(['service', 'attempts'])
                ->latest()
                ->paginate(10),
            'providerLabels' => collect($this->identityEngines->providerCodes())
                ->mapWithKeys(fn (string $provider) => [$provider => $this->identityEngines->publicLabel($provider)])
                ->all(),
        ]);
    }

    public function create(Request $request): View
    {
        abort_unless($this->siteSettings->current()->verification_enabled, 403, 'Verification requests are currently disabled.');

        $user = $request->user();
        $services = $this->verificationCatalog
            ->filterLaunchable(VerificationService::query()
            ->active()
            ->orderBy('name')
            ->get())
            ->sortBy(fn (VerificationService $service) => sprintf(
                '%s-%s',
                data_get($this->verificationCatalog->definitionFor($service), 'service.featured', false) ? '0' : '1',
                $service->name
            ))
            ->values();

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
            'serviceEngineVersions' => $services
                ->mapWithKeys(fn (VerificationService $service) => [
                    $service->id => collect($this->identityEngines->availableProvidersForService($service))
                        ->map(fn (string $provider) => $this->identityEngines->publicLabel($provider))
                        ->values()
                        ->all(),
                ])
                ->all(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless($this->siteSettings->current()->verification_enabled, 403, 'Verification requests are currently disabled.');

        $service = VerificationService::query()
            ->active()
            ->whereKey($request->integer('service_id'))
            ->firstOrFail();

        abort_unless($this->verificationCatalog->isLaunchable($service), 403, 'This verification service is currently unavailable.');

        $validated = $request->validate(
            $this->rulesFor($service),
            [],
            $this->attributeNamesFor($service)
        );

        $exclusiveFieldErrors = $this->verificationCatalog->validateExclusiveFields($service, $validated);

        if ($exclusiveFieldErrors !== []) {
            throw ValidationException::withMessages($exclusiveFieldErrors);
        }

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
            default => 'Verification submitted successfully.',
        };

        return redirect()
            ->route('verifications.show', $verificationRequest)
            ->with('status', $message);
    }

    public function show(Request $request, VerificationRequest $verificationRequest): View
    {
        $verification = $this->ownedVerification($request, $verificationRequest);

        return view('dashboard.verifications.show', [
            'verification' => $verification,
            'report' => $this->resultPresenter->present($verification, true),
        ]);
    }

    public function download(Request $request, VerificationRequest $verificationRequest): StreamedResponse
    {
        $verification = $this->ownedVerification($request, $verificationRequest);
        $report = $this->resultPresenter->present($verification, true);
        $mode = $this->resolvedPrintMode((string) $request->query('mode', 'standard'), $report['printModes']);
        $html = view('verifications.print', [
            'verification' => $verification,
            'report' => $report,
            'printMode' => $mode,
        ])->render();
        $filename = str_replace('.html', '-'.$mode.'.html', $report['downloadName']);

        return response()->streamDownload(function () use ($html): void {
            echo $html;
        }, $filename, [
            'Content-Type' => 'text/html; charset=UTF-8',
        ]);
    }

    public function print(Request $request, VerificationRequest $verificationRequest): View
    {
        $verification = $this->ownedVerification($request, $verificationRequest);
        $report = $this->resultPresenter->present($verification, true);

        return view('verifications.print', [
            'verification' => $verification,
            'report' => $report,
            'printMode' => $this->resolvedPrintMode((string) $request->query('mode', 'standard'), $report['printModes']),
        ]);
    }

    private function rulesFor(VerificationService $service): array
    {
        return [
            'service_id' => ['required', 'integer', 'exists:verification_services,id'],
            ...$this->verificationCatalog->validationRulesFor($service),
        ];
    }

    private function attributeNamesFor(VerificationService $service): array
    {
        return $this->verificationCatalog->attributeNamesFor($service);
    }

    private function payloadFor(VerificationService $service, array $validated): array
    {
        return $this->verificationCatalog->payloadFor($service, $validated);
    }

    private function fieldBlueprintsFor(VerificationService $service): array
    {
        return $this->verificationCatalog->fieldBlueprintsFor($service);
    }

    private function fieldDefaultsFor($user, ?VerificationService $service = null): array
    {
        $defaults = $this->kycStrength->profile($user);

        if (! $service) {
            return $defaults;
        }

        return $this->verificationCatalog->defaultValuesFor($service, $defaults);
    }

    private function priceFor($user, VerificationService $service): float
    {
        $discountRate = $user->currentDiscountRate((float) $this->siteSettings->current()->user_pro_discount_rate);

        return round(max(0, (float) $service->default_price * (1 - ($discountRate / 100))), 2);
    }

    private function ownedVerification(Request $request, VerificationRequest $verificationRequest): VerificationRequest
    {
        abort_unless((int) $verificationRequest->user_id === (int) $request->user()->id, 404);

        return $verificationRequest->loadMissing(['service', 'attempts']);
    }

    private function resolvedPrintMode(string $mode, array $availableModes): string
    {
        return in_array($mode, $availableModes, true) ? $mode : $availableModes[0];
    }
}
