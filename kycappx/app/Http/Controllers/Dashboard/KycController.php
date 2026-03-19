<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\VerificationService;
use App\Services\Kyc\KycStrengthService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class KycController extends Controller
{
    public function __construct(private KycStrengthService $kycStrength)
    {
    }

    public function edit(Request $request): View
    {
        $user = $request->user();
        $snapshot = $this->kycStrength->snapshot($user);

        return view('dashboard.kyc', [
            'profile' => $snapshot['profile'],
            'snapshot' => $snapshot,
            'recommendedServices' => VerificationService::query()
                ->whereIn('code', ['PHONE', 'NIN', 'BVN', 'US_PHONE'])
                ->active()
                ->orderBy('country')
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'middle_name' => ['nullable', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'dob' => ['required', 'date'],
            'phone' => ['required', 'string', 'max:30'],
            'nin' => ['nullable', 'digits:11'],
            'bvn' => ['nullable', 'digits:11'],
            'address_line1' => ['required', 'string', 'max:255'],
            'address_line2' => ['nullable', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:120'],
            'state' => ['required', 'string', 'max:120'],
            'zip' => ['nullable', 'string', 'max:20'],
            'country' => ['required', Rule::in(['NG', 'US'])],
        ]);

        $this->kycStrength->persist($request->user(), $validated);

        return redirect()
            ->route('kyc.edit')
            ->with('status', 'KYC profile updated successfully.');
    }
}
