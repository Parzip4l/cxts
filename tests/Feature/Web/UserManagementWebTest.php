<?php

namespace Tests\Feature\Web;

use App\Models\Department;
use App\Models\Permission;
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

        $organizationPermission = Permission::query()->create([
            'code' => 'organization.manage',
            'name' => 'Manage Organization Data',
            'is_active' => true,
        ]);
        Role::query()
            ->where('code', 'operational_admin')
            ->firstOrFail()
            ->permissions()
            ->attach($organizationPermission);

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

    public function test_super_admin_can_open_user_management_without_seeded_permission_pivot(): void
    {
        Role::query()->create(['code' => 'super_admin', 'name' => 'Super Admin', 'is_active' => true]);

        $admin = User::factory()->create(['role' => 'super_admin']);

        $this->actingAs($admin)
            ->get(route('master-data.users.index'))
            ->assertOk()
            ->assertSee('User &amp; Role Management', false)
            ->assertSee('Add User');
    }

    public function test_super_admin_can_change_an_existing_users_role(): void
    {
        Role::query()->create(['code' => 'super_admin', 'name' => 'Super Admin', 'is_active' => true]);
        Role::query()->create(['code' => 'requester', 'name' => 'Requester', 'is_active' => true]);
        Role::query()->create(['code' => 'inspection_officer', 'name' => 'Inspection Officer', 'is_active' => true]);

        $admin = User::factory()->create(['role' => 'super_admin']);
        $user = User::factory()->create(['role' => 'requester']);

        $this->actingAs($admin)
            ->put(route('master-data.users.update', $user), [
                'name' => $user->name,
                'email' => $user->email,
                'role' => 'inspection_officer',
            ])
            ->assertRedirect(route('master-data.users.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'role' => 'inspection_officer',
        ]);
    }
}
