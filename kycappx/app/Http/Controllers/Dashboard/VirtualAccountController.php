<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\DedicatedVirtualAccount;
use App\Services\Billing\VirtualAccountService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use RuntimeException;

class VirtualAccountController extends Controller
{
    public function store(Request $request, string $provider, VirtualAccountService $virtualAccounts): RedirectResponse
    {
        $provider = strtolower($provider);

        $validated = $request->validate([
            'provider' => ['nullable', Rule::in(['paystack', 'kora'])],
            'bvn' => [Rule::requiredIf($provider === 'kora'), 'nullable', 'digits:11'],
            'nin' => ['nullable', 'digits:11'],
        ]);

        try {
            $virtualAccounts->assign($request->user(), $provider, $validated);
        } catch (RuntimeException $exception) {
            return back()->withErrors(['virtual_accounts' => $exception->getMessage()]);
        }

        return redirect()
            ->route('wallet')
            ->with('status', strtoupper($provider).' dedicated account is ready for wallet topups.');
    }

    public function requery(
        Request $request,
        DedicatedVirtualAccount $dedicatedVirtualAccount,
        VirtualAccountService $virtualAccounts
    ): RedirectResponse {
        abort_unless($dedicatedVirtualAccount->user_id === $request->user()->id, 403);

        try {
            $virtualAccounts->requery($dedicatedVirtualAccount);
        } catch (RuntimeException $exception) {
            return back()->withErrors(['virtual_accounts' => $exception->getMessage()]);
        }

        return back()->with('status', 'The provider is checking that dedicated account for new transfers now.');
    }
}
