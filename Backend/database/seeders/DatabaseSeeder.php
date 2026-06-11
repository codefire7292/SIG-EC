<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use App\Enums\UserRole;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RolesAndPermissionsSeeder::class,
        ]);

        // 1. Admin
        $admin = User::firstOrCreate(
            ['email' => 'admin@sig-ec.sn'],
            [
                'name' => 'Administrateur Système',
                'password' => Hash::make('password'),
            ]
        );
        $admin->syncRoles([UserRole::ADMIN->value]);

        // 2. Officier
        $officier = User::firstOrCreate(
            ['email' => 'officier@sig-ec.sn'],
            [
                'name' => 'Officier État Civil',
                'password' => Hash::make('password'),
            ]
        );
        $officier->syncRoles([UserRole::OFFICIER->value]);

        // 3. Superviseur
        $superviseur = User::firstOrCreate(
            ['email' => 'superviseur@sig-ec.sn'],
            [
                'name' => 'Superviseur de Centre',
                'password' => Hash::make('password'),
            ]
        );
        $superviseur->syncRoles([UserRole::SUPERVISEUR->value]);

        // 4. Agent
        $agent = User::firstOrCreate(
            ['email' => 'agent@sig-ec.sn'],
            [
                'name' => 'Agent de Saisie',
                'password' => Hash::make('password'),
            ]
        );
        $agent->syncRoles([UserRole::AGENT->value]);

        // 5. Maire
        $maire = User::firstOrCreate(
            ['email' => 'maire@sig-ec.sn'],
            [
                'name' => 'Maire',
                'password' => Hash::make('password'),
            ]
        );
        $maire->syncRoles([UserRole::MAIRE->value]);
    }
}
