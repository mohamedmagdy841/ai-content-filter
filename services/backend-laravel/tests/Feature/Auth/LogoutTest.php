<?php

namespace Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LogoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_logout_revokes_token(): void
    {
        $user = User::factory()->create();

        $token = $user->createToken('Api Token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/v1/logout');

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 200,
            'message' => 'User logged out successfully.',
            'data' => [],
        ]);

        $this->assertDatabaseMissing('personal_access_tokens', [
            'tokenable_id' => $user->id,
        ]);
    }

    public function test_logout_fails_without_authentication(): void
    {
        $response = $this->postJson('/api/v1/logout');

        $response->assertStatus(401);
        $response->assertJson([
            "message" => "Unauthenticated."
        ]);
    }
}
