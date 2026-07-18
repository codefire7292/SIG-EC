<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\UserRole;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // ---------------------------------------------------------------
        // Permissions
        // ---------------------------------------------------------------
        $permissions = [
            'manage-users',
            'manage-centers',
            'manage-registries',
            'view-logs',
            'full-access',
            'validate-final',
            'sign-legally',
            'view-registries',
            'validate-intermediate',
            'manage-corrections',
            'create-drafts',
            'upload-docs',
            'print-extracts',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // ---------------------------------------------------------------
        // Roles Mapping
        // ---------------------------------------------------------------
        $roles = [
            UserRole::ADMIN->value => [
                'manage-users',
                'manage-centers',
                'manage-registries',
                'view-logs',
                'view-registries',
                'create-drafts',
                'full-access', // Added for testing
            ],
            UserRole::MAIRE->value => [
                'create-drafts',
                'full-access',
                'sign-legally',
                'view-registries',
                'print-extracts',
            ],
            UserRole::OFFICIER->value => [
                'create-drafts',
                'full-access',
                'validate-final',
                'view-registries',
                'print-extracts',
            ],
            UserRole::SUPERVISEUR->value => [
                'view-registries',
                'manage-registries',
                'validate-intermediate',
                'manage-corrections',
                'print-extracts',
                'create-drafts',
                'upload-docs',
            ],
            UserRole::AGENT->value => [
                'create-drafts',
                'upload-docs',
                'print-extracts',
                'view-registries',
            ],
        ];

        foreach ($roles as $roleName => $rolePermissions) {
            /** @var Role $role */
            $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);

            if (!empty($rolePermissions)) {
                $role->syncPermissions($rolePermissions);
            }
        }

        $this->command->info('✅ Rôles et permissions SIG-EC créés avec succès.');
    }
}
