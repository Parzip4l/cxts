<?php

namespace App\Modules\Notifications;

use App\Models\Inspection;
use App\Models\Ticket;
use App\Models\TicketActivity;
use App\Models\User;
use App\Models\UserNotificationState;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class NotificationCenterService
{
    public function latestForUser(User $user, int $limit = 10): Collection
    {
        return $this->withUserState($user, $this->candidateNotifications($user))
            ->take($limit)
            ->values();
    }

    public function unreadCountForUser(User $user): int
    {
        return $this->latestForUser($user, 99)
            ->where('is_read', false)
            ->count();
    }

    public function markRead(User $user, string $notificationKey): void
    {
        UserNotificationState::query()->updateOrCreate(
            [
                'user_id' => $user->id,
                'notification_key' => $notificationKey,
            ],
            [
                'notification_type' => $this->typeFromKey($notificationKey),
                'read_at' => now(),
            ],
        );
    }

    public function acknowledge(User $user, string $notificationKey): void
    {
        $state = UserNotificationState::query()->firstOrNew([
            'user_id' => $user->id,
            'notification_key' => $notificationKey,
        ]);

        $state->fill([
            'notification_type' => $state->notification_type ?: $this->typeFromKey($notificationKey),
            'read_at' => $state->read_at ?? now(),
            'acknowledged_at' => now(),
        ]);
        $state->save();
    }

    public function markAllRead(User $user, int $limit = 99): int
    {
        $notifications = $this->latestForUser($user, $limit)
            ->where('is_read', false)
            ->values();

        foreach ($notifications as $notification) {
            $this->markRead($user, $notification['key']);
        }

        return $notifications->count();
    }

    public function findForUser(User $user, string $notificationKey): ?array
    {
        return $this->latestForUser($user, 99)
            ->firstWhere('key', $notificationKey);
    }

    private function candidateNotifications(User $user): Collection
    {
        $items = collect()
            ->merge($this->approvalNotifications($user))
            ->merge($this->engineerNotifications($user))
            ->merge($this->opsNotifications($user))
            ->sortByDesc(fn (array $item) => $item['occurred_at'])
            ->values();

        return $items;
    }

    private function withUserState(User $user, Collection $notifications): Collection
    {
        $keys = $notifications->pluck('key')->filter()->values();

        if ($keys->isEmpty()) {
            return $notifications;
        }

        $states = UserNotificationState::query()
            ->where('user_id', $user->id)
            ->whereIn('notification_key', $keys)
            ->get()
            ->keyBy('notification_key');

        return $notifications->map(function (array $notification) use ($states): array {
            $state = $states->get($notification['key']);

            $notification['read_at'] = $state?->read_at;
            $notification['acknowledged_at'] = $state?->acknowledged_at;
            $notification['is_read'] = $state?->read_at !== null;
            $notification['is_acknowledged'] = $state?->acknowledged_at !== null;
            $notification['open_url'] = route('notifications.open', $notification['key']);

            return $notification;
        });
    }

    private function approvalNotifications(User $user): Collection
    {
        $tickets = Ticket::query()
            ->with('status:id,name')
            ->where('expected_approver_id', $user->id)
            ->where('approval_status', Ticket::APPROVAL_STATUS_PENDING)
            ->latest('approval_requested_at')
            ->limit(10)
            ->get();

        return $tickets->map(fn (Ticket $ticket) => [
            'key' => 'approval-ticket-'.$ticket->id,
            'title' => 'Approval needed for ' . $ticket->ticket_number,
            'message' => $ticket->title,
            'type' => 'approval',
            'badge_class' => 'warning',
            'icon' => 'solar:shield-warning-outline',
            'url' => route('tickets.show', $ticket),
            'occurred_at' => $ticket->approval_requested_at ?? $ticket->created_at,
        ]);
    }

    private function engineerNotifications(User $user): Collection
    {
        if (! $user->hasPermission('engineer_task.view_assigned')) {
            return collect();
        }

        $tickets = Ticket::query()
            ->with('status:id,name')
            ->where(function ($query) use ($user): void {
                $query->where('assigned_engineer_id', $user->id)
                    ->orWhereHas('assignedEngineers', fn ($engineerQuery) => $engineerQuery->whereKey($user->id));
            })
            ->whereHas('status', fn ($query) => $query->where('is_closed', false))
            ->latest('last_status_changed_at')
            ->limit(10)
            ->get();

        return $tickets->map(fn (Ticket $ticket) => [
            'key' => 'assignment-ticket-'.$ticket->id,
            'title' => 'Assigned ticket ' . $ticket->ticket_number,
            'message' => ($ticket->status?->name ?? 'Open') . ' · ' . $ticket->title,
            'type' => 'assignment',
            'badge_class' => 'primary',
            'icon' => 'solar:ticket-outline',
            'url' => route('engineer-tasks.show', $ticket),
            'occurred_at' => $ticket->last_status_changed_at ?? $ticket->updated_at,
        ]);
    }

    private function opsNotifications(User $user): Collection
    {
        if (! $user->hasPermission('dashboard.view_ops') && ! $user->hasPermission('inspection_result.view_assigned')) {
            return collect();
        }

        $activities = TicketActivity::query()
            ->with(['ticket:id,ticket_number,title', 'actor:id,name'])
            ->whereIn('activity_type', [
                'ticket_created',
                'ticket_approved',
                'ticket_rejected',
                'ticket_ready_for_assignment',
                'ticket_pending_customer',
                'ticket_closed',
                'ticket_reopened',
                'ticket_cancelled',
                'sla_response_breached',
                'sla_resolution_breached',
                'work_completed',
            ])
            ->latest()
            ->limit(10)
            ->get()
            ->map(fn (TicketActivity $activity) => [
                'key' => 'ticket-activity-'.$activity->id,
                'title' => $this->ticketActivityTitle($activity),
                'message' => $activity->ticket?->title ?? 'Ticket activity',
                'type' => 'ticket_activity',
                'badge_class' => $this->ticketActivityBadgeClass($activity->activity_type),
                'icon' => $this->ticketActivityIcon($activity->activity_type),
                'url' => $activity->ticket ? route('tickets.show', $activity->ticket) : route('tickets.index'),
                'occurred_at' => $activity->created_at,
            ]);

        $inspections = Inspection::query()
            ->with(['officer:id,name'])
            ->where('final_result', Inspection::FINAL_RESULT_ABNORMAL)
            ->latest('submitted_at')
            ->limit(5)
            ->get()
            ->map(fn (Inspection $inspection) => [
                'key' => 'inspection-abnormal-'.$inspection->id,
                'title' => 'Abnormal inspection result ' . $inspection->inspection_number,
                'message' => 'Officer: ' . ($inspection->officer?->name ?? 'Unassigned'),
                'type' => 'inspection',
                'badge_class' => 'danger',
                'icon' => 'solar:clipboard-check-outline',
                'url' => route('inspection-results.show', $inspection),
                'occurred_at' => $inspection->submitted_at ?? $inspection->updated_at,
            ]);

        return $activities->merge($inspections);
    }

    private function ticketActivityTitle(TicketActivity $activity): string
    {
        $ticketNumber = $activity->ticket?->ticket_number ?? 'Ticket';

        return match ($activity->activity_type) {
            'ticket_created' => 'New ticket ' . $ticketNumber,
            'ticket_approved' => 'Ticket approved ' . $ticketNumber,
            'ticket_rejected' => 'Ticket rejected ' . $ticketNumber,
            'ticket_ready_for_assignment' => 'Ready for assignment ' . $ticketNumber,
            'ticket_pending_customer' => 'Pending customer ' . $ticketNumber,
            'ticket_closed' => 'Ticket closed ' . $ticketNumber,
            'ticket_reopened' => 'Ticket reopened ' . $ticketNumber,
            'ticket_cancelled' => 'Ticket cancelled ' . $ticketNumber,
            'sla_response_breached' => 'Response SLA breached ' . $ticketNumber,
            'sla_resolution_breached' => 'Resolution SLA breached ' . $ticketNumber,
            'work_completed' => 'Work completed ' . $ticketNumber,
            default => 'Ticket update ' . $ticketNumber,
        };
    }

    private function ticketActivityBadgeClass(string $activityType): string
    {
        return match ($activityType) {
            'ticket_created' => 'primary',
            'ticket_approved', 'work_completed', 'ticket_closed' => 'success',
            'ticket_reopened', 'ticket_pending_customer' => 'warning',
            'ticket_rejected', 'ticket_cancelled', 'sla_response_breached', 'sla_resolution_breached' => 'danger',
            default => 'info',
        };
    }

    private function ticketActivityIcon(string $activityType): string
    {
        return match ($activityType) {
            'ticket_created' => 'solar:ticket-outline',
            'ticket_approved' => 'solar:check-circle-outline',
            'ticket_rejected' => 'solar:close-circle-outline',
            'ticket_ready_for_assignment' => 'solar:plain-2-outline',
            'ticket_pending_customer' => 'solar:user-block-outline',
            'ticket_closed' => 'solar:archive-check-outline',
            'ticket_reopened' => 'solar:restart-outline',
            'ticket_cancelled' => 'solar:close-square-outline',
            'sla_response_breached', 'sla_resolution_breached' => 'solar:danger-triangle-outline',
            'work_completed' => 'solar:check-read-outline',
            default => 'solar:bell-bing-outline',
        };
    }

    private function typeFromKey(string $notificationKey): string
    {
        if (str_starts_with($notificationKey, 'approval-')) {
            return 'approval';
        }

        if (str_starts_with($notificationKey, 'assignment-')) {
            return 'assignment';
        }

        if (str_starts_with($notificationKey, 'ticket-activity-')) {
            return 'ticket_activity';
        }

        if (str_starts_with($notificationKey, 'inspection-')) {
            return 'inspection';
        }

        return 'general';
    }
}
