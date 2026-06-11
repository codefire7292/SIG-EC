<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;

class ActCreateTest extends TestCase
{
    public function test_create_route()
    {
        $user = User::first();
        if (!$user) {
            $user = User::factory()->create();
        }

        $response = $this->actingAs($user)->get('/acts/naissance/create');
        
        $response->dump();
        $response->assertStatus(200);
    }
}
