<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\CivilRegistrationCenter;
use App\Models\Registry;
use App\Enums\UserRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CenterControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
    }

    public function test_updating_center_code_cascades_to_registries_and_acts()
    {
        $admin = User::factory()->create();
        $admin->assignRole(UserRole::ADMIN->value);

        // 1. Create a center with code 'DKR'
        $center = CivilRegistrationCenter::create([
            'name' => 'Dakar Center',
            'code' => 'DKR',
            'commune' => 'Dakar',
            'region' => 'Dakar',
            'is_active' => true,
        ]);

        // 2. Create a registry for this center
        $registry = Registry::create([
            'civil_registration_center_id' => $center->id,
            'type' => 'naissance',
            'year' => 2026,
            'number' => 1,
            'reference_prefix' => 'N-2026-DKR',
            'status' => 'open',
        ]);

        // 3. Create a birth act associated with the registry
        $birthActId = DB::table('birth_acts')->insertGetId([
            'uuid' => \Illuminate\Support\Str::uuid(),
            'registry_id' => $registry->id,
            'reference_number' => 'N-2026-DKR-0001',
            'first_name' => 'Jean',
            'last_name' => 'Dupont',
            'gender' => 'M',
            'date_of_birth' => '2026-01-01',
            'time_of_birth' => '12:00:00',
            'place_of_birth' => 'Dakar',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 4. Create a civil certificate associated with the center and registry
        $certId = DB::table('civil_certificates')->insertGetId([
            'uuid' => \Illuminate\Support\Str::uuid(),
            'civil_registration_center_id' => $center->id,
            'registry_id' => $registry->id,
            'type' => 'naissance',
            'center' => 'DKR',
            'reference_number' => 'N-2026-DKR-0001',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 5. Update the center's code to 'DKR2'
        $response = $this->actingAs($admin)->put(route('admin.centers.update', $center->id), [
            'name' => 'Dakar Center Edited',
            'code' => 'DKR2',
            'commune' => 'Dakar',
            'region' => 'Dakar',
            'is_active' => true,
        ]);

        $response->assertRedirect(route('admin.centers.index'));

        // Assert center code was updated
        $this->assertDatabaseHas('civil_registration_centers', [
            'id' => $center->id,
            'code' => 'DKR2',
        ]);

        // Assert registry reference_prefix was updated
        $this->assertDatabaseHas('registries', [
            'id' => $registry->id,
            'reference_prefix' => 'N-2026-DKR2',
        ]);

        // Assert birth act reference_number was updated
        $this->assertDatabaseHas('birth_acts', [
            'id' => $birthActId,
            'reference_number' => 'N-2026-DKR2-0001',
        ]);

        // Assert civil certificate reference_number and center were updated
        $this->assertDatabaseHas('civil_certificates', [
            'id' => $certId,
            'center' => 'DKR2',
            'reference_number' => 'N-2026-DKR2-0001',
        ]);
    }
}
