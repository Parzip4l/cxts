<?php

namespace Tests\Feature\Web;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RoleLandingTest extends TestCase
{
    use RefreshDatabase;

    public function test_requester_lands_on_ticket_list_after_login(): void
    {
        $user = $this->createUser('requester', 'requester.landing@example.com');

        $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'password',
        ])->assertRedirect(route('tickets.index', absolute: false));
    }

    public function test_engineer_lands_on_assigned_tasks_after_login(): void
    {
        $user = $this->createUser('engineer', 'engineer.landing@example.com');

        $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'password',
        ])->assertRedirect(route('engineer-tasks.index', absolute: false));
    }

    public function test_inspection_officer_lands_on_inspections_after_login(): void
    {
        $user = $this->createUser('inspection_officer', 'inspection.landing@example.com');

        $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'password',
        ])->assertRedirect(route('inspections.index', absolute: false));
    }

    public function test_dashboard_redirects_non_ops_roles_to_their_role_landing_page(): void
    {
        $requester = $this->createUser('requester', 'requester.dashboard@example.com');
        $engineer = $this->createUser('engineer', 'engineer.dashboard@example.com');
        $inspectionOfficer = $this->createUser('inspection_officer', 'inspection.dashboard@example.com');

        $this->actingAs($requester)
            ->get(route('dashboard'))
            ->assertRedirect(route('tickets.index'));

        $this->actingAs($engineer)
            ->get(route('dashboard'))
            ->assertRedirect(route('engineer-tasks.index'));

        $this->actingAs($inspectionOfficer)
            ->get(route('dashboard'))
            ->assertRedirect(route('inspections.index'));
    }

    private function createUser(string $role, string $email): User
    {
        return User::query()->create([
            'name' => ucfirst(str_replace('_', ' ', $role)),
            'email' => $email,
            'password' => Hash::make('password'),
            'role' => $role,
            'email_verified_at' => now(),
        ]);
    }
}
