<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class RegisterTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_creates_user(): void
    {
        $response = $this->postJson('/api/v1/register', [
            "name" => "Mohamed Magdy",
            "email" => "user@gmail.com",
            "password" => "123456789",
            "password_confirmation" => "123456789"
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('users', [
            'name' => 'Mohamed Magdy',
            'email' => 'user@gmail.com',
        ]);
    }

    public function test_register_returns_validation_error(): void
    {
        $response = $this->postJson('/api/v1/register', [
            "email" => "user@gmail.com",
            "password" => "123456789",
            "password_confirmation" => "123456789"
        ]);

        $response->assertStatus(422);
    }

    public function test_register_fails_with_password_mismatch(): void
    {
        $response = $this->postJson('/api/v1/register', [
            "name" => "Mohamed Magdy",
            "email" => "user@gmail.com",
            "password" => "123456789",
            "password_confirmation" => "2222222"
        ]);

        $response->assertStatus(422);
    }

    public function test_register_fails_with_duplicate_email(): void
    {
        User::factory()->create([
            "email" => "user@gmail.com",
        ]);

        $response = $this->postJson('/api/v1/register', [
            "name" => "Mohamed Magdy",
            "email" => "user@gmail.com",
            "password" => "123456789",
            "password_confirmation" => "123456789"
        ]);

        $response->assertStatus(422);
    }
}
