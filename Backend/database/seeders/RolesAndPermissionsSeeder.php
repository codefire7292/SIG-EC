<?php

declare(strict_types=1);

namespace Database\Seeders;

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
            'validate-chapters',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // ---------------------------------------------------------------
        // Roles
        // ---------------------------------------------------------------
        $roles = [
            'Directeur'          => ['validate-chapters'],
            'Secrétaire'         => [],
            'Formateur'          => ['validate-chapters'],
            'Responsable Groupe' => [],
            'Apprenant'          => [],
            'Stagiaire'          => [],
        ];

        foreach ($roles as $roleName => $rolePermissions) {
            /** @var Role $role */
            $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);

            if (!empty($rolePermissions)) {
                $role->givePermissionTo($rolePermissions);
            }
        }

        $this->command->info('✅ Rôles et permissions créés avec succès.');
        $this->command->table(
            ['Rôle', 'Permissions'],
            collect($roles)->map(fn ($perms, $name) => [
                $name,
                empty($perms) ? '—' : implode(', ', $perms),
            ])->values()->toArray()
        );
    }
}
