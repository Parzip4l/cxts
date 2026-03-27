<?php

namespace App\Modules\Notifications;

use App\Models\Inspection;
use App\Models\Ticket;
use App\Models\TicketActivity;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class NotificationCenterService
{
    public function latestForUser(User $user, int $limit = 10): Collection
    {
        $items = collect()
            ->merge($this->approvalNotifications($user))
            ->merge($this->engineerNotifications($user))
            ->merge($this->opsNotifications($user))
            ->sortByDesc(fn (array $item) => $item['occurred_at'])
            ->values();

        return $items->take($limit)->values();
    }

    public function unreadCountForUser(User $user): int
    {
        return $this->latestForUser($user, 99)
            ->filter(fn (array $item) => $item['occurred_at']->greaterThanOrEqualTo(now()->subDays(3)))
            ->count();
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
            ->where('assigned_engineer_id', $user->id)
            ->whereHas('status', fn ($query) => $query->where('is_closed', false))
            ->latest('last_status_changed_at')
            ->limit(10)
            ->get();

        return $tickets->map(fn (Ticket $ticket) => [
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
                'sla_response_breached',
                'sla_resolution_breached',
                'work_completed',
            ])
            ->latest()
            ->limit(10)
            ->get()
            ->map(fn (TicketActivity $activity) => [
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
            'ticket_approved', 'work_completed' => 'success',
            'ticket_rejected', 'sla_response_breached', 'sla_resolution_breached' => 'danger',
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
            'sla_response_breached', 'sla_resolution_breached' => 'solar:danger-triangle-outline',
            'work_completed' => 'solar:check-read-outline',
            default => 'solar:bell-bing-outline',
        };
    }
}
