<?php

namespace Tests\Feature\Api;

use App\Models\Inspection;
use App\Models\InspectionTemplate;
use App\Models\Ticket;
use App\Models\TicketPriority;
use App\Models\TicketStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MobileNotificationApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_engineer_can_register_device_token_and_fetch_notifications(): void
    {
        $engineer = User::factory()->create([
            'email' => 'engineer.notification@example.com',
            'password' => bcrypt('secret123'),
            'role' => 'engineer',
        ]);

        $ticketStatus = TicketStatus::query()->create([
            'code' => 'ASSIGNED',
            'name' => 'Assigned',
            'is_open' => true,
            'is_active' => true,
        ]);

        $ticketPriority = TicketPriority::query()->create([
            'code' => 'P2',
            'name' => 'High',
            'level' => 2,
            'response_target_minutes' => 30,
            'resolution_target_minutes' => 240,
            'is_active' => true,
        ]);

        Ticket::query()->create([
            'ticket_number' => 'TCK-NOTIF-0001',
            'title' => 'Notification ticket',
            'description' => 'Generated for mobile notification API test.',
            'ticket_status_id' => $ticketStatus->id,
            'ticket_priority_id' => $ticketPriority->id,
            'assigned_engineer_id' => $engineer->id,
            'source' => 'api',
            'impact' => 'medium',
            'urgency' => 'medium',
        ]);

        $template = InspectionTemplate::query()->create([
            'code' => 'INSP-NOTIF-001',
            'name' => 'Inspection Notification Template',
            'is_active' => true,
        ]);

        Inspection::query()->create([
            'inspection_number' => 'INSP-NOTIF-0001',
            'inspection_template_id' => $template->id,
            'inspection_officer_id' => $engineer->id,
            'inspection_date' => now()->toDateString(),
            'status' => Inspection::STATUS_IN_PROGRESS,
        ]);

        $token = $this->postJson('/api/v1/auth/login', [
            'email' => $engineer->email,
            'password' => 'secret123',
        ])->json('token');

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/mobile/notifications/device-token', [
                'token' => 'fcm-token-123456',
                'platform' => 'android',
                'device_name' => 'Pixel Test',
                'app_version' => '1.0.0',
            ])
            ->assertOk()
            ->assertJsonPath('message', 'Device token registered.');

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/v1/mobile/notifications')
            ->assertOk()
            ->assertJsonPath('meta.total', 2)
            ->assertJsonFragment(['type' => 'ticket'])
            ->assertJsonFragment(['type' => 'inspection']);

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/v1/mobile/notifications/firebase-config')
            ->assertOk()
            ->assertJsonStructure(['enabled', 'project_id', 'android_channel_id', 'topics']);
    }

    public function test_requester_cannot_access_mobile_notification_endpoints(): void
    {
        $requester = User::factory()->create([
            'email' => 'requester.notification@example.com',
            'password' => bcrypt('secret123'),
            'role' => 'requester',
        ]);

        $token = $this->postJson('/api/v1/auth/login', [
            'email' => $requester->email,
            'password' => 'secret123',
        ])->json('token');

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/v1/mobile/notifications')
            ->assertForbidden();
    }
}
