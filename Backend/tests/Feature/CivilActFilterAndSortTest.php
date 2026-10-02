<?php

namespace Tests\Feature;

use App\Models\BirthAct;
use App\Models\CivilRegistrationCenter;
use App\Models\Registry;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CivilActFilterAndSortTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected CivilRegistrationCenter $center;
    protected Registry $reg2025Vol1;
    protected Registry $reg2026Vol1;
    protected Registry $reg2026Vol2;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);

        $this->user = User::factory()->create();
        $this->user->assignRole(\App\Enums\UserRole::ADMIN->value);
        $this->actingAs($this->user);

        $this->center = CivilRegistrationCenter::create([
            'name' => 'Centre Test',
            'code' => 'CT1',
            'commune' => 'Enampore',
            'region' => 'Ziguinchor',
            'is_active' => true,
        ]);

        $this->reg2025Vol1 = Registry::create([
            'civil_registration_center_id' => $this->center->id,
            'type' => 'naissance',
            'year' => 2025,
            'number' => 1,
            'reference_prefix' => 'N-2025-CT1-V01',
            'status' => 'open',
            'opening_date' => '2025-01-01',
        ]);

        $this->reg2026Vol1 = Registry::create([
            'civil_registration_center_id' => $this->center->id,
            'type' => 'naissance',
            'year' => 2026,
            'number' => 1,
            'reference_prefix' => 'N-2026-CT1-V01',
            'status' => 'open',
            'opening_date' => '2026-01-01',
        ]);

        $this->reg2026Vol2 = Registry::create([
            'civil_registration_center_id' => $this->center->id,
            'type' => 'naissance',
            'year' => 2026,
            'number' => 2,
            'reference_prefix' => 'N-2026-CT1-V02',
            'status' => 'open',
            'opening_date' => '2026-02-01',
        ]);

        // Acts in 2026 Vol 1
        BirthAct::forceCreate([
            'registry_id' => $this->reg2026Vol1->id,
            'reference_number' => 'N-2026-CT1-V01-0001',
            'first_name' => 'Alpha',
            'last_name' => 'Diallo',
            'date_of_birth' => '2026-01-05',
            'place_of_birth' => 'Dakar',
            'gender' => 'M',
            'status' => 'signe',
            'is_current' => true,
        ]);

        BirthAct::forceCreate([
            'registry_id' => $this->reg2026Vol1->id,
            'reference_number' => 'N-2026-CT1-V01-0002',
            'first_name' => 'Beta',
            'last_name' => 'Sow',
            'date_of_birth' => '2026-01-10',
            'place_of_birth' => 'Dakar',
            'gender' => 'F',
            'status' => 'signe',
            'is_current' => true,
        ]);

        // Act in 2025 Vol 1
        BirthAct::forceCreate([
            'registry_id' => $this->reg2025Vol1->id,
            'reference_number' => 'N-2025-CT1-V01-0001',
            'first_name' => 'Zack',
            'last_name' => 'Ba',
            'date_of_birth' => '2025-05-15',
            'place_of_birth' => 'Dakar',
            'gender' => 'M',
            'status' => 'signe',
            'is_current' => true,
        ]);
    }

    public function test_registries_list_page_returns_all_volumes_with_acts_count(): void
    {
        $response = $this->get('/acts/naissance/registres');

        $response->assertStatus(200);
        $page = $response->original->getData()['page'];
        $this->assertEquals('CivilActs/Registries', $page['component']);
        $this->assertCount(3, $page['props']['registries']);
        $this->assertEquals('naissance', $page['props']['type']);
    }

    public function test_can_filter_acts_inside_a_registry_volume_sorted_by_act_number(): void
    {
        $response = $this->get("/acts/naissance/list?registry_id={$this->reg2026Vol1->id}&sort_by=number&sort_order=asc");

        $response->assertStatus(200);
        $page = $response->original->getData()['page'];
        $this->assertEquals('CivilActs/Index', $page['component']);
        $acts = $page['props']['acts']['data'];
        $this->assertCount(2, $acts);
        $this->assertEquals('N-2026-CT1-V01-0001', $acts[0]['reference_number']);
        $this->assertEquals('N-2026-CT1-V01-0002', $acts[1]['reference_number']);
        $this->assertEquals('number', $page['props']['filters']['sort_by']);
        $this->assertEquals('asc', $page['props']['filters']['sort_order']);
        $this->assertEquals($this->reg2026Vol1->id, $page['props']['activeRegistry']['id']);
        $this->assertCount(2, $page['props']['siblingRegistries']);
    }

    public function test_can_sort_acts_by_number_descending(): void
    {
        $response = $this->get("/acts/naissance/list?registry_id={$this->reg2026Vol1->id}&sort_by=number&sort_order=desc");

        $response->assertStatus(200);
        $page = $response->original->getData()['page'];
        $acts = $page['props']['acts']['data'];
        $this->assertCount(2, $acts);
        $this->assertEquals('N-2026-CT1-V01-0002', $acts[0]['reference_number']);
        $this->assertEquals('N-2026-CT1-V01-0001', $acts[1]['reference_number']);
        $this->assertEquals('desc', $page['props']['filters']['sort_order']);
    }

    public function test_can_filter_acts_by_exact_act_number(): void
    {
        $response = $this->get("/acts/naissance/list?registry_id={$this->reg2026Vol1->id}&act_number=2");

        $response->assertStatus(200);
        $page = $response->original->getData()['page'];
        $acts = $page['props']['acts']['data'];
        $this->assertCount(1, $acts);
        $this->assertEquals('N-2026-CT1-V01-0002', $acts[0]['reference_number']);
        $this->assertEquals('2', $page['props']['filters']['act_number']);
    }

    public function test_admin_registries_can_filter_year_by_year_and_number_by_number(): void
    {
        // Filter year 2026
        $responseYear = $this->get('/admin/registries?year=2026');
        $responseYear->assertStatus(200);
        $pageYear = $responseYear->original->getData()['page'];
        $this->assertEquals('Admin/Registries/Index', $pageYear['component']);
        $this->assertCount(2, $pageYear['props']['registries']['data']);
        $this->assertEquals('2026', $pageYear['props']['filters']['year']);

        // Filter volume number 2
        $responseNum = $this->get('/admin/registries?number=2');
        $responseNum->assertStatus(200);
        $pageNum = $responseNum->original->getData()['page'];
        $this->assertEquals('Admin/Registries/Index', $pageNum['component']);
        $this->assertCount(1, $pageNum['props']['registries']['data']);
        $this->assertEquals(2, $pageNum['props']['registries']['data'][0]['number']);
        $this->assertEquals('2', $pageNum['props']['filters']['number']);
    }
}
