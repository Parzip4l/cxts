<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTokenTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_login_and_fetch_profile_with_api_token(): void
    {
        $user = User::factory()->create([
            'email' => 'engineer@example.com',
            'password' => bcrypt('secret123'),
        ]);

        $loginResponse = $this->postJson('/api/v1/auth/login', [
            'email' => 'engineer@example.com',
            'password' => 'secret123',
            'device_name' => 'mobile-test',
        ]);

        $loginResponse
            ->assertOk()
            ->assertJsonStructure([
                'token',
                'token_type',
                'expires_at',
                'user' => ['id', 'name', 'email', 'role', 'department_id'],
            ]);

        $token = $loginResponse->json('token');

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/v1/auth/me')
            ->assertOk()
            ->assertJsonPath('data.id', $user->id)
            ->assertJsonPath('data.email', $user->email);
    }
}
