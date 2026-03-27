<?php

namespace App\Services\SLA;

use App\Events\SlaBreached;
use App\Events\SlaWarningTriggered;
use App\Models\Ticket;
use App\Models\TicketActivity;
use App\Models\User;
use Carbon\CarbonImmutable;

class SLATrackingService
{
    public const WARNING_THRESHOLD_PERCENTAGE = 80;
    public const RESPONSE_WARNING_ACTIVITY = 'sla_response_warning_80';
    public const RESOLUTION_WARNING_ACTIVITY = 'sla_resolution_warning_80';
    public const RESPONSE_BREACH_ACTIVITY = 'sla_response_breached';
    public const RESOLUTION_BREACH_ACTIVITY = 'sla_resolution_breached';

    public function buildResumePayload(Ticket $ticket, CarbonImmutable $resumedAt): array
    {
        if ($ticket->paused_at === null) {
            return [
                'ticket_updates' => [],
                'activity_metadata' => [
                    'paused_duration_seconds' => 0,
                    'paused_duration_minutes' => 0,
                ],
            ];
        }

        $pausedAt = CarbonImmutable::instance($ticket->paused_at);
        $pausedDurationSeconds = max(0, $pausedAt->diffInSeconds($resumedAt));
        $ticketUpdates = [];

        if (
            $ticket->response_due_at !== null
            && $ticket->responded_at === null
            && $ticket->breached_response_at === null
        ) {
            $ticketUpdates['response_due_at'] = CarbonImmutable::instance($ticket->response_due_at)
                ->addSeconds($pausedDurationSeconds);
        }

        if (
            $ticket->resolution_due_at !== null
            && $ticket->completed_at === null
            && $ticket->breached_resolution_at === null
        ) {
            $ticketUpdates['resolution_due_at'] = CarbonImmutable::instance($ticket->resolution_due_at)
                ->addSeconds($pausedDurationSeconds);
        }

        return [
            'ticket_updates' => $ticketUpdates,
            'activity_metadata' => [
                'paused_duration_seconds' => $pausedDurationSeconds,
                'paused_duration_minutes' => (int) ceil($pausedDurationSeconds / 60),
                'response_due_at' => isset($ticketUpdates['response_due_at'])
                    ? $ticketUpdates['response_due_at']->toIso8601String()
                    : null,
                'resolution_due_at' => isset($ticketUpdates['resolution_due_at'])
                    ? $ticketUpdates['resolution_due_at']->toIso8601String()
                    : null,
            ],
        ];
    }

    public function sync(Ticket $ticket, ?User $actor = null, ?CarbonImmutable $referenceAt = null): Ticket
    {
        $referenceAt ??= CarbonImmutable::now();
        $ticket = $ticket->fresh() ?? $ticket;

        $responseState = $this->responseState($ticket, $referenceAt);
        $resolutionState = $this->resolutionState($ticket, $referenceAt);
        $ticketUpdates = [];

        if ($responseState['breach_at'] !== null && $ticket->breached_response_at === null) {
            $ticketUpdates['breached_response_at'] = $responseState['breach_at'];
        }

        if ($resolutionState['breach_at'] !== null && $ticket->breached_resolution_at === null) {
            $ticketUpdates['breached_resolution_at'] = $resolutionState['breach_at'];
        }

        if (($responseState['breach_at'] !== null || $resolutionState['breach_at'] !== null)) {
            $ticketUpdates['sla_status'] = Ticket::SLA_STATUS_BREACHED;
        } elseif (
            $ticket->sla_status === null
            && ($ticket->response_due_at !== null || $ticket->resolution_due_at !== null)
        ) {
            $ticketUpdates['sla_status'] = Ticket::SLA_STATUS_ON_TIME;
        }

        if ($ticketUpdates !== []) {
            if ($actor !== null) {
                $ticketUpdates['updated_by_id'] = $actor->id;
            }

            $ticket->update($ticketUpdates);
            $ticket->refresh();
        }

        if ($responseState['warning_at'] !== null) {
            $this->recordWarning(
                ticket: $ticket,
                actor: $actor,
                activityType: self::RESPONSE_WARNING_ACTIVITY,
                target: 'response',
                thresholdPercentage: self::WARNING_THRESHOLD_PERCENTAGE,
                triggeredAt: $responseState['warning_at'],
                dueAt: $responseState['due_at'],
            );
        }

        if ($resolutionState['warning_at'] !== null) {
            $this->recordWarning(
                ticket: $ticket,
                actor: $actor,
                activityType: self::RESOLUTION_WARNING_ACTIVITY,
                target: 'resolution',
                thresholdPercentage: self::WARNING_THRESHOLD_PERCENTAGE,
                triggeredAt: $resolutionState['warning_at'],
                dueAt: $resolutionState['due_at'],
            );
        }

        if ($responseState['breach_at'] !== null) {
            $this->recordBreach(
                ticket: $ticket,
                actor: $actor,
                activityType: self::RESPONSE_BREACH_ACTIVITY,
                target: 'response',
                breachedAt: $responseState['breach_at'],
                dueAt: $responseState['due_at'],
            );
        }

        if ($resolutionState['breach_at'] !== null) {
            $this->recordBreach(
                ticket: $ticket,
                actor: $actor,
                activityType: self::RESOLUTION_BREACH_ACTIVITY,
                target: 'resolution',
                breachedAt: $resolutionState['breach_at'],
                dueAt: $resolutionState['due_at'],
            );
        }

        return $ticket;
    }

    private function responseState(Ticket $ticket, CarbonImmutable $referenceAt): array
    {
        $dueAt = $ticket->response_due_at !== null
            ? CarbonImmutable::instance($ticket->response_due_at)
            : null;

        if ($dueAt === null) {
            return ['warning_at' => null, 'breach_at' => null, 'due_at' => null];
        }

        $respondedAt = $ticket->responded_at !== null
            ? CarbonImmutable::instance($ticket->responded_at)
            : null;

        $breachAt = $ticket->breached_response_at !== null
            ? CarbonImmutable::instance($ticket->breached_response_at)
            : null;

        if ($breachAt === null && $respondedAt !== null && $respondedAt->gt($dueAt)) {
            $breachAt = $dueAt;
        }

        if ($breachAt === null && $respondedAt === null && $referenceAt->gt($dueAt)) {
            $breachAt = $dueAt;
        }

        $warningAt = null;
        if ($breachAt === null && $respondedAt === null) {
            $candidateWarningAt = $this->warningThresholdAt(
                startedAt: CarbonImmutable::instance($ticket->created_at),
                dueAt: $dueAt,
                pausedSeconds: 0,
            );

            if ($candidateWarningAt !== null && $referenceAt->gte($candidateWarningAt)) {
                $warningAt = $candidateWarningAt;
            }
        }

        return [
            'warning_at' => $warningAt,
            'breach_at' => $breachAt,
            'due_at' => $dueAt,
        ];
    }

    private function resolutionState(Ticket $ticket, CarbonImmutable $referenceAt): array
    {
        $dueAt = $ticket->resolution_due_at !== null
            ? CarbonImmutable::instance($ticket->resolution_due_at)
            : null;

        if ($dueAt === null) {
            return ['warning_at' => null, 'breach_at' => null, 'due_at' => null];
        }

        $completedAt = $ticket->completed_at !== null
            ? CarbonImmutable::instance($ticket->completed_at)
            : ($ticket->resolved_at !== null ? CarbonImmutable::instance($ticket->resolved_at) : null);

        $breachAt = $ticket->breached_resolution_at !== null
            ? CarbonImmutable::instance($ticket->breached_resolution_at)
            : null;

        if ($breachAt === null && $completedAt !== null && $completedAt->gt($dueAt)) {
            $breachAt = $dueAt;
        }

        if (
            $breachAt === null
            && $completedAt === null
            && $ticket->paused_at === null
            && $referenceAt->gt($dueAt)
        ) {
            $breachAt = $dueAt;
        }

        $warningAt = null;
        if ($breachAt === null && $completedAt === null && $ticket->paused_at === null) {
            $candidateWarningAt = $this->warningThresholdAt(
                startedAt: CarbonImmutable::instance($ticket->created_at),
                dueAt: $dueAt,
                pausedSeconds: $this->totalPausedSeconds($ticket, $referenceAt),
            );

            if ($candidateWarningAt !== null && $referenceAt->gte($candidateWarningAt)) {
                $warningAt = $candidateWarningAt;
            }
        }

        return [
            'warning_at' => $warningAt,
            'breach_at' => $breachAt,
            'due_at' => $dueAt,
        ];
    }

    private function warningThresholdAt(
        CarbonImmutable $startedAt,
        CarbonImmutable $dueAt,
        int $pausedSeconds,
    ): ?CarbonImmutable {
        $windowSeconds = max(0, $startedAt->diffInSeconds($dueAt) - $pausedSeconds);

        if ($windowSeconds === 0) {
            return $dueAt;
        }

        return $startedAt
            ->addSeconds($pausedSeconds)
            ->addSeconds((int) ceil($windowSeconds * (self::WARNING_THRESHOLD_PERCENTAGE / 100)));
    }

    private function totalPausedSeconds(Ticket $ticket, CarbonImmutable $referenceAt): int
    {
        $completedPauseSeconds = TicketActivity::query()
            ->where('ticket_id', $ticket->id)
            ->where('activity_type', 'work_resumed')
            ->get(['metadata'])
            ->sum(fn (TicketActivity $activity): int => (int) ($activity->metadata['paused_duration_seconds'] ?? 0));

        if ($ticket->paused_at === null) {
            return $completedPauseSeconds;
        }

        $pausedAt = CarbonImmutable::instance($ticket->paused_at);

        return $completedPauseSeconds + max(0, $pausedAt->diffInSeconds($referenceAt));
    }

    private function recordWarning(
        Ticket $ticket,
        ?User $actor,
        string $activityType,
        string $target,
        int $thresholdPercentage,
        CarbonImmutable $triggeredAt,
        ?CarbonImmutable $dueAt,
    ): void {
        if ($this->activityExists($ticket, $activityType)) {
            return;
        }

        TicketActivity::query()->create([
            'ticket_id' => $ticket->id,
            'actor_user_id' => $actor?->id,
            'activity_type' => $activityType,
            'old_status_id' => $ticket->ticket_status_id,
            'new_status_id' => $ticket->ticket_status_id,
            'metadata' => [
                'target' => $target,
                'threshold_percentage' => $thresholdPercentage,
                'triggered_at' => $triggeredAt->toIso8601String(),
                'due_at' => $dueAt?->toIso8601String(),
            ],
        ]);

        event(new SlaWarningTriggered($ticket->fresh(), $target, $thresholdPercentage, $triggeredAt));
    }

    private function recordBreach(
        Ticket $ticket,
        ?User $actor,
        string $activityType,
        string $target,
        CarbonImmutable $breachedAt,
        ?CarbonImmutable $dueAt,
    ): void {
        if ($this->activityExists($ticket, $activityType)) {
            return;
        }

        TicketActivity::query()->create([
            'ticket_id' => $ticket->id,
            'actor_user_id' => $actor?->id,
            'activity_type' => $activityType,
            'old_status_id' => $ticket->ticket_status_id,
            'new_status_id' => $ticket->ticket_status_id,
            'metadata' => [
                'target' => $target,
                'breached_at' => $breachedAt->toIso8601String(),
                'due_at' => $dueAt?->toIso8601String(),
            ],
        ]);

        event(new SlaBreached($ticket->fresh(), $target, $breachedAt));
    }

    private function activityExists(Ticket $ticket, string $activityType): bool
    {
        return TicketActivity::query()
            ->where('ticket_id', $ticket->id)
            ->where('activity_type', $activityType)
            ->exists();
    }
}
