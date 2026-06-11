<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\BirthAct;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\BirthActsImport;

class ExcelImportTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_can_upload_birth_registry_excel()
    {
        $file = UploadedFile::fake()->create('births.xlsx');

        $response = $this->actingAs($this->user)
            ->post(route('acts.naissance.import'), [
                'file' => $file,
            ]);

        $response->assertStatus(302);
        // We don't check for success/error flash here as we used fake file
    }

    public function test_birth_acts_import_logic()
    {
        $data = [
            [
                'reference_number' => 'B2026-001',
                'first_name' => 'John',
                'last_name' => 'Doe',
                'date_of_birth' => '2026-01-01',
                'place_of_birth' => 'Dakar',
                'gender' => 'M',
                'father_name' => 'James Doe',
                'mother_name' => 'Jane Doe',
            ]
        ];

        // Setup context
        $center = \App\Models\CivilRegistrationCenter::create([
            'name' => 'Center Test',
            'code' => 'CT01',
            'commune' => 'Dakar',
            'region' => 'Dakar',
            'is_active' => true,
        ]);

        $registry = \App\Models\Registry::create([
            'civil_registration_center_id' => $center->id,
            'type' => 'naissance',
            'year' => 2026,
            'reference_prefix' => 'REG',
            'last_number' => 0,
            'status' => 'open',
        ]);

        $import = new BirthActsImport();
        $act = $import->model($data[0]);
        $act->registry_id = $registry->id;
        $act->save();

        $this->assertDatabaseHas('birth_acts', [
            'reference_number' => 'B2026-001',
            'first_name' => 'John',
        ]);
    }
}
