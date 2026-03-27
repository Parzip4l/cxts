<?php

namespace Tests\Feature\Api;

use App\Models\Department;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserManagementApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_create_engineer_user_via_api(): void
    {
        Role::query()->create(['code' => 'super_admin', 'name' => 'Super Admin', 'is_active' => true]);
        Role::query()->create(['code' => 'engineer', 'name' => 'Engineer', 'is_active' => true]);

        $department = Department::query()->create([
            'code' => 'DEP-ENG-API',
            'name' => 'Engineering API',
            'is_active' => true,
        ]);

        $superAdmin = User::factory()->create([
            'email' => 'superadmin.users.api@example.com',
            'password' => bcrypt('secret123'),
            'role' => 'super_admin',
        ]);

        $token = $this->postJson('/api/v1/auth/login', [
            'email' => $superAdmin->email,
            'password' => 'secret123',
        ])->json('token');

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/users', [
                'name' => 'Engineer API User',
                'email' => 'engineer.api.user@example.com',
                'role' => 'engineer',
                'department_id' => $department->id,
                'password' => 'secret123',
                'password_confirmation' => 'secret123',
            ])
            ->assertOk()
            ->assertJsonPath('data.role', 'engineer')
            ->assertJsonPath('data.department_name', 'Engineering API');

        $this->assertDatabaseHas('users', [
            'email' => 'engineer.api.user@example.com',
            'role' => 'engineer',
            'department_id' => $department->id,
        ]);
    }

    public function test_engineer_cannot_access_user_management_api(): void
    {
        Role::query()->create(['code' => 'engineer', 'name' => 'Engineer', 'is_active' => true]);

        $engineer = User::factory()->create([
            'email' => 'engineer.forbidden.api@example.com',
            'password' => bcrypt('secret123'),
            'role' => 'engineer',
        ]);

        $token = $this->postJson('/api/v1/auth/login', [
            'email' => $engineer->email,
            'password' => 'secret123',
        ])->json('token');

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/v1/users')
            ->assertForbidden();
    }

    public function test_user_can_update_own_profile_via_api(): void
    {
        Role::query()->create(['code' => 'requester', 'name' => 'Requester', 'is_active' => true]);

        $user = User::factory()->create([
            'email' => 'profile.api@example.com',
            'password' => bcrypt('secret123'),
            'role' => 'requester',
        ]);

        $token = $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'secret123',
        ])->json('token');

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->putJson('/api/v1/auth/me', [
                'name' => 'Updated Profile API',
                'email' => 'profile.api.updated@example.com',
                'password' => 'newsecret123',
                'password_confirmation' => 'newsecret123',
                'current_password' => 'secret123',
            ])
            ->assertOk()
            ->assertJsonPath('data.name', 'Updated Profile API')
            ->assertJsonPath('data.email', 'profile.api.updated@example.com');

        $updatedUser = User::query()->where('id', $user->id)->firstOrFail();
        $this->assertTrue(Hash::check('newsecret123', (string) $updatedUser->password));
    }
}

