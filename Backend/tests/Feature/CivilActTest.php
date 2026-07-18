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
}
