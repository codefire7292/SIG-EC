<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\CivilCertificate;
use App\Enums\CivilCertificateType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ResidenceCertificateTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        \Spatie\Permission\Models\Permission::create(['name' => 'create-drafts', 'guard_name' => 'web']);
        $this->user = User::factory()->create();
        $this->user->givePermissionTo('create-drafts');

        $center = new \App\Models\CivilRegistrationCenter([
            'name' => 'Centre Test',
            'code' => 'TEST01',
            'commune' => 'Dakar',
            'region' => 'Dakar'
        ]);
        $center->id = 1;
        $center->save();
    }

    public function test_can_create_residence_certificate_with_cni()
    {
        $response = $this->actingAs($this->user)->post(route('civil-certificates.store'), [
            'type' => 'residence',
            'center' => 'Dakar',
            'applicant_first_name' => 'John',
            'applicant_last_name' => 'Doe',
            'applicant_cni' => '123456789',
            'data' => [
                'adresse' => 'Parcelles Assainies',
                'temoin_1_identite' => 'Temoin One',
                'temoin_2_identite' => 'Temoin Two',
            ]
        ]);

        $response->assertRedirect(route('civil-certificates.index'));
        $this->assertDatabaseHas('civil_certificates', [
            'applicant_first_name' => 'John',
            'applicant_last_name' => 'Doe',
            'applicant_cni' => '123456789',
        ]);
    }

    public function test_cannot_create_residence_certificate_without_cni()
    {
        $response = $this->actingAs($this->user)->post(route('civil-certificates.store'), [
            'type' => 'residence',
            'center' => 'Dakar',
            'applicant_first_name' => 'John',
            'applicant_last_name' => 'Doe',
            'applicant_cni' => '', // Empty CNI
            'data' => [
                'adresse' => 'Parcelles Assainies',
                'temoin_1_identite' => 'Temoin One',
                'temoin_2_identite' => 'Temoin Two',
            ]
        ]);

        $response->assertSessionHasErrors(['applicant_cni']);
    }

    public function test_cannot_create_residence_certificate_without_adresse()
    {
        $response = $this->actingAs($this->user)->post(route('civil-certificates.store'), [
            'type' => 'residence',
            'center' => 'Dakar',
            'applicant_first_name' => 'John',
            'applicant_last_name' => 'Doe',
            'applicant_cni' => '123456789',
            'data' => [
                'temoin_1_identite' => 'Temoin One',
                'temoin_2_identite' => 'Temoin Two',
            ]
        ]);

        $response->assertSessionHasErrors(['data.adresse']);
    }
}
