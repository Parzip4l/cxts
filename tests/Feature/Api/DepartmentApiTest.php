<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DepartmentApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_create_department_via_api(): void
    {
        User::factory()->create([
            'email' => 'admin@example.com',
            'password' => bcrypt('secret123'),
            'role' => 'super_admin',
        ]);

        $token = $this->postJson('/api/v1/auth/login', [
            'email' => 'admin@example.com',
            'password' => 'secret123',
        ])->json('token');

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/departments', [
                'code' => 'DEP-QA',
                'name' => 'Quality Assurance',
                'is_active' => true,
            ])
            ->assertCreated()
            ->assertJsonPath('data.code', 'DEP-QA');

        $this->assertDatabaseHas('departments', [
            'code' => 'DEP-QA',
            'name' => 'Quality Assurance',
        ]);
    }
}
