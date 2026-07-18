<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\BirthAct;
use App\Models\Registry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class RegistryLimitTest extends TestCase
{
    use RefreshDatabase;

    protected $registry;
    protected $adminUser;

    protected function setUp(): void
    {
        parent::setUp();
        
        $center = new \App\Models\CivilRegistrationCenter([
            'name' => 'Centre Test',
            'code' => 'TEST01',
            'commune' => 'Dakar',
            'region' => 'Dakar'
        ]);
        $center->id = 1;
        $center->save();

        $this->registry = Registry::create([
            'civil_registration_center_id' => $center->id,
            'name' => 'Registre Test', 
            'type' => 'naissance',
            'year' => 2026, 
            'number' => 1,
            'reference_prefix' => 'TEST',
            'status' => 'open'
        ]);

        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
        $this->adminUser = User::factory()->create();
        $this->adminUser->assignRole(\App\Enums\UserRole::ADMIN->value);
    }

    public function test_registry_limit_blocks_creation_beyond_100(): void
    {
        $this->actingAs($this->adminUser);

        // Fill up the registry to 100 acts
        for ($i = 1; $i <= 100; $i++) {
            BirthAct::create([
                'registry_id' => $this->registry->id,
                'reference_number' => 'TEST-' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'first_name' => 'Child' . $i,
                'last_name' => 'Doe',
                'date_of_birth' => '2026-01-01',
                'place_of_birth' => 'Dakar',
                'gender' => 'M',
                'status' => 'brouillon'
            ]);
        }

        // Try to add the 101st act via store route
        $response = $this->post(route('acts.naissance.store'), [
            'registry_id' => $this->registry->id,
            'first_name' => 'ExceedingChild',
            'last_name' => 'Doe',
            'date_of_birth' => '2026-01-01',
            'time_of_birth' => '10:00',
            'place_of_birth' => 'Dakar',
            'health_facility' => 'Centre de Santé',
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
            'doc_cni_pere' => UploadedFile::fake()->create('cni_pere.pdf', 100, 'application/pdf'),
            'doc_cni_mere' => UploadedFile::fake()->create('cni_mere.pdf', 100, 'application/pdf'),
            'doc_acte_naissance' => UploadedFile::fake()->create('acte_naissance.pdf', 100, 'application/pdf'),
            'doc_cni_declarant' => UploadedFile::fake()->create('cni_declarant.pdf', 100, 'application/pdf'),
        ]);

        // Should return redirect (back) with errors
        $response->assertStatus(302);
        $response->assertSessionHasErrors(['registry_id']);
        
        // Assert the database still only has 100 acts for this registry
        $this->assertEquals(100, BirthAct::where('registry_id', $this->registry->id)->count());
    }

    public function test_registry_limit_auto_rolls_over_on_reassignment_beyond_100(): void
    {
        $this->actingAs($this->adminUser);

        // Target registry for year 2025
        $center = $this->registry->civil_registration_center_id;
        $registry2025 = Registry::create([
            'civil_registration_center_id' => $center,
            'name' => 'Registre 2025', 
            'type' => 'naissance',
            'year' => 2025, 
            'number' => 1,
            'reference_prefix' => 'TEST-2025',
            'status' => 'open'
        ]);

        // Fill registry2025 to 100 acts
        for ($i = 1; $i <= 100; $i++) {
            BirthAct::create([
                'registry_id' => $registry2025->id,
                'reference_number' => 'TEST-2025-' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'first_name' => 'Child' . $i,
                'last_name' => 'Doe',
                'date_of_birth' => '2025-01-01',
                'place_of_birth' => 'Dakar',
                'gender' => 'M',
                'status' => 'brouillon'
            ]);
        }

        // Create 1 act in registry 2026
        $act2026 = BirthAct::create([
            'registry_id' => $this->registry->id,
            'reference_number' => 'TEST-0001',
            'first_name' => 'Original',
            'last_name' => 'Doe',
            'date_of_birth' => '2026-01-01',
            'place_of_birth' => 'Dakar',
            'gender' => 'M',
            'status' => 'brouillon'
        ]);

        // Try to update its date to 2025 (which would reassign it to registry2025)
        $response = $this->patch(route('acts.naissance.update', $act2026->id), [
            'registry_id' => $this->registry->id,
            'first_name' => 'Original',
            'last_name' => 'Doe',
            'date_of_birth' => '2025-01-01', // change year to 2025
            'time_of_birth' => '10:00',
            'place_of_birth' => 'Dakar',
            'health_facility' => 'Centre de Santé',
            'act_registration_date' => '2025-01-02',
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

        // Should return redirect (back) with success status
        $response->assertStatus(302);
        
        // Assert a new registry (number 2) was automatically created for 2025
        $newRegistry = Registry::where('year', 2025)->where('number', 2)->first();
        $this->assertNotNull($newRegistry);
        
        // Assert it was reassigned to the new registry
        $this->assertEquals($newRegistry->id, $act2026->fresh()->registry_id);
    }

    public function test_registry_limit_blocks_reassignment_to_closed_registry(): void
    {
        $this->actingAs($this->adminUser);

        // Target registry for year 2025 is closed
        $center = $this->registry->civil_registration_center_id;
        $registry2025Closed = Registry::create([
            'civil_registration_center_id' => $center,
            'name' => 'Registre 2025 Clos', 
            'type' => 'naissance',
            'year' => 2025, 
            'number' => 1,
            'reference_prefix' => 'TEST-2025-C',
            'status' => 'closed'
        ]);

        // Create 1 act in registry 2026
        $act2026 = BirthAct::create([
            'registry_id' => $this->registry->id,
            'reference_number' => 'TEST-0001',
            'first_name' => 'Original',
            'last_name' => 'Doe',
            'date_of_birth' => '2026-01-01',
            'place_of_birth' => 'Dakar',
            'gender' => 'M',
            'status' => 'brouillon'
        ]);

        // If they manually passed registry_id of a closed registry, it must fail
        $response = $this->patch(route('acts.naissance.update', $act2026->id), [
            'registry_id' => $registry2025Closed->id,
            'first_name' => 'Original',
            'last_name' => 'Doe',
            'date_of_birth' => '2025-01-01', // change year to 2025
            'time_of_birth' => '10:00',
            'place_of_birth' => 'Dakar',
            'health_facility' => 'Centre de Santé',
            'act_registration_date' => '2025-01-02',
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
        $response->assertSessionHas('error');
        $this->assertEquals($this->registry->id, $act2026->fresh()->registry_id);
    }
}
