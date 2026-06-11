<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::updateOrCreate(
            ['email' => 'admin@e-cre.sn'],
            [
                'name' => 'Directeur E-CRE',
                'password' => Hash::make('password'),
                'telephone' => '770000000',
                'email_verified_at' => now(),
            ]
        );

        // Ensure the role exists first
        if (Role::where('name', \App\Enums\UserRole::ADMIN->value)->exists()) {
            $admin->syncRoles([\App\Enums\UserRole::ADMIN->value]);
        }
    }
}
