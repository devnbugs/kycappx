<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('username')->nullable()->after('name')->unique();
            $table->string('phone')->nullable()->after('email');
            $table->string('company_name')->nullable()->after('phone');
            $table->string('timezone')->default('UTC')->after('company_name');
            $table->string('theme_preference')->default('system')->after('timezone');
            $table->string('status')->default('active')->after('theme_preference')->index();
            $table->json('settings')->nullable()->after('status');
            $table->timestamp('last_login_at')->nullable()->after('remember_token');
        });

        $users = DB::table('users')
            ->select(['id', 'name', 'email'])
            ->orderBy('id')
            ->get();

        foreach ($users as $user) {
            $seed = Str::lower(
                preg_replace('/[^a-z0-9._-]/', '', Str::of($user->email)->before('@')->value())
                    ?: preg_replace('/[^a-z0-9._-]/', '', Str::slug((string) $user->name, ''))
                    ?: 'user'.$user->id
            );

            $username = $seed;
            $counter = 1;

            while (
                DB::table('users')
                    ->where('username', $username)
                    ->where('id', '!=', $user->id)
                    ->exists()
            ) {
                $username = $seed.$counter;
                $counter++;
            }

            DB::table('users')
                ->where('id', $user->id)
                ->update([
                    'username' => $username,
                    'status' => 'active',
                    'theme_preference' => 'system',
                    'timezone' => 'UTC',
                    'settings' => json_encode([
                        'security_alerts' => true,
                        'monthly_reports' => true,
                        'marketing_emails' => false,
                    ], JSON_THROW_ON_ERROR),
                ]);
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropUnique(['username']);
            $table->dropColumn([
                'username',
                'phone',
                'company_name',
                'timezone',
                'theme_preference',
                'status',
                'settings',
                'last_login_at',
            ]);
        });
    }
};
