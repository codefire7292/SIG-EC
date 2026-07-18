<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;

class ActCreateTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_route()
    {
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
        $user = User::factory()->create();
        $user->givePermissionTo('create-drafts');

        $response = $this->actingAs($user)->get('/acts/naissance/create');
        
        $response->assertStatus(200);
    }
}
