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
        $center = \App\Models\CivilRegistrationCenter::create([
            'name' => 'Centre Test',
            'code' => 'TEST01',
            'commune' => 'Dakar',
            'region' => 'Dakar'
        ]);
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
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->post(route('acts.naissance.store'), [
            'reference_number' => '2024/NAI/001',
            'registry_id' => $this->registry->id,
            'first_name' => 'John',
            'last_name' => 'Doe',
            'date_of_birth' => '2024-01-01',
            'place_of_birth' => 'Dakar',
            'gender' => 'M',
            'father_name' => 'Father Doe',
            'mother_name' => 'Mother Doe',
            'parents_metadata' => [
                'father_profession' => 'Ingénieur',
                'mother_profession' => 'Médecin',
                'residence' => 'Dakar Plateau'
            ],
            'officer_comments' => 'Enregistrement initial'
        ]);

        $response->assertStatus(302);
        $this->assertDatabaseHas('birth_acts', [
            'reference_number' => '2024/NAI/001',
            'first_name' => 'John',
        ]);
        
        $act = BirthAct::where('reference_number', '2024/NAI/001')->first();
        $this->assertEquals('Ingénieur', $act->parents_metadata['father_profession']);
        $this->assertEquals('Enregistrement initial', $act->officer_comments);
    }

    public function test_can_update_birth_act(): void
    {
        $user = User::factory()->create();
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
            'place_of_birth' => 'Rufisque',
            'gender' => 'F',
            'officer_comments' => 'Nom corrigé'
        ]);

        $response->assertStatus(302);
        $this->assertDatabaseHas('birth_acts', [
            'id' => $act->id,
            'first_name' => 'Janet',
            'officer_comments' => 'Nom corrigé'
        ]);
    }
}
