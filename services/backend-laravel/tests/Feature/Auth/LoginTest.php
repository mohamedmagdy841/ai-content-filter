<?php

namespace Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_returns_token_with_valid_credentials(): void
    {
        $user = User::factory()->create();

        $response = $this->postJson('/api/v1/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertStatus(200);

        $response->assertJsonStructure([
            'status',
            'message',
            'data' => ['name', 'email', 'token'],
        ]);

        $response->assertJsonPath('data.token', fn($token) => !empty($token));
    }

    public function test_login_fails_with_invalid_password(): void
    {
        $user = User::factory()->create();

        $response = $this->postJson('/api/v1/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(401);
    }

    public function test_login_fails_with_nonexistent_email(): void
    {
        $user = User::factory()->create();

        $response = $this->postJson('/api/v1/login', [
            'email' => 'notfound@example.com',
            'password' => 'password',
        ]);

        $response->assertStatus(401);
    }

    public function test_login_fails_with_missing_fields(): void
    {
        $user = User::factory()->create();

        $response = $this->postJson('/api/v1/login', [
            'email' => $user->email,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('password');
    }

    public function test_login_fails_with_invalid_email_format(): void
    {
        $response = $this->postJson('/api/v1/login', [
            'email' => 'not-an-email',
            'password' => 'password',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['email']);
    }
}
