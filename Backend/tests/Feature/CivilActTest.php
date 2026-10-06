<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\BirthAct;
use App\Models\Registry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CivilActTest extends TestCase
{
    use RefreshDatabase;

    protected $registry;

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
            'year' => 2024, 
            'reference_prefix' => 'TEST',
            'status' => 'open'
        ]);
    }

    public function test_can_create_birth_act_with_metadata(): void
    {
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
        $user = User::factory()->create();
        $user->assignRole(\App\Enums\UserRole::ADMIN->value);
        $this->actingAs($user);

        $response = $this->post(route('acts.naissance.store'), [
            'reference_number' => '2024/NAI/001',
            'registry_id' => $this->registry->id,
            'first_name' => 'John',
            'last_name' => 'Doe',
            'date_of_birth' => '2024-01-01',
            'time_of_birth' => '10:00',
            'place_of_birth' => 'Dakar',
            'health_facility' => 'Centre de Santé',
            'act_registration_date' => '2024-01-02',
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
            'officer_comments' => 'Enregistrement initial',
            'doc_cni_pere' => \Illuminate\Http\UploadedFile::fake()->create('cni_pere.pdf', 100, 'application/pdf'),
            'doc_cni_mere' => \Illuminate\Http\UploadedFile::fake()->create('cni_mere.pdf', 100, 'application/pdf'),
            'doc_acte_naissance' => \Illuminate\Http\UploadedFile::fake()->create('acte_naissance.pdf', 100, 'application/pdf'),
            'doc_cni_declarant' => \Illuminate\Http\UploadedFile::fake()->create('cni_declarant.pdf', 100, 'application/pdf'),
        ]);

        $response->assertStatus(302);
        $this->assertDatabaseHas('birth_acts', [
            'first_name' => 'John',
        ]);
        
        $act = BirthAct::where('first_name', 'John')->first();
        $this->assertEquals('Ingénieur', $act->parents_metadata['father_profession']);
        $this->assertEquals('Enregistrement initial', $act->officer_comments);
    }

    public function test_can_update_birth_act(): void
    {
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
        $user = User::factory()->create();
        $user->assignRole(\App\Enums\UserRole::ADMIN->value);
        $this->actingAs($user);

        $act = BirthAct::create([
            'registry_id' => $this->registry->id,
            'reference_number' => '2024/NAI/002',
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'date_of_birth' => '2024-02-01',
            'place_of_birth' => 'Rufisque',
            'gender' => 'F',
            'status' => 'brouillon'
        ]);

        $response = $this->patch(route('acts.naissance.update', $act->id), [
            'reference_number' => '2024/NAI/002',
            'registry_id' => $this->registry->id,
            'first_name' => 'Janet',
            'last_name' => 'Doe',
            'date_of_birth' => '2024-02-01',
            'time_of_birth' => '12:00',
            'place_of_birth' => 'Rufisque',
            'health_facility' => 'Poste de Santé',
            'act_registration_date' => '2024-02-02',
            'gender' => 'F',
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
            'officer_comments' => 'Nom corrigé'
        ]);

        $response->assertStatus(302);
        $this->assertDatabaseHas('birth_acts', [
            'id' => $act->id,
            'first_name' => 'Janet',
            'officer_comments' => 'Nom corrigé'
        ]);
    }

    public function test_can_update_marriage_act(): void
    {
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
        $user = User::factory()->create();
        $user->assignRole(\App\Enums\UserRole::ADMIN->value);
        $this->actingAs($user);

        $marriageRegistry = Registry::create([
            'civil_registration_center_id' => $this->registry->civil_registration_center_id,
            'name' => 'Registre Mariage', 
            'type' => 'mariage',
            'year' => 2024, 
            'reference_prefix' => 'TESTMAR',
            'status' => 'open'
        ]);

        $act = \App\Models\MarriageAct::create([
            'registry_id' => $marriageRegistry->id,
            'reference_number' => '2024/MAR/001',
            'husband_first_name' => 'Romeo',
            'husband_last_name' => 'Montague',
            'wife_first_name' => 'Juliet',
            'wife_last_name' => 'Capulet',
            'marriage_date' => '2024-06-01',
            'marriage_place' => 'Verona',
            'status' => 'brouillon'
        ]);

        $response = $this->patch(route('acts.mariage.update', $act->id), [
            'reference_number' => '2024/MAR/001',
            'registry_id' => $marriageRegistry->id,
            'husband_first_name' => 'Romeo Update',
            'husband_last_name' => 'Montague',
            'wife_first_name' => 'Juliet',
            'wife_last_name' => 'Capulet',
            'marriage_date' => '2024-06-01',
            'marriage_place' => 'Verona',
            'spouses_metadata' => [
                'husband_date_of_birth' => '1995-05-05',
                'husband_place_of_birth' => 'Dakar',
                'husband_profession' => 'Poète',
                'husband_domicile' => 'Dakar',
                'husband_residence' => 'Dakar',
                'wife_date_of_birth' => '1997-07-07',
                'wife_place_of_birth' => 'Dakar',
                'wife_profession' => 'Médecin',
                'wife_domicile' => 'Dakar',
                'wife_residence' => 'Dakar',
                
                'husband_father_first_name' => 'Father',
                'husband_father_last_name' => 'Montague',
                'husband_father_date_of_birth' => '1960-01-01',
                'husband_father_profession' => 'Commerçant',
                'husband_father_domicile' => 'Dakar',
                'husband_mother_first_name' => 'Mother',
                'husband_mother_last_name' => 'Montague',
                'husband_mother_date_of_birth' => '1965-01-01',
                'husband_mother_profession' => 'Ménagère',
                'husband_mother_domicile' => 'Dakar',
                
                'wife_father_first_name' => 'Father',
                'wife_father_last_name' => 'Capulet',
                'wife_father_date_of_birth' => '1962-01-01',
                'wife_father_profession' => 'Commerçant',
                'wife_father_domicile' => 'Dakar',
                'wife_mother_first_name' => 'Mother',
                'wife_mother_last_name' => 'Capulet',
                'wife_mother_date_of_birth' => '1967-01-01',
                'wife_mother_profession' => 'Ménagère',
                'wife_mother_domicile' => 'Dakar',
            ]
        ]);

        $response->assertStatus(302);
        $this->assertDatabaseHas('marriage_acts', [
            'id' => $act->id,
            'husband_first_name' => 'Romeo Update',
        ]);
        
        $act = \App\Models\MarriageAct::find($act->id);
        $this->assertEquals('Poète', $act->spouses_metadata['husband_profession']);
    }

    public function test_can_update_death_act(): void
    {
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
        $user = User::factory()->create();
        $user->assignRole(\App\Enums\UserRole::ADMIN->value);
        $this->actingAs($user);

        $deathRegistry = Registry::create([
            'civil_registration_center_id' => $this->registry->civil_registration_center_id,
            'name' => 'Registre Deces', 
            'type' => 'deces',
            'year' => 2024, 
            'reference_prefix' => 'TESTDEC',
            'status' => 'open'
        ]);

        $act = \App\Models\DeathAct::create([
            'registry_id' => $deathRegistry->id,
            'reference_number' => '2024/DEC/001',
            'deceased_first_name' => 'John',
            'deceased_last_name' => 'Doe',
            'gender' => 'M',
            'date_of_birth' => '1980-01-01',
            'date_of_death' => '2024-05-01',
            'place_of_death' => 'Dakar',
            'status' => 'brouillon'
        ]);

        $response = $this->patch(route('acts.deces.update', $act->id), [
            'reference_number' => '2024/DEC/001',
            'registry_id' => $deathRegistry->id,
            'deceased_first_name' => 'John Update',
            'deceased_last_name' => 'Doe',
            'gender' => 'M',
            'date_of_birth' => '1980-01-01',
            'date_of_death' => '2024-05-01',
            'time_of_death' => '14:30',
            'place_of_death' => 'Dakar',
            'health_facility' => 'Poste de Santé',
            'act_registration_date' => '2024-05-02',
            'death_metadata' => [
                'place_of_birth' => 'Dakar',
                'profession' => 'Commerçant',
                'domicile' => 'Dakar',
                'marital_status' => 'Célibataire',
                
                'father_first_name' => 'Jean',
                'father_last_name' => 'Doe',
                'father_date_of_birth' => '1950-01-01',
                'father_profession' => 'Retraité',
                'father_domicile' => 'Dakar',
                
                'mother_first_name' => 'Marie',
                'mother_last_name' => 'Doe',
                'mother_date_of_birth' => '1955-01-01',
                'mother_profession' => 'Ménagère',
                'mother_domicile' => 'Dakar',
                
                'declarant_first_name' => 'Jane',
                'declarant_last_name' => 'Doe',
                'declarant_profession' => 'Enseignante',
                'declarant_address' => 'Dakar',
                'declarant_relationship' => 'Sœur',
                'declarant_id_number' => '123456789',
                'declarant_date_time' => '2024-05-02 10:00:00'
            ]
        ]);

        $response->assertStatus(302);
        $this->assertDatabaseHas('death_acts', [
            'id' => $act->id,
            'deceased_first_name' => 'John Update',
        ]);

        $act = \App\Models\DeathAct::find($act->id);
        $this->assertEquals('Jane', $act->death_metadata['declarant_first_name']);
        $this->assertEquals('2024-05-02 10:00:00', $act->death_metadata['declarant_date_time']);
    }

    public function test_act_extract_download_requires_signed_status(): void
    {
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
        $user = User::factory()->create();
        $user->assignRole(\App\Enums\UserRole::ADMIN->value);
        $this->actingAs($user);

        // 1. Create a BirthAct with status 'valide'
        $actValide = BirthAct::forceCreate([
            'registry_id' => $this->registry->id,
            'reference_number' => '2024/NAI/999',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'date_of_birth' => '2024-01-01',
            'place_of_birth' => 'Dakar',
            'gender' => 'M',
            'status' => 'valide'
        ]);
        $actValide->refresh();

        // 2. Create a BirthAct with status 'signe'
        $actSigne = BirthAct::forceCreate([
            'registry_id' => $this->registry->id,
            'reference_number' => '2024/NAI/888',
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'date_of_birth' => '2024-01-01',
            'place_of_birth' => 'Dakar',
            'gender' => 'F',
            'status' => 'signe'
        ]);
        $actSigne->refresh();

        // 3. Try downloading 'valide' act (should fail/404)
        $responseValide = $this->get("/verify/naissance/{$actValide->uuid}/download");
        $responseValide->assertStatus(404);

        // 4. Try downloading 'signe' act (should succeed/200)
        $responseSigne = $this->get("/verify/naissance/{$actSigne->uuid}/download");
        $responseSigne->assertStatus(200);
    }

    public function test_act_extract_download_requires_authentication(): void
    {
        // 1. Create a BirthAct with status 'signe'
        $actSigne = BirthAct::forceCreate([
            'registry_id' => $this->registry->id,
            'reference_number' => '2024/NAI/777',
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'date_of_birth' => '2024-01-01',
            'place_of_birth' => 'Dakar',
            'gender' => 'F',
            'status' => 'signe'
        ]);
        $actSigne->refresh();

        // 2. Try downloading without being authenticated (should redirect/302)
        $response = $this->get("/verify/naissance/{$actSigne->uuid}/download");
        $response->assertStatus(302);
        $response->assertRedirect('/login');
    }

    public function test_reference_number_incrementation_ignores_non_sequential_references(): void
    {
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
        $user = User::factory()->create();
        $user->assignRole(\App\Enums\UserRole::OFFICIER->value);
        $this->actingAs($user);

        // Create registry
        $registry = \App\Models\Registry::create([
            'civil_registration_center_id' => 1,
            'type' => 'naissance',
            'year' => 2026,
            'number' => 1,
            'status' => 'open',
            'opening_date' => now(),
            'reference_prefix' => 'N-2026-C1',
        ]);

        // Insert first act with standard reference N-2026-C1-0001
        BirthAct::forceCreate([
            'registry_id' => $registry->id,
            'reference_number' => 'N-2026-C1-0001',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'date_of_birth' => '2026-01-01',
            'place_of_birth' => 'Dakar',
            'gender' => 'M',
            'status' => 'brouillon'
        ]);

        // Insert second act with non-sequential custom reference
        BirthAct::forceCreate([
            'registry_id' => $registry->id,
            'reference_number' => '16TGFS67',
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'date_of_birth' => '2026-01-01',
            'place_of_birth' => 'Dakar',
            'gender' => 'F',
            'status' => 'brouillon'
        ]);

        // Post request to store third act
        $data = [
            'registry_id' => $registry->id,
            'first_name' => 'Baby',
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
            'doc_cni_pere' => \Illuminate\Http\UploadedFile::fake()->create('cni_pere.pdf', 100, 'application/pdf'),
            'doc_cni_mere' => \Illuminate\Http\UploadedFile::fake()->create('cni_mere.pdf', 100, 'application/pdf'),
            'doc_acte_naissance' => \Illuminate\Http\UploadedFile::fake()->create('acte_naissance.pdf', 100, 'application/pdf'),
            'doc_cni_declarant' => \Illuminate\Http\UploadedFile::fake()->create('cni_declarant.pdf', 100, 'application/pdf'),
        ];

        $response = $this->post('/acts/naissance', $data);
        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        // Verify that the generated reference number is N-2026-C1-0002 (since N-2026-C1-0001 was max and 16TGFS67 was ignored)
        $newAct = BirthAct::where('first_name', 'Baby')->first();
        $this->assertNotNull($newAct);
        $this->assertEquals('N-2026-C1-0002', $newAct->reference_number);
    }

    public function test_can_create_birth_act_without_time_and_health_facility_for_old_registry(): void
    {
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
        $user = User::factory()->create();
        $user->assignRole(\App\Enums\UserRole::OFFICIER->value);
        $this->actingAs($user);

        $registry = \App\Models\Registry::create([
            'civil_registration_center_id' => 1,
            'type' => 'naissance',
            'year' => 1990,
            'number' => 1,
            'status' => 'open',
            'opening_date' => now(),
            'reference_prefix' => 'N-1990-C1',
        ]);

        $data = [
            'is_old_registry' => true,
            'registry_id' => $registry->id,
            'reference_number' => 'N-1990-C1-0005',
            'first_name' => 'OldBaby',
            'last_name' => 'Doe',
            'date_of_birth' => '1990-01-01',
            // time_of_birth and health_facility are omitted
            'place_of_birth' => 'Dakar',
            'act_registration_date' => '1990-01-02',
            'gender' => 'M',
            'father_name' => 'Father Doe',
            'mother_name' => 'Mother Doe',
            'parents_metadata' => [
                'father_profession' => 'Ingénieur',
                'father_date_of_birth' => '1960-01-01',
                'father_place_of_birth' => 'Dakar',
                'father_domicile' => 'Dakar Plateau',
                'mother_profession' => 'Médecin',
                'mother_date_of_birth' => '1965-02-02',
                'mother_place_of_birth' => 'Dakar',
                'mother_domicile' => 'Dakar Plateau',
            ],
        ];

        $response = $this->post('/acts/naissance', $data);
        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        $newAct = BirthAct::where('first_name', 'Oldbaby')->first();
        $this->assertNotNull($newAct);
        $this->assertNull($newAct->time_of_birth);
        $this->assertNull($newAct->health_facility);
    }

    public function test_index_filtering_rules_for_signed_and_unsigned_acts(): void
    {
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
        $user = User::factory()->create();
        $user->assignRole(\App\Enums\UserRole::ADMIN->value);
        $this->actingAs($user);

        // Create a signed act
        $signedAct = BirthAct::forceCreate([
            'registry_id' => $this->registry->id,
            'reference_number' => '2024/NAI/111',
            'first_name' => 'Signedchild',
            'last_name' => 'Doe',
            'date_of_birth' => '2024-01-01',
            'place_of_birth' => 'Dakar',
            'gender' => 'M',
            'status' => 'signe'
        ]);

        // Create a draft act
        $draftAct = BirthAct::forceCreate([
            'registry_id' => $this->registry->id,
            'reference_number' => '2024/NAI/222',
            'first_name' => 'Draftchild',
            'last_name' => 'Doe',
            'date_of_birth' => '2024-01-01',
            'place_of_birth' => 'Dakar',
            'gender' => 'M',
            'status' => 'brouillon'
        ]);

        // Fetch general index (should exclude Signedchild, include Draftchild)
        $responseGeneral = $this->get('/acts/naissance/list');
        $responseGeneral->assertStatus(200);
        $generalActs = $responseGeneral->original->getData()['page']['props']['acts']['data'];
        $generalNames = collect($generalActs)->pluck('first_name')->toArray();
        $this->assertContains('Draftchild', $generalNames);
        $this->assertNotContains('Signedchild', $generalNames);

        // Fetch registry-filtered index (should include Signedchild, exclude Draftchild)
        $responseFiltered = $this->get('/acts/naissance/list?registry_id=' . $this->registry->id);
        $responseFiltered->assertStatus(200);
        $filteredActs = $responseFiltered->original->getData()['page']['props']['acts']['data'];
        $filteredNames = collect($filteredActs)->pluck('first_name')->toArray();
        $this->assertNotContains('Draftchild', $filteredNames);
        $this->assertContains('Signedchild', $filteredNames);
    }

    public function test_index_search_filter(): void
    {
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
        $user = User::factory()->create();
        $user->assignRole(\App\Enums\UserRole::ADMIN->value);
        $this->actingAs($user);

        BirthAct::forceCreate([
            'registry_id' => $this->registry->id,
            'reference_number' => '2024/NAI/999',
            'first_name' => 'UniqueSearchFirst',
            'last_name' => 'UniqueSearchLast',
            'date_of_birth' => '2024-01-01',
            'place_of_birth' => 'Dakar',
            'gender' => 'M',
            'status' => 'brouillon'
        ]);

        // Case-insensitive search test (searching for lowercase 'uniquesearchfirst')
        $response = $this->get('/acts/naissance/list?search=uniquesearchfirst');
        $response->assertStatus(200);
        $acts = $response->original->getData()['page']['props']['acts']['data'];
        $this->assertCount(1, $acts);
        $this->assertEquals('UniqueSearchFirst', $acts[0]['first_name']);
    }

    public function test_can_create_birth_act_with_approximate_birth_date_vers(): void
    {
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
        $user = User::factory()->create();
        $user->assignRole(\App\Enums\UserRole::ADMIN->value);
        $this->actingAs($user);

        $response = $this->post(route('acts.naissance.store'), [
            'reference_number' => '2024/NAI/VERS1',
            'registry_id' => $this->registry->id,
            'first_name' => 'Ablaye',
            'last_name' => 'Diallo',
            'birth_date_type' => 'vers',
            'birth_year' => 1954,
            'place_of_birth' => 'Enampore',
            'act_registration_date' => '2024-01-02',
            'gender' => 'M',
            'father_name' => 'Mamadou Diallo',
            'mother_name' => 'Aissatou Diallo',
            'parents_metadata' => [
                'father_birth_type' => 'vers',
                'father_birth_year' => 1920,
                'father_profession' => 'Agriculteur',
                'father_place_of_birth' => 'Enampore',
                'father_domicile' => 'Enampore',
                'mother_birth_type' => 'age',
                'mother_age' => 40,
                'mother_profession' => 'Ménagère',
                'mother_place_of_birth' => 'Enampore',
                'mother_domicile' => 'Enampore',
            ],
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        $act = BirthAct::where('first_name', 'Ablaye')->first();
        $this->assertNotNull($act);
        $this->assertEquals('vers', $act->birth_date_type);
        $this->assertEquals(1954, $act->birth_year);
        $this->assertEquals('1954-01-01', $act->date_of_birth->format('Y-m-d'));

        // Check parents metadata
        $this->assertEquals('vers', $act->parents_metadata['father_birth_type']);
        $this->assertEquals(1920, $act->parents_metadata['father_birth_year']);
        $this->assertEquals('1920-01-01', $act->parents_metadata['father_date_of_birth']);
        $this->assertEquals('age', $act->parents_metadata['mother_birth_type']);
        $this->assertEquals(40, $act->parents_metadata['mother_age']);

        // Check PDF rendering with DocumentGenerationService
        $service = app(\App\Services\DocumentGenerationService::class);
        $pdfContent = $service->generateActExtractPdf($act, 'naissance');
        $this->assertNotEmpty($pdfContent);

        // Check Blade rendering directly contains the expected phrase
        $html = view('pdf.act', [
            'act' => $act,
            'type' => 'naissance',
            'title' => 'Extrait',
            'center' => null,
            'qrCode' => '',
            'logo' => '',
            'timestamp' => now()->format('d/m/Y H:i:s'),
            'volet' => null,
        ])->render();

        $this->assertStringContainsString("vers l'an", $html);
        $this->assertStringContainsString("1954", $html);
        $this->assertStringNotContainsString("premier du mois de", $html);
    }

    public function test_can_create_birth_act_with_presumed_age(): void
    {
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
        $user = User::factory()->create();
        $user->assignRole(\App\Enums\UserRole::ADMIN->value);
        $this->actingAs($user);

        $response = $this->post(route('acts.naissance.store'), [
            'reference_number' => '2024/NAI/AGE1',
            'registry_id' => $this->registry->id,
            'first_name' => 'Fatou',
            'last_name' => 'Sow',
            'birth_date_type' => 'age',
            'presumed_age' => 50,
            'place_of_birth' => 'Ziguinchor',
            'act_registration_date' => '2024-06-15',
            'gender' => 'F',
            'father_name' => 'Amadou Sow',
            'mother_name' => 'Mariama Sow',
            'parents_metadata' => [
                'father_profession' => 'Commerçant',
                'father_date_of_birth' => '1950-01-01',
                'father_place_of_birth' => 'Ziguinchor',
                'father_domicile' => 'Ziguinchor',
                'mother_profession' => 'Commerçante',
                'mother_date_of_birth' => '1955-01-01',
                'mother_place_of_birth' => 'Ziguinchor',
                'mother_domicile' => 'Ziguinchor',
            ],
        ]);

        $response->assertRedirect();

        $act = BirthAct::where('first_name', 'Fatou')->first();
        $this->assertNotNull($act);
        $this->assertEquals('age', $act->birth_date_type);
        $this->assertEquals(50, $act->presumed_age);
        $this->assertEquals(1974, $act->birth_year);
        $this->assertEquals('1974-01-01', $act->date_of_birth->format('Y-m-d'));
    }

    public function test_old_registry_excludes_taken_reference_numbers_from_available_list(): void
    {
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
        $user = User::factory()->create();
        $user->assignRole(\App\Enums\UserRole::OFFICIER->value);
        $this->actingAs($user);

        // Registry 2016 Vol 1 (N-2016-C1)
        $registry = Registry::create([
            'civil_registration_center_id' => 1,
            'name' => 'Registre 2016',
            'type' => 'naissance',
            'year' => 2016,
            'number' => 1,
            'reference_prefix' => 'N-2016-C1',
            'status' => 'open'
        ]);

        // Insert Act #1 with reference N-2016-C1-0001
        BirthAct::forceCreate([
            'registry_id' => $registry->id,
            'reference_number' => 'N-2016-C1-0001',
            'first_name' => 'Premier',
            'last_name' => 'Acte',
            'date_of_birth' => '2016-03-01',
            'place_of_birth' => 'Enampore',
            'gender' => 'M',
            'status' => 'brouillon'
        ]);

        // Insert Act #5 with reference N-2016-C1-0005
        BirthAct::forceCreate([
            'registry_id' => $registry->id,
            'reference_number' => 'N-2016-C1-0005',
            'first_name' => 'Cinquieme',
            'last_name' => 'Acte',
            'date_of_birth' => '2016-04-01',
            'place_of_birth' => 'Enampore',
            'gender' => 'F',
            'status' => 'brouillon'
        ]);

        // 1. Visit create page and verify Inertia props
        $createResponse = $this->get(route('acts.naissance.create', ['old_registry' => 1]));
        $createResponse->assertStatus(200);

        $registriesProp = $createResponse->inertiaProps('registries');
        $this->assertNotEmpty($registriesProp);
        
        $regItem = collect($registriesProp)->firstWhere('id', $registry->id);
        $this->assertNotNull($regItem);
        $this->assertContains('N-2016-C1-0001', $regItem['existing_reference_numbers']);
        $this->assertContains('N-2016-C1-0005', $regItem['existing_reference_numbers']);
        $this->assertContains(1, $regItem['used_act_numbers']);
        $this->assertContains(5, $regItem['used_act_numbers']);
        $this->assertNotContains(2, $regItem['used_act_numbers']);

        // 2. Attempting to create an act with N-2016-C1-0001 in the same registry must fail validation
        $dupResponse = $this->post(route('acts.naissance.store'), [
            'is_old_registry' => true,
            'registry_id' => $registry->id,
            'reference_number' => 'N-2016-C1-0001',
            'first_name' => 'Doublon',
            'last_name' => 'Test',
            'date_of_birth' => '2016-01-01',
            'place_of_birth' => 'Enampore',
            'act_registration_date' => '2016-01-02',
            'gender' => 'M',
            'father_name' => 'Pere Test',
            'mother_name' => 'Mere Test',
            'parents_metadata' => [
                'father_profession' => 'Agriculteur',
                'father_date_of_birth' => '1980-01-01',
                'father_place_of_birth' => 'Enampore',
                'father_domicile' => 'Enampore',
                'mother_profession' => 'Ménagère',
                'mother_date_of_birth' => '1985-01-01',
                'mother_place_of_birth' => 'Enampore',
                'mother_domicile' => 'Enampore',
            ],
        ]);

        $dupResponse->assertSessionHasErrors(['reference_number']);

        // 3. Creating an act with an available reference number (N-2016-C1-0002) must succeed
        $successResponse = $this->post(route('acts.naissance.store'), [
            'is_old_registry' => true,
            'registry_id' => $registry->id,
            'reference_number' => 'N-2016-C1-0002',
            'first_name' => 'Deuxieme',
            'last_name' => 'Valide',
            'date_of_birth' => '2016-01-01',
            'place_of_birth' => 'Enampore',
            'act_registration_date' => '2016-01-02',
            'gender' => 'M',
            'father_name' => 'Pere Test',
            'mother_name' => 'Mere Test',
            'parents_metadata' => [
                'father_profession' => 'Agriculteur',
                'father_date_of_birth' => '1980-01-01',
                'father_place_of_birth' => 'Enampore',
                'father_domicile' => 'Enampore',
                'mother_profession' => 'Ménagère',
                'mother_date_of_birth' => '1985-01-01',
                'mother_place_of_birth' => 'Enampore',
                'mother_domicile' => 'Enampore',
            ],
        ]);

        $successResponse->assertSessionHasNoErrors();
        $this->assertDatabaseHas('birth_acts', [
            'reference_number' => 'N-2016-C1-0002',
            'first_name' => 'Deuxieme'
        ]);
    }

    public function test_validation_messages_for_non_file_max_rules(): void
    {
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
        $user = User::factory()->create();
        $user->assignRole(\App\Enums\UserRole::ADMIN->value);
        $this->actingAs($user);

        // Test birth_year exceeding max (future year)
        $response = $this->post(route('acts.naissance.store'), [
            'birth_date_type' => 'vers',
            'birth_year' => (int) date('Y') + 10, // Exceeds max
            'is_old_registry' => true,
            'reference_number' => 'TEST-001',
            'first_name' => 'Test',
            'last_name' => 'Child',
            'place_of_birth' => 'Dakar',
            'act_registration_date' => '2024-01-01',
            'gender' => 'M',
            'parents_metadata' => [],
        ]);

        $response->assertSessionHasErrors(['birth_year']);
        $errors = session('errors')->get('birth_year');
        $this->assertStringNotContainsString('500 Ko', $errors[0]);
        $this->assertStringContainsString('année de naissance', mb_strtolower($errors[0]));

        // Test presumed_age exceeding 150
        $responseAge = $this->post(route('acts.naissance.store'), [
            'birth_date_type' => 'age',
            'presumed_age' => 200,
            'is_old_registry' => true,
            'reference_number' => 'TEST-002',
            'first_name' => 'Test',
            'last_name' => 'Child',
            'place_of_birth' => 'Dakar',
            'act_registration_date' => '2024-01-01',
            'gender' => 'M',
            'parents_metadata' => [],
        ]);

        $responseAge->assertSessionHasErrors(['presumed_age']);
        $ageErrors = session('errors')->get('presumed_age');
        $this->assertStringNotContainsString('500 Ko', $ageErrors[0]);
        $this->assertStringContainsString('âge présumé', mb_strtolower($ageErrors[0]));
    }
}


