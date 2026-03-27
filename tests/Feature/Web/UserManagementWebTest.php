<?php

namespace Tests\Feature\Web;

use App\Models\Department;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserManagementWebTest extends TestCase
{
    use RefreshDatabase;

    public function test_operational_admin_can_create_engineer_from_master_data_users_page(): void
    {
        Role::query()->create(['code' => 'operational_admin', 'name' => 'Operational Admin', 'is_active' => true]);
        Role::query()->create(['code' => 'engineer', 'name' => 'Engineer', 'is_active' => true]);

        $department = Department::query()->create([
            'code' => 'DEP-WEB-USR',
            'name' => 'Web User Department',
            'is_active' => true,
        ]);

        $admin = User::factory()->create([
            'role' => 'operational_admin',
        ]);

        $this->actingAs($admin)
            ->post(route('master-data.users.store'), [
                'name' => 'Engineer Web User',
                'email' => 'engineer.web.user@example.com',
                'role' => 'engineer',
                'department_id' => $department->id,
                'password' => 'secret123',
                'password_confirmation' => 'secret123',
            ])
            ->assertRedirect(route('master-data.users.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'email' => 'engineer.web.user@example.com',
            'role' => 'engineer',
            'department_id' => $department->id,
        ]);
    }
}

