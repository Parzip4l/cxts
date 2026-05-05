<?php

namespace Tests\Feature\Web;

use App\Models\Department;
use App\Models\Ticket;
use App\Models\TicketActivity;
use App\Models\TicketCategory;
use App\Models\TicketPriority;
use App\Models\TicketStatus;
use App\Models\User;
use App\Models\UserNotificationState;
use App\Modules\Notifications\NotificationCenterService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationCenterWebTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_mark_notification_read_and_acknowledge_it(): void
    {
        [$user, $activity] = $this->createOpsNotificationFixture();
        $notificationKey = 'ticket-activity-'.$activity->id;

        $this->actingAs($user)
            ->get(route('notifications.center'))
            ->assertOk()
            ->assertSee('New ticket TCK-NOTIF-0001')
            ->assertSee('Unread');

        $this->assertSame(1, app(NotificationCenterService::class)->unreadCountForUser($user));

        $this->actingAs($user)
            ->post(route('notifications.read', $notificationKey))
            ->assertRedirect();

        $this->assertDatabaseHas('user_notification_states', [
            'user_id' => $user->id,
            'notification_key' => $notificationKey,
            'notification_type' => 'ticket_activity',
        ]);

        $this->assertNotNull(UserNotificationState::query()->where('notification_key', $notificationKey)->value('read_at'));
        $this->assertSame(0, app(NotificationCenterService::class)->unreadCountForUser($user));

        $this->actingAs($user)
            ->post(route('notifications.acknowledge', $notificationKey))
            ->assertRedirect();

        $state = UserNotificationState::query()->where('notification_key', $notificationKey)->firstOrFail();
        $this->assertNotNull($state->read_at);
        $this->assertNotNull($state->acknowledged_at);
    }

    public function test_open_notification_marks_it_read_before_redirecting(): void
    {
        [$user, $activity] = $this->createOpsNotificationFixture('TCK-NOTIF-0002');
        $notificationKey = 'ticket-activity-'.$activity->id;

        $this->actingAs($user)
            ->get(route('notifications.open', $notificationKey))
            ->assertRedirect(route('tickets.show', $activity->ticket));

        $this->assertNotNull(UserNotificationState::query()->where('notification_key', $notificationKey)->value('read_at'));
    }

    /**
     * @return array{0: User, 1: TicketActivity}
     */
    private function createOpsNotificationFixture(string $ticketNumber = 'TCK-NOTIF-0001'): array
    {
        $department = Department::query()->create([
            'code' => 'DEP-NOTIF',
            'name' => 'Notification Department',
            'is_active' => true,
        ]);

        $user = User::factory()->create([
            'role' => 'super_admin',
            'department_id' => $department->id,
        ]);

        $requester = User::factory()->create([
            'role' => 'requester',
            'department_id' => $department->id,
        ]);

        $category = TicketCategory::query()->create([
            'code' => 'INCIDENT',
            'name' => 'Incident',
            'is_active' => true,
        ]);

        $priority = TicketPriority::query()->create([
            'code' => 'P3',
            'name' => 'Medium',
            'level' => 3,
            'response_target_minutes' => 60,
            'resolution_target_minutes' => 480,
            'is_active' => true,
        ]);

        $status = TicketStatus::query()->create([
            'code' => 'NEW',
            'name' => 'New',
            'is_open' => true,
            'is_active' => true,
        ]);

        $ticket = Ticket::query()->create([
            'ticket_number' => $ticketNumber,
            'title' => 'Notification test ticket',
            'description' => 'Ticket used to verify notification read state.',
            'requester_id' => $requester->id,
            'requester_department_id' => $department->id,
            'ticket_category_id' => $category->id,
            'ticket_priority_id' => $priority->id,
            'ticket_status_id' => $status->id,
            'source' => 'web',
            'impact' => 'medium',
            'urgency' => 'medium',
        ]);

        $activity = TicketActivity::query()->create([
            'ticket_id' => $ticket->id,
            'actor_user_id' => $requester->id,
            'activity_type' => 'ticket_created',
            'old_status_id' => null,
            'new_status_id' => $status->id,
            'metadata' => [],
        ]);

        return [$user, $activity->load('ticket')];
    }
}
