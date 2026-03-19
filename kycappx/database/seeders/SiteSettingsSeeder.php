<?php

namespace Database\Seeders;

use App\Models\SiteSetting;
use App\Services\SiteSettings;
use Illuminate\Database\Seeder;

class SiteSettingsSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = app(SiteSettings::class)->defaults();

        SiteSetting::query()->updateOrCreate(
            ['id' => 1],
            $defaults
        );
    }
}
