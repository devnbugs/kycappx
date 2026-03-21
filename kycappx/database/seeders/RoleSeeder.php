<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissionNames = [
            'admin.services.manage',
            'admin.providers.manage',
        ];
        $permissions = collect($permissionNames)
            ->map(fn (string $permission) => Permission::findOrCreate($permission, 'web'));

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (['super-admin', 'admin', 'support', 'customer', 'user-pro', 'developer'] as $roleName) {
            $role = Role::firstOrCreate(['name' => $roleName]);

            if (in_array($roleName, ['super-admin', 'admin'], true)) {
                $role->syncPermissions($permissions);
            }
        }
    }
}
