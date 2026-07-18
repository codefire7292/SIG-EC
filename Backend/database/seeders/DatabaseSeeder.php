<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use App\Models\CivilRegistrationCenter;
use App\Enums\UserRole;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call([
            RolesAndPermissionsSeeder::class,
        ]);

        // Create Default Center (ID = 1)
        CivilRegistrationCenter::firstOrCreate(
            ['id' => 1],
            [
                'name' => 'Centre Principal de Enampore',
                'code' => 'C1',
                'commune' => 'Enampore',
                'region' => 'Ziguinchor',
                'is_active' => true,
            ]
        );

        // Create Super Admin User
        $admin = User::firstOrCreate(
            ['email' => 'youssouphbadji2013@gmail.com'],
            [
                'name' => 'Administrateur technique',
                'password' => Hash::make('password'),
            ]
        );
        $admin->syncRoles([UserRole::ADMIN->value]);
    }
}
