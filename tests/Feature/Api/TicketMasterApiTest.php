<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TicketMasterApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_manage_ticket_master_data_via_api(): void
    {
        $admin = User::factory()->create([
            'email' => 'ticket-master-admin@example.com',
            'password' => bcrypt('secret123'),
            'role' => 'super_admin',
        ]);

        $token = $this->postJson('/api/v1/auth/login', [
            'email' => $admin->email,
            'password' => 'secret123',
        ])->json('token');

        $categoryResponse = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/ticket-categories', [
                'code' => 'INCIDENT',
                'name' => 'Incident',
                'is_active' => true,
            ])
            ->assertCreated()
            ->assertJsonPath('data.code', 'INCIDENT');

        $categoryId = (int) $categoryResponse->json('data.id');

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/ticket-subcategories', [
                'ticket_category_id' => $categoryId,
                'code' => 'NETWORK_DOWN',
                'name' => 'Network Down',
                'is_active' => true,
            ])
            ->assertCreated()
            ->assertJsonPath('data.ticket_category_id', $categoryId);

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/ticket-priorities', [
                'code' => 'P2',
                'name' => 'High',
                'level' => 2,
                'response_target_minutes' => 30,
                'resolution_target_minutes' => 240,
                'is_active' => true,
            ])
            ->assertCreated()
            ->assertJsonPath('data.code', 'P2');

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/ticket-statuses', [
                'code' => 'ASSIGNED',
                'name' => 'Assigned',
                'is_open' => true,
                'is_in_progress' => false,
                'is_closed' => false,
                'is_active' => true,
            ])
            ->assertCreated()
            ->assertJsonPath('data.code', 'ASSIGNED');
    }
}
