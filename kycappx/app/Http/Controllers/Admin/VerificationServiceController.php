<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\VerificationService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class VerificationServiceController extends Controller
{
    public function index(): View
    {
        return view('admin.services.index', [
            'services' => VerificationService::query()
                ->withCount('verificationRequests')
                ->orderByDesc('is_active')
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function update(Request $request, VerificationService $verificationService): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', 'max:50'],
            'country' => ['required', 'string', 'size:2'],
            'default_price' => ['required', 'numeric', 'min:0'],
            'default_cost' => ['required', 'numeric', 'min:0'],
            'required_fields' => ['nullable', 'string', 'max:500'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $requiredFields = collect(explode(',', (string) ($validated['required_fields'] ?? '')))
            ->map(fn ($field) => trim($field))
            ->filter()
            ->values()
            ->all();

        $verificationService->update([
            'name' => $validated['name'],
            'type' => strtolower($validated['type']),
            'country' => strtoupper($validated['country']),
            'default_price' => $validated['default_price'],
            'default_cost' => $validated['default_cost'],
            'required_fields' => $requiredFields,
            'is_active' => $request->boolean('is_active'),
        ]);

        AuditLog::create([
            'user_id' => $request->user()->id,
            'action' => 'admin.service.updated',
            'target_type' => VerificationService::class,
            'target_id' => (string) $verificationService->id,
            'meta' => [
                'code' => $verificationService->code,
                'is_active' => $verificationService->is_active,
                'default_price' => $verificationService->default_price,
            ],
        ]);

        return redirect()
            ->route('admin.services.index')
            ->with('status', $verificationService->name.' updated successfully.');
    }
}
