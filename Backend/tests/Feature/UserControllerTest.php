<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create roles and permission
        Role::create(['name' => 'Super Admin']);
        Role::create(['name' => 'Agent']);
        Permission::create(['name' => 'manage-users']);
    }

    public function test_user_cannot_update_own_role()
    {
        $user = User::factory()->create();
        $user->assignRole('Super Admin');
        $user->givePermissionTo('manage-users');

        $response = $this->actingAs($user)->put(route('admin.users.update', $user->id), [
            'name' => $user->name,
            'email' => $user->email,
            'role' => 'Agent', // trying to change own role to Agent
        ]);

        $response->assertSessionHasErrors(['role']);
        $this->assertTrue($user->fresh()->hasRole('Super Admin'));
        $this->assertFalse($user->fresh()->hasRole('Agent'));
    }

    public function test_user_can_update_own_name_and_email()
    {
        $user = User::factory()->create();
        $user->assignRole('Super Admin');
        $user->givePermissionTo('manage-users');

        $response = $this->actingAs($user)->put(route('admin.users.update', $user->id), [
            'name' => 'New Name',
            'email' => 'newemail@example.com',
            'role' => 'Super Admin', // same role
        ]);

        $response->assertRedirect(route('admin.users.index'));
        $this->assertEquals('New Name', $user->fresh()->name);
        $this->assertEquals('newemail@example.com', $user->fresh()->email);
    }

    public function test_user_can_update_other_user_role()
    {
        $admin = User::factory()->create();
        $admin->assignRole('Super Admin');
        $admin->givePermissionTo('manage-users');

        $otherUser = User::factory()->create();
        $otherUser->assignRole('Agent');

        $response = $this->actingAs($admin)->put(route('admin.users.update', $otherUser->id), [
            'name' => $otherUser->name,
            'email' => $otherUser->email,
            'role' => 'Super Admin', // changing other user's role to Super Admin
        ]);

        $response->assertRedirect(route('admin.users.index'));
        $this->assertTrue($otherUser->fresh()->hasRole('Super Admin'));
    }
}
