<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\ApiKey;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ApiKeyController extends Controller
{
    public function index(Request $request): View
    {
        return view('dashboard.api-keys', [
            'apiKeys' => $request->user()
                ->apiKeys()
                ->latest()
                ->paginate(10),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:50'],
        ]);

        $rawKey = 'kyc_live_'.Str::lower(Str::random(32));

        ApiKey::create([
            'user_id' => $request->user()->id,
            'name' => $validated['name'],
            'key_hash' => hash('sha256', $rawKey),
            'prefix' => Str::substr($rawKey, 0, 12),
            'abilities' => ['verification:create', 'verification:read', 'wallet:read'],
            'is_active' => true,
        ]);

        return redirect()
            ->route('api.keys')
            ->with('status', 'A new API key has been generated.')
            ->with('generated_api_key', $rawKey);
    }

    public function destroy(Request $request, ApiKey $apiKey): RedirectResponse
    {
        abort_unless($apiKey->user_id === $request->user()->id, 403);

        if ($apiKey->is_active) {
            $apiKey->update(['is_active' => false]);
        }

        return redirect()
            ->route('api.keys')
            ->with('status', 'API key revoked.');
    }
}
