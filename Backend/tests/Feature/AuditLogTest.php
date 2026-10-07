<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\AuditLog;
use App\Models\BirthAct;
use App\Models\CivilRegistrationCenter;
use App\Models\Registry;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditLogTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
    }

    public function test_admin_and_mayor_can_access_audit_logs()
    {
        $admin = User::factory()->create();
        $admin->assignRole(UserRole::ADMIN->value);

        $response = $this->actingAs($admin)->get(route('admin.audit-logs.index'));
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Admin/AuditLogs/Index')
            ->has('logs')
            ->has('stats')
            ->has('agents')
        );

        $maire = User::factory()->create();
        $maire->assignRole(UserRole::MAIRE->value);

        $responseMaire = $this->actingAs($maire)->get(route('admin.audit-logs.index'));
        $responseMaire->assertStatus(200);
    }

    public function test_unauthorized_user_cannot_access_audit_logs()
    {
        $agent = User::factory()->create();
        $agent->assignRole(UserRole::AGENT->value);

        $response = $this->actingAs($agent)->get(route('admin.audit-logs.index'));
        $response->assertStatus(403);

        $supervisor = User::factory()->create();
        $supervisor->assignRole(UserRole::SUPERVISEUR->value);

        $responseSup = $this->actingAs($supervisor)->get(route('admin.audit-logs.index'));
        $responseSup->assertStatus(403);
    }

    public function test_act_creation_and_status_update_records_audit_logs()
    {
        $admin = User::factory()->create();
        $admin->assignRole(UserRole::ADMIN->value);

        $center = CivilRegistrationCenter::create([
            'name' => 'Centre Enampore',
            'code' => 'ENP',
            'commune' => 'Enampore',
            'region' => 'Ziguinchor',
            'is_active' => true,
        ]);

        $registry = Registry::create([
            'civil_registration_center_id' => $center->id,
            'type' => 'naissance',
            'year' => 2026,
            'number' => 1,
            'reference_prefix' => 'N-2026-ENP-1',
            'status' => 'open',
            'opening_date' => now()->toDateString(),
        ]);

        // Acting as admin, create a birth act
        $this->actingAs($admin);

        $act = BirthAct::create([
            'registry_id' => $registry->id,
            'reference_number' => 'N-2026-ENP-1-0001',
            'first_name' => 'Moussa',
            'last_name' => 'Sow',
            'date_of_birth' => '2026-05-10',
            'place_of_birth' => 'Enampore',
            'gender' => 'M',
            'status' => 'brouillon',
        ]);

        // Assert creation log was recorded
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $admin->id,
            'auditable_type' => BirthAct::class,
            'auditable_id' => $act->id,
            'action' => 'creation',
        ]);

        // Update status to valide
        $response = $this->actingAs($admin)->post(route('acts.naissance.status', $act->id), [
            'status' => 'valide',
        ]);

        $response->assertSessionHas('success');

        // Assert validation log was recorded
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $admin->id,
            'auditable_type' => BirthAct::class,
            'auditable_id' => $act->id,
            'action' => 'validation',
        ]);
    }

    public function test_audit_logs_can_be_filtered_by_agent_and_action()
    {
        $admin = User::factory()->create(['name' => 'Agent Alpha']);
        $admin->assignRole(UserRole::ADMIN->value);

        $otherUser = User::factory()->create(['name' => 'Agent Beta']);
        $otherUser->assignRole(UserRole::AGENT->value);

        AuditLog::record('connexion', $admin);
        AuditLog::record('creation', $otherUser);

        // Filter by user_id
        $response = $this->actingAs($admin)->get(route('admin.audit-logs.index', ['user_id' => $admin->id]));
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->where('filters.user_id', (string) $admin->id)
        );

        // Filter by action
        $responseAction = $this->actingAs($admin)->get(route('admin.audit-logs.index', ['action' => 'connexion']));
        $responseAction->assertStatus(200);
        $responseAction->assertInertia(fn ($page) => $page
            ->where('filters.action', 'connexion')
        );
    }

    public function test_audit_logs_can_be_exported_to_csv()
    {
        $admin = User::factory()->create(['name' => 'Admin Test']);
        $admin->assignRole(UserRole::ADMIN->value);

        AuditLog::record('connexion', $admin);

        $response = $this->actingAs($admin)->get(route('admin.audit-logs.export'));
        $response->assertStatus(200);
        $this->assertEquals('text/csv; charset=UTF-8', $response->headers->get('content-type'));
    }

    public function test_audit_log_show_endpoint_returns_json()
    {
        $admin = User::factory()->create(['name' => 'Admin Test']);
        $admin->assignRole(UserRole::ADMIN->value);

        $log = AuditLog::record('connexion', $admin);

        $response = $this->actingAs($admin)->get(route('admin.audit-logs.show', $log->id));
        $response->assertStatus(200);
        $response->assertJsonPath('log.id', $log->id);
        $response->assertJsonPath('log.action', 'connexion');
    }
}
