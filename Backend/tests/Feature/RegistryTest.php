<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\CivilRegistrationCenter;
use App\Models\Registry;
use App\Enums\UserRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RegistryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Run seeders or manually create roles and permissions for tests
        // Let's create roles
        $adminRole = Role::create(['name' => UserRole::ADMIN->value, 'guard_name' => 'web']);
        $supervisorRole = Role::create(['name' => UserRole::SUPERVISEUR->value, 'guard_name' => 'web']);
        $officerRole = Role::create(['name' => UserRole::OFFICIER->value, 'guard_name' => 'web']);

        // Create permissions
        $manageRegistriesPerm = Permission::create(['name' => 'manage-registries', 'guard_name' => 'web']);
        $manageCentersPerm = Permission::create(['name' => 'manage-centers', 'guard_name' => 'web']);

        // Assign permissions to roles
        $adminRole->givePermissionTo([$manageRegistriesPerm, $manageCentersPerm]);
        $supervisorRole->givePermissionTo([$manageRegistriesPerm]);
    }

    public function test_admin_can_access_registries_endpoints()
    {
        $admin = User::factory()->create();
        $admin->assignRole(UserRole::ADMIN->value);

        $center = CivilRegistrationCenter::create([
            'name' => 'Dakar Center',
            'code' => 'DKR',
            'commune' => 'Dakar',
            'region' => 'Dakar',
            'is_active' => true,
        ]);

        // Can list
        $response = $this->actingAs($admin)->get(route('admin.registries.index'));
        $response->assertStatus(200);

        // Can create
        $response = $this->actingAs($admin)->post(route('admin.registries.store'), [
            'civil_registration_center_id' => $center->id,
            'type' => 'naissance',
            'year' => 2026,
            'reference_prefix' => 'N-2026-DKR',
            'status' => 'open',
            'opening_date' => now()->toDateString(),
        ]);

        $response->assertRedirect(route('admin.registries.index'));
        $this->assertDatabaseHas('registries', [
            'civil_registration_center_id' => $center->id,
            'type' => 'naissance',
            'year' => 2026,
            'reference_prefix' => 'N-2026-DKR',
        ]);
    }

    public function test_supervisor_can_access_registries_endpoints()
    {
        $supervisor = User::factory()->create();
        $supervisor->assignRole(UserRole::SUPERVISEUR->value);

        $center = CivilRegistrationCenter::create([
            'name' => 'Ziguinchor Center',
            'code' => 'ZIG',
            'commune' => 'Ziguinchor',
            'region' => 'Ziguinchor',
            'is_active' => true,
        ]);

        // Can list
        $response = $this->actingAs($supervisor)->get(route('admin.registries.index'));
        $response->assertStatus(200);

        // Can create
        $response = $this->actingAs($supervisor)->post(route('admin.registries.store'), [
            'civil_registration_center_id' => $center->id,
            'type' => 'naissance',
            'year' => 2026,
            'reference_prefix' => 'N-2026-ZIG',
            'status' => 'open',
            'opening_date' => now()->toDateString(),
        ]);

        $response->assertRedirect(route('admin.registries.index'));
        $this->assertDatabaseHas('registries', [
            'civil_registration_center_id' => $center->id,
            'type' => 'naissance',
            'year' => 2026,
            'reference_prefix' => 'N-2026-ZIG',
        ]);
    }

    public function test_unauthorized_user_cannot_access_registries_endpoints()
    {
        $officer = User::factory()->create();
        $officer->assignRole(UserRole::OFFICIER->value);

        $center = CivilRegistrationCenter::create([
            'name' => 'Ziguinchor Center',
            'code' => 'ZIG',
            'commune' => 'Ziguinchor',
            'region' => 'Ziguinchor',
            'is_active' => true,
        ]);

        // Cannot list
        $response = $this->actingAs($officer)->get(route('admin.registries.index'));
        $response->assertStatus(403);

        // Cannot create
        $response = $this->actingAs($officer)->post(route('admin.registries.store'), [
            'civil_registration_center_id' => $center->id,
            'type' => 'naissance',
            'year' => 2026,
            'reference_prefix' => 'N-2026-ZIG',
            'status' => 'open',
            'opening_date' => now()->toDateString(),
        ]);

        $response->assertStatus(403);
    }
}
