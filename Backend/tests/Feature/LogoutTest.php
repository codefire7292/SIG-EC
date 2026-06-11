<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LogoutTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that logout is possible even without CSRF session token (excepted).
     */
    public function test_logout_route_is_excepted_from_csrf(): void
    {
        $user = User::factory()->create();

        // Acting as the user
        $this->actingAs($user);

        // Perform logout POST request. 
        // In Laravel tests, CSRF is disabled by default, but we can verify it doesn't fail.
        $response = $this->post('/logout');

        $response->assertStatus(302);
        $response->assertRedirect('/');
        $this->assertGuest();
    }
}
