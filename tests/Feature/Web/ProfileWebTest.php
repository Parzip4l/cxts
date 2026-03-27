<?php

namespace Tests\Feature\Web;

use App\Models\Department;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProfileWebTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_update_own_profile_from_web_page(): void
    {
        Role::query()->create(['code' => 'requester', 'name' => 'Requester', 'is_active' => true]);

        $department = Department::query()->create([
            'code' => 'DEP-PROF-01',
            'name' => 'Profile Department',
            'is_active' => true,
        ]);

        $user = User::factory()->create([
            'role' => 'requester',
            'password' => bcrypt('secret123'),
        ]);

        $this->actingAs($user)
            ->get(route('profile.edit'))
            ->assertOk()
            ->assertSee('My Profile');

        $this->actingAs($user)
            ->put(route('profile.update'), [
                'name' => 'Updated Web Profile',
                'email' => 'updated.web.profile@example.com',
                'department_id' => $department->id,
                'password' => 'newsecret123',
                'password_confirmation' => 'newsecret123',
                'current_password' => 'secret123',
            ])
            ->assertRedirect(route('profile.edit'))
            ->assertSessionHas('success');

        $updatedUser = User::query()->findOrFail($user->id);

        $this->assertSame('Updated Web Profile', $updatedUser->name);
        $this->assertSame('updated.web.profile@example.com', $updatedUser->email);
        $this->assertSame($department->id, $updatedUser->department_id);
        $this->assertTrue(Hash::check('newsecret123', (string) $updatedUser->password));
    }
}

