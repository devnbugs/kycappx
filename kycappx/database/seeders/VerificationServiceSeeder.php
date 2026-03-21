<?php

namespace Database\Seeders;

use App\Models\VerificationService;
use App\Services\Verification\VerificationCatalogService;
use Illuminate\Database\Seeder;

class VerificationServiceSeeder extends Seeder
{
    public function run(): void
    {
        $services = app(VerificationCatalogService::class)->seedableServices();
        $catalogCodes = collect($services)->pluck('code')->filter()->values();

        VerificationService::query()
            ->whereNotIn('code', $catalogCodes)
            ->doesntHave('verificationRequests')
            ->delete();

        VerificationService::query()
            ->whereNotIn('code', $catalogCodes)
            ->whereHas('verificationRequests')
            ->update(['is_active' => false]);

        foreach ($services as $service) {
            VerificationService::updateOrCreate(
                ['code' => $service['code']],
                $service
            );
        }
    }
}
