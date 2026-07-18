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
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
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

    public function test_admin_edit_registry_page_contains_registry_prop()
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

        $registry = Registry::create([
            'civil_registration_center_id' => $center->id,
            'type' => 'naissance',
            'year' => 2026,
            'number' => 1,
            'reference_prefix' => 'N-2026-DKR',
            'status' => 'open',
            'opening_date' => '2026-07-18',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.registries.edit', $registry->id));
        $response->assertStatus(200);

        $page = $response->original->getData()['page'];
        $this->assertEquals('Admin/Registries/Form', $page['component']);
        $this->assertEquals($registry->id, $page['props']['registry']['id']);
        $this->assertEquals($center->id, $page['props']['registry']['civil_registration_center_id']);
        $this->assertEquals('naissance', $page['props']['registry']['type']);
        $this->assertEquals(2026, $page['props']['registry']['year']);
        $this->assertEquals(1, $page['props']['registry']['number']);
        $this->assertEquals('N-2026-DKR', $page['props']['registry']['reference_prefix']);
        $this->assertEquals('open', $page['props']['registry']['status']);
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

    public function test_supervisor_cannot_delete_registry()
    {
        $supervisor = User::factory()->create();
        $supervisor->assignRole(UserRole::SUPERVISEUR->value);

        $center = CivilRegistrationCenter::create([
            'name' => 'Center 1',
            'code' => 'C1',
            'commune' => 'Dakar',
            'region' => 'Dakar',
            'is_active' => true,
        ]);

        $registry = Registry::create([
            'civil_registration_center_id' => $center->id,
            'type' => 'naissance',
            'year' => 2026,
            'number' => 1,
            'reference_prefix' => 'N-2026-C1',
            'status' => 'open',
        ]);

        $response = $this->actingAs($supervisor)->delete(route('admin.registries.destroy', $registry->id));
        $response->assertStatus(403);
    }

    public function test_supervisor_cannot_modify_registry()
    {
        $supervisor = User::factory()->create();
        $supervisor->assignRole(UserRole::SUPERVISEUR->value);

        $center = CivilRegistrationCenter::create([
            'name' => 'Center 1',
            'code' => 'C1',
            'commune' => 'Dakar',
            'region' => 'Dakar',
            'is_active' => true,
        ]);

        $registry = Registry::create([
            'civil_registration_center_id' => $center->id,
            'type' => 'naissance',
            'year' => 2026,
            'number' => 1,
            'reference_prefix' => 'N-2026-C1',
            'status' => 'open',
        ]);

        // Cannot view edit page
        $response = $this->actingAs($supervisor)->get(route('admin.registries.edit', $registry->id));
        $response->assertStatus(403);

        // Cannot update
        $response = $this->actingAs($supervisor)->put(route('admin.registries.update', $registry->id), [
            'civil_registration_center_id' => $center->id,
            'type' => 'naissance',
            'year' => 2026,
            'number' => 1,
            'reference_prefix' => 'N-2026-NEW-C1',
            'status' => 'open',
        ]);
        $response->assertStatus(403);
    }

    public function test_supervisor_cannot_close_or_reopen_registry()
    {
        $supervisor = User::factory()->create();
        $supervisor->assignRole(UserRole::SUPERVISEUR->value);

        $center = CivilRegistrationCenter::create([
            'name' => 'Center 1',
            'code' => 'C1',
            'commune' => 'Dakar',
            'region' => 'Dakar',
            'is_active' => true,
        ]);

        $registry = Registry::create([
            'civil_registration_center_id' => $center->id,
            'type' => 'naissance',
            'year' => 2026,
            'number' => 1,
            'reference_prefix' => 'N-2026-C1',
            'status' => 'open',
        ]);

        // Cannot close
        $response = $this->actingAs($supervisor)->post(route('admin.registries.close', $registry->id));
        $response->assertStatus(403);

        // Cannot reopen
        $registry->update(['status' => 'closed']);
        $response = $this->actingAs($supervisor)->post(route('admin.registries.reopen', $registry->id));
        $response->assertStatus(403);
    }

    public function test_admin_cannot_delete_registry_with_acts()
    {
        $admin = User::factory()->create();
        $admin->assignRole(UserRole::ADMIN->value);

        $center = CivilRegistrationCenter::create([
            'name' => 'Center 1',
            'code' => 'C1',
            'commune' => 'Dakar',
            'region' => 'Dakar',
            'is_active' => true,
        ]);

        $registry = Registry::create([
            'civil_registration_center_id' => $center->id,
            'type' => 'naissance',
            'year' => 2026,
            'number' => 1,
            'reference_prefix' => 'N-2026-C1',
            'status' => 'open',
        ]);

        // Create an act associated with this registry
        \App\Models\BirthAct::create([
            'registry_id' => $registry->id,
            'reference_number' => 'N-2026-C1-0001',
            'first_name' => 'Child',
            'last_name' => 'Doe',
            'date_of_birth' => '2026-01-01',
            'place_of_birth' => 'Dakar',
            'gender' => 'M',
            'status' => 'brouillon'
        ]);

        $response = $this->actingAs($admin)->delete(route('admin.registries.destroy', $registry->id));
        $response->assertStatus(302);
        $response->assertSessionHas('error', 'Impossible de supprimer ce registre car il contient déjà des actes enregistrés.');
        
        $this->assertDatabaseHas('registries', ['id' => $registry->id]);
    }

    public function test_admin_can_modify_registry_and_cascade_reference_prefix_changes()
    {
        $admin = User::factory()->create();
        $admin->assignRole(UserRole::ADMIN->value);

        $center = CivilRegistrationCenter::create([
            'name' => 'Center 1',
            'code' => 'C1',
            'commune' => 'Dakar',
            'region' => 'Dakar',
            'is_active' => true,
        ]);

        $registry = Registry::create([
            'civil_registration_center_id' => $center->id,
            'type' => 'naissance',
            'year' => 2026,
            'number' => 1,
            'reference_prefix' => 'N-2026-C1',
            'status' => 'open',
        ]);

        // Create acts associated with this registry
        $act = \App\Models\BirthAct::create([
            'registry_id' => $registry->id,
            'reference_number' => 'N-2026-C1-0001',
            'first_name' => 'Child',
            'last_name' => 'Doe',
            'date_of_birth' => '2026-01-01',
            'place_of_birth' => 'Dakar',
            'gender' => 'M',
            'status' => 'brouillon'
        ]);

        $response = $this->actingAs($admin)->put(route('admin.registries.update', $registry->id), [
            'civil_registration_center_id' => $center->id,
            'type' => 'naissance',
            'year' => 2026,
            'number' => 1,
            'reference_prefix' => 'N-2026-MOD-C1',
            'status' => 'open',
        ]);

        $response->assertRedirect(route('admin.registries.index'));
        $this->assertDatabaseHas('registries', [
            'id' => $registry->id,
            'reference_prefix' => 'N-2026-MOD-C1',
        ]);

        // Verify the act reference number has cascaded prefix
        $this->assertEquals('N-2026-MOD-C1-0001', $act->fresh()->reference_number);
    }

    public function test_duplicate_check_for_manual_old_registry_acts()
    {
        $agent = User::factory()->create();
        $agent->givePermissionTo('create-drafts');

        $center = new CivilRegistrationCenter([
            'name' => 'Center 1',
            'code' => 'C1',
            'commune' => 'Dakar',
            'region' => 'Dakar',
            'is_active' => true,
        ]);
        $center->id = 1;
        $center->save();

        $registry = Registry::create([
            'civil_registration_center_id' => $center->id,
            'type' => 'naissance',
            'year' => 2026,
            'number' => 1,
            'reference_prefix' => 'N-2026-C1',
            'status' => 'open',
        ]);

        // Create existing act
        \App\Models\BirthAct::create([
            'registry_id' => $registry->id,
            'reference_number' => 'N-2026-C1-0010',
            'first_name' => 'Existing',
            'last_name' => 'Doe',
            'date_of_birth' => '2026-01-01',
            'place_of_birth' => 'Dakar',
            'gender' => 'M',
            'status' => 'brouillon'
        ]);

        // Try to create another act with the same manual reference number
        $response = $this->actingAs($agent)->post(route('acts.naissance.store'), [
            'registry_id' => $registry->id,
            'is_old_registry' => true,
            'reference_number' => 'N-2026-C1-0010',
            'first_name' => 'DuplicateChild',
            'last_name' => 'Doe',
            'date_of_birth' => '2026-01-01',
            'time_of_birth' => '10:00',
            'place_of_birth' => 'Dakar',
            'health_facility' => 'Centre de Sante',
            'act_registration_date' => '2026-01-02',
            'gender' => 'M',
            'father_name' => 'Father Doe',
            'mother_name' => 'Mother Doe',
            'parents_metadata' => [
                'father_profession' => 'Ingénieur',
                'father_date_of_birth' => '1990-01-01',
                'father_place_of_birth' => 'Dakar',
                'father_domicile' => 'Dakar Plateau',
                'mother_profession' => 'Médecin',
                'mother_date_of_birth' => '1992-02-02',
                'mother_place_of_birth' => 'Dakar',
                'mother_domicile' => 'Dakar Plateau',
            ],
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['reference_number']);
    }

    public function test_excel_import_and_download_permissions()
    {
        $agent = User::factory()->create();
        $agent->assignRole(UserRole::OFFICIER->value); // restricted role
        $agent->givePermissionTo('create-drafts');

        $supervisor = User::factory()->create();
        $supervisor->assignRole(UserRole::SUPERVISEUR->value); // allowed role
        $supervisor->givePermissionTo('create-drafts');

        // Agent should be forbidden
        $response = $this->actingAs($agent)->get(route('acts.naissance.template'));
        $response->assertStatus(403);

        // Supervisor should be allowed
        $response = $this->actingAs($supervisor)->get(route('acts.naissance.template'));
        $response->assertStatus(200);
    }
}
