<?php

namespace App\Modules\Dashboards\Operations;

use App\Models\Inspection;
use App\Models\Ticket;
use App\Models\TicketWorklog;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class OperationsDashboardService
{

    public function overview(?User $actor, array $filters = []): array
    {
        [$from, $to] = $this->resolvePeriod($filters);

        return [
            'period' => $this->serializePeriod($from, $to),
            'summary' => $this->ticketSummary($actor, $from, $to, $filters),
            'sla' => $this->slaSummary($actor, $from, $to, $filters),
            'inspection_summary' => $this->inspectionSummary($actor, $from, $to),
            'daily_trend' => $this->ticketDailyTrend($actor, $from, $to, $filters),
            'top_engineers' => $this->engineerStats($actor, $from, $to, $filters, 5)->values()->all(),
            'report_structure' => $this->reportStructure($actor, $from, $to, $filters, 10),
        ];
    }

    public function slaPerformance(?User $actor, array $filters = []): array
    {
        [$from, $to] = $this->resolvePeriod($filters);

        return [
            'period' => $this->serializePeriod($from, $to),
            'summary' => $this->slaSummary($actor, $from, $to, $filters),
            'breach_tickets' => $this->breachTickets($actor, $from, $to, $filters),
            'daily_breach_trend' => $this->slaBreachTrend($actor, $from, $to, $filters),
        ];
    }

    public function engineerEffectiveness(?User $actor, array $filters = []): array
    {
        [$from, $to] = $this->resolvePeriod($filters);
        $engineers = $this->engineerStats($actor, $from, $to, $filters)->values();

        $totalAssigned = (int) $engineers->sum('assigned_tickets');
        $totalCompleted = (int) $engineers->sum('completed_tickets');

        return [
            'period' => $this->serializePeriod($from, $to),
            'summary' => [
                'engineer_count' => $engineers->count(),
                'total_assigned_tickets' => $totalAssigned,
                'total_completed_tickets' => $totalCompleted,
                'overall_completion_rate' => $this->percentage($totalCompleted, $totalAssigned),
                'avg_effectiveness_score' => round((float) $engineers->avg('effectiveness_score'), 2),
                'total_worklog_minutes' => (int) $engineers->sum('total_worklog_minutes'),
            ],
            'engineers' => $engineers->all(),
        ];
    }

    public function myEngineerPerformance(User $engineer, array $filters = []): array
    {
        [$from, $to] = $this->resolvePeriod($filters);
        $engineerStats = $this->engineerStats($engineer, $from, $to, $filters, null, $engineer->id)->first();

        return [
            'period' => $this->serializePeriod($from, $to),
            'engineer' => $engineerStats,
            'sla' => $this->slaSummary($engineer, $from, $to, $filters),
            'recent_tickets' => $this->recentTicketsForEngineer($engineer, $from, $to, $filters),
        ];
    }

    private function ticketSummary(?User $actor, CarbonImmutable $from, CarbonImmutable $to, array $filters = []): array
    {
        $now = CarbonImmutable::now();
        $baseQuery = $this->ticketBaseQuery($actor, $from, $to, $filters);

        $totalTickets = (clone $baseQuery)->count();
        $completedTickets = (clone $baseQuery)->whereNotNull('tickets.completed_at')->count();
        $openTickets = (clone $baseQuery)->whereNull('tickets.completed_at')->count();

        $avgResponseMinutes = (clone $baseQuery)
            ->whereNotNull('tickets.responded_at')
            ->selectRaw('AVG(EXTRACT(EPOCH FROM (tickets.responded_at - tickets.created_at)) / 60) as avg_minutes')
            ->value('avg_minutes');

        $avgResolutionMinutes = (clone $baseQuery)
            ->whereNotNull('tickets.completed_at')
            ->selectRaw('AVG(EXTRACT(EPOCH FROM (tickets.completed_at - tickets.created_at)) / 60) as avg_minutes')
            ->value('avg_minutes');

        return [
            'total_tickets' => $totalTickets,
            'open_tickets' => $openTickets,
            'completed_tickets' => $completedTickets,
            'in_progress_tickets' => (clone $baseQuery)->whereNotNull('tickets.started_at')->whereNull('tickets.completed_at')->count(),
            'unassigned_tickets' => (clone $baseQuery)->whereNull('tickets.assigned_engineer_id')->count(),
            'overdue_resolution_tickets' => (clone $baseQuery)
                ->whereNull('tickets.completed_at')
                ->whereNull('tickets.paused_at')
                ->whereNotNull('tickets.resolution_due_at')
                ->where('tickets.resolution_due_at', '<', $now)
                ->count(),
            'avg_response_minutes' => $avgResponseMinutes !== null ? round((float) $avgResponseMinutes, 2) : null,
            'avg_resolution_minutes' => $avgResolutionMinutes !== null ? round((float) $avgResolutionMinutes, 2) : null,
        ];
    }

    private function slaSummary(?User $actor, CarbonImmutable $from, CarbonImmutable $to, array $filters = []): array
    {
        $now = CarbonImmutable::now();
        $baseQuery = $this->ticketBaseQuery($actor, $from, $to, $filters);

        $responseOnTime = (clone $baseQuery)
            ->whereNotNull('tickets.response_due_at')
            ->whereNotNull('tickets.responded_at')
            ->whereColumn('tickets.responded_at', '<=', 'tickets.response_due_at')
            ->count();

        $responseBreached = (clone $baseQuery)
            ->whereNotNull('tickets.response_due_at')
            ->where(function (Builder $query) use ($now): void {
                $query->where(function (Builder $started): void {
                    $started->whereNotNull('tickets.responded_at')
                        ->whereColumn('tickets.responded_at', '>', 'tickets.response_due_at');
                })->orWhere(function (Builder $pending) use ($now): void {
                    $pending->whereNull('tickets.responded_at')
                        ->whereNull('tickets.paused_at')
                        ->where('tickets.response_due_at', '<', $now);
                });
            })
            ->count();

        $responsePending = (clone $baseQuery)
            ->whereNotNull('tickets.response_due_at')
            ->whereNull('tickets.responded_at')
            ->whereNull('tickets.paused_at')
            ->where('tickets.response_due_at', '>=', $now)
            ->count();

        $resolutionOnTime = (clone $baseQuery)
            ->whereNotNull('tickets.resolution_due_at')
            ->whereNotNull('tickets.completed_at')
            ->whereColumn('tickets.completed_at', '<=', 'tickets.resolution_due_at')
            ->count();

        $resolutionBreached = (clone $baseQuery)
            ->whereNotNull('tickets.resolution_due_at')
            ->where(function (Builder $query) use ($now): void {
                $query->where(function (Builder $completed): void {
                    $completed->whereNotNull('tickets.completed_at')
                        ->whereColumn('tickets.completed_at', '>', 'tickets.resolution_due_at');
                })->orWhere(function (Builder $pending) use ($now): void {
                    $pending->whereNull('tickets.completed_at')
                        ->whereNull('tickets.paused_at')
                        ->where('tickets.resolution_due_at', '<', $now);
                });
            })
            ->count();

        $resolutionPending = (clone $baseQuery)
            ->whereNotNull('tickets.resolution_due_at')
            ->whereNull('tickets.completed_at')
            ->whereNull('tickets.paused_at')
            ->where('tickets.resolution_due_at', '>=', $now)
            ->count();

        $responseMeasured = $responseOnTime + $responseBreached;
        $resolutionMeasured = $resolutionOnTime + $resolutionBreached;

        return [
            'response' => [
                'on_time' => $responseOnTime,
                'breached' => $responseBreached,
                'pending' => $responsePending,
                'compliance_rate' => $this->percentage($responseOnTime, $responseMeasured),
            ],
            'resolution' => [
                'on_time' => $resolutionOnTime,
                'breached' => $resolutionBreached,
                'pending' => $resolutionPending,
                'compliance_rate' => $this->percentage($resolutionOnTime, $resolutionMeasured),
            ],
        ];
    }

    private function inspectionSummary(?User $actor, CarbonImmutable $from, CarbonImmutable $to): array
    {
        $baseQuery = Inspection::query()
            ->whereBetween('inspection_date', [$from->toDateString(), $to->toDateString()]);

        $this->applyInspectionAccessScope($baseQuery, $actor);

        $totalInspections = (clone $baseQuery)->count();
        $submittedInspections = (clone $baseQuery)->where('status', Inspection::STATUS_SUBMITTED)->count();
        $normalInspections = (clone $baseQuery)->where('final_result', Inspection::FINAL_RESULT_NORMAL)->count();
        $abnormalInspections = (clone $baseQuery)->where('final_result', Inspection::FINAL_RESULT_ABNORMAL)->count();

        return [
            'total_inspections' => $totalInspections,
            'submitted_inspections' => $submittedInspections,
            'draft_inspections' => (clone $baseQuery)->where('status', Inspection::STATUS_DRAFT)->count(),
            'in_progress_inspections' => (clone $baseQuery)->where('status', Inspection::STATUS_IN_PROGRESS)->count(),
            'normal_inspections' => $normalInspections,
            'abnormal_inspections' => $abnormalInspections,
            'submission_rate' => $this->percentage($submittedInspections, $totalInspections),
            'normal_rate' => $this->percentage($normalInspections, $submittedInspections),
        ];
    }

    private function ticketDailyTrend(?User $actor, CarbonImmutable $from, CarbonImmutable $to, array $filters = []): array
    {
        $baseQuery = $this->ticketBaseQuery($actor, $from, $to, $filters);

        $createdByDay = (clone $baseQuery)
            ->selectRaw('DATE(tickets.created_at) as day, COUNT(*) as total')
            ->groupBy('day')
            ->pluck('total', 'day');

        $completedByDay = (clone $baseQuery)
            ->whereNotNull('tickets.completed_at')
            ->whereBetween('tickets.completed_at', [$from, $to])
            ->selectRaw('DATE(tickets.completed_at) as day, COUNT(*) as total')
            ->groupBy('day')
            ->pluck('total', 'day');

        $days = [];
        for ($date = $from->startOfDay(); $date->lte($to->startOfDay()); $date = $date->addDay()) {
            $dayKey = $date->toDateString();
            $days[] = [
                'date' => $dayKey,
                'created' => (int) ($createdByDay[$dayKey] ?? 0),
                'completed' => (int) ($completedByDay[$dayKey] ?? 0),
            ];
        }

        return $days;
    }

    private function slaBreachTrend(?User $actor, CarbonImmutable $from, CarbonImmutable $to, array $filters = []): array
    {
        $now = CarbonImmutable::now();
        $baseQuery = $this->ticketBaseQuery($actor, $from, $to, $filters);

        $breachByDay = (clone $baseQuery)
            ->where(function (Builder $query) use ($now): void {
                $query->where(function (Builder $response) use ($now): void {
                    $response->whereNotNull('tickets.response_due_at')
                        ->where(function (Builder $responseCheck) use ($now): void {
                            $responseCheck->where(function (Builder $started): void {
                                $started->whereNotNull('tickets.responded_at')
                                    ->whereColumn('tickets.responded_at', '>', 'tickets.response_due_at');
                            })->orWhere(function (Builder $pending) use ($now): void {
                                $pending->whereNull('tickets.responded_at')
                                    ->whereNull('tickets.paused_at')
                                    ->where('tickets.response_due_at', '<', $now);
                            });
                        });
                })->orWhere(function (Builder $resolution) use ($now): void {
                    $resolution->whereNotNull('tickets.resolution_due_at')
                        ->where(function (Builder $resolutionCheck) use ($now): void {
                            $resolutionCheck->where(function (Builder $completed): void {
                                $completed->whereNotNull('tickets.completed_at')
                                    ->whereColumn('tickets.completed_at', '>', 'tickets.resolution_due_at');
                            })->orWhere(function (Builder $pending) use ($now): void {
                                $pending->whereNull('tickets.completed_at')
                                    ->whereNull('tickets.paused_at')
                                    ->where('tickets.resolution_due_at', '<', $now);
                            });
                        });
                });
            })
            ->selectRaw('DATE(COALESCE(tickets.resolution_due_at, tickets.response_due_at)) as day, COUNT(*) as total')
            ->groupBy('day')
            ->pluck('total', 'day');

        $days = [];
        for ($date = $from->startOfDay(); $date->lte($to->startOfDay()); $date = $date->addDay()) {
            $dayKey = $date->toDateString();
            $days[] = [
                'date' => $dayKey,
                'breached' => (int) ($breachByDay[$dayKey] ?? 0),
            ];
        }

        return $days;
    }

    private function breachTickets(?User $actor, CarbonImmutable $from, CarbonImmutable $to, array $filters = [], int $limit = 10): array
    {
        $now = CarbonImmutable::now();
        $tickets = $this->ticketBaseQuery($actor, $from, $to, $filters)
            ->with([
                'status:id,name,code',
                'priority:id,name,level',
                'assignedEngineer:id,name',
            ])
            ->where(function (Builder $query) use ($now): void {
                $query->where(function (Builder $response) use ($now): void {
                    $response->whereNotNull('tickets.response_due_at')
                        ->where(function (Builder $responseCheck) use ($now): void {
                            $responseCheck->where(function (Builder $started): void {
                                $started->whereNotNull('tickets.responded_at')
                                    ->whereColumn('tickets.responded_at', '>', 'tickets.response_due_at');
                            })->orWhere(function (Builder $pending) use ($now): void {
                                $pending->whereNull('tickets.responded_at')
                                    ->whereNull('tickets.paused_at')
                                    ->where('tickets.response_due_at', '<', $now);
                            });
                        });
                })->orWhere(function (Builder $resolution) use ($now): void {
                    $resolution->whereNotNull('tickets.resolution_due_at')
                        ->where(function (Builder $resolutionCheck) use ($now): void {
                            $resolutionCheck->where(function (Builder $completed): void {
                                $completed->whereNotNull('tickets.completed_at')
                                    ->whereColumn('tickets.completed_at', '>', 'tickets.resolution_due_at');
                            })->orWhere(function (Builder $pending) use ($now): void {
                                $pending->whereNull('tickets.completed_at')
                                    ->whereNull('tickets.paused_at')
                                    ->where('tickets.resolution_due_at', '<', $now);
                            });
                        });
                });
            })
            ->orderByRaw('COALESCE(tickets.resolution_due_at, tickets.response_due_at) ASC')
            ->limit($limit)
            ->get();

        return $tickets->map(function (Ticket $ticket) use ($now): array {
            $responseBreached = $ticket->response_due_at !== null
                && (($ticket->responded_at !== null && $ticket->responded_at->gt($ticket->response_due_at))
                    || ($ticket->responded_at === null && $ticket->paused_at === null && $ticket->response_due_at->lt($now)));

            $resolutionBreached = $ticket->resolution_due_at !== null
                && (($ticket->completed_at !== null && $ticket->completed_at->gt($ticket->resolution_due_at))
                    || ($ticket->completed_at === null && $ticket->paused_at === null && $ticket->resolution_due_at->lt($now)));

            $responseLateMinutes = null;
            if ($responseBreached && $ticket->response_due_at !== null) {
                $actualResponseAt = $ticket->responded_at ?? $now;
                $responseLateMinutes = max(0, $ticket->response_due_at->diffInMinutes($actualResponseAt));
            }

            $resolutionLateMinutes = null;
            if ($resolutionBreached && $ticket->resolution_due_at !== null) {
                $actualResolutionAt = $ticket->completed_at ?? $now;
                $resolutionLateMinutes = max(0, $ticket->resolution_due_at->diffInMinutes($actualResolutionAt));
            }

            return [
                'id' => $ticket->id,
                'ticket_number' => $ticket->ticket_number,
                'title' => $ticket->title,
                'status_name' => $ticket->status?->name,
                'priority_name' => $ticket->priority?->name,
                'assigned_engineer_name' => $ticket->assignedEngineer?->name,
                'response_due_at' => $ticket->response_due_at,
                'resolution_due_at' => $ticket->resolution_due_at,
                'response_breached' => $responseBreached,
                'resolution_breached' => $resolutionBreached,
                'response_late_minutes' => $responseLateMinutes,
                'resolution_late_minutes' => $resolutionLateMinutes,
            ];
        })->all();
    }

    private function engineerStats(
        ?User $actor,
        CarbonImmutable $from,
        CarbonImmutable $to,
        array $filters = [],
        ?int $limit = null,
        ?int $specificEngineerId = null,
    ): Collection {
        $now = CarbonImmutable::now();

        $query = Ticket::query()
            ->from('tickets')
            ->join('users as engineers', 'tickets.assigned_engineer_id', '=', 'engineers.id')
            ->leftJoin('departments as engineer_departments', 'engineers.department_id', '=', 'engineer_departments.id')
            ->whereNotNull('tickets.assigned_engineer_id')
            ->whereBetween('tickets.created_at', [$from, $to]);

        $this->applyTicketAccessScope($query, $actor);
        $this->applyTicketFilters($query, $filters);

        if ($specificEngineerId !== null) {
            $query->where('tickets.assigned_engineer_id', $specificEngineerId);
        }

        $stats = $query
            ->selectRaw(
                'tickets.assigned_engineer_id as engineer_id,
                engineers.name as engineer_name,
                engineer_departments.name as department_name,
                COUNT(*) as assigned_tickets,
                SUM(CASE WHEN tickets.completed_at IS NOT NULL THEN 1 ELSE 0 END) as completed_tickets,
                SUM(CASE WHEN tickets.completed_at IS NULL THEN 1 ELSE 0 END) as open_tickets,
                SUM(CASE WHEN tickets.response_due_at IS NOT NULL AND tickets.responded_at IS NOT NULL AND tickets.responded_at <= tickets.response_due_at THEN 1 ELSE 0 END) as response_on_time_count,
                SUM(CASE WHEN tickets.response_due_at IS NOT NULL AND ((tickets.responded_at IS NOT NULL AND tickets.responded_at > tickets.response_due_at) OR (tickets.responded_at IS NULL AND tickets.paused_at IS NULL AND tickets.response_due_at < ?)) THEN 1 ELSE 0 END) as response_breached_count,
                SUM(CASE WHEN tickets.resolution_due_at IS NOT NULL AND tickets.completed_at IS NOT NULL AND tickets.completed_at <= tickets.resolution_due_at THEN 1 ELSE 0 END) as resolution_on_time_count,
                SUM(CASE WHEN tickets.resolution_due_at IS NOT NULL AND ((tickets.completed_at IS NOT NULL AND tickets.completed_at > tickets.resolution_due_at) OR (tickets.completed_at IS NULL AND tickets.paused_at IS NULL AND tickets.resolution_due_at < ?)) THEN 1 ELSE 0 END) as resolution_breached_count,
                AVG(CASE WHEN tickets.responded_at IS NOT NULL THEN EXTRACT(EPOCH FROM (tickets.responded_at - tickets.created_at)) / 60 END) as avg_response_minutes,
                AVG(CASE WHEN tickets.completed_at IS NOT NULL THEN EXTRACT(EPOCH FROM (tickets.completed_at - tickets.created_at)) / 60 END) as avg_resolution_minutes',
                [$now, $now]
            )
            ->groupBy('tickets.assigned_engineer_id', 'engineers.name', 'engineer_departments.name')
            ->orderByDesc('completed_tickets')
            ->get();

        $worklogQuery = TicketWorklog::query()
            ->join('tickets', 'ticket_worklogs.ticket_id', '=', 'tickets.id')
            ->selectRaw('ticket_worklogs.user_id, COALESCE(SUM(ticket_worklogs.duration_minutes), 0) as total_worklog_minutes')
            ->whereBetween('ticket_worklogs.created_at', [$from, $to]);

        $this->applyTicketFilters($worklogQuery, $filters);

        if ($specificEngineerId !== null) {
            $worklogQuery->where('ticket_worklogs.user_id', $specificEngineerId);
        }

        $worklogByEngineer = $worklogQuery
            ->groupBy('ticket_worklogs.user_id')
            ->pluck('total_worklog_minutes', 'ticket_worklogs.user_id');

        $mapped = $stats->map(function (object $row) use ($worklogByEngineer): array {
            $assignedTickets = (int) $row->assigned_tickets;
            $completedTickets = (int) $row->completed_tickets;
            $responseOnTimeCount = (int) $row->response_on_time_count;
            $responseBreachedCount = (int) $row->response_breached_count;
            $resolutionOnTimeCount = (int) $row->resolution_on_time_count;
            $resolutionBreachedCount = (int) $row->resolution_breached_count;

            $completionRate = $this->percentage($completedTickets, $assignedTickets);
            $responseCompliance = $this->percentage($responseOnTimeCount, $responseOnTimeCount + $responseBreachedCount);
            $resolutionCompliance = $this->percentage($resolutionOnTimeCount, $resolutionOnTimeCount + $resolutionBreachedCount);
            $effectivenessScore = round(($completionRate * 0.5) + ($resolutionCompliance * 0.35) + ($responseCompliance * 0.15), 2);

            return [
                'engineer_id' => (int) $row->engineer_id,
                'engineer_name' => $row->engineer_name,
                'department_name' => $row->department_name,
                'assigned_tickets' => $assignedTickets,
                'completed_tickets' => $completedTickets,
                'open_tickets' => (int) $row->open_tickets,
                'completion_rate' => $completionRate,
                'response_on_time_count' => $responseOnTimeCount,
                'response_breached_count' => $responseBreachedCount,
                'response_compliance_rate' => $responseCompliance,
                'resolution_on_time_count' => $resolutionOnTimeCount,
                'resolution_breached_count' => $resolutionBreachedCount,
                'resolution_compliance_rate' => $resolutionCompliance,
                'avg_response_minutes' => $row->avg_response_minutes !== null ? round((float) $row->avg_response_minutes, 2) : null,
                'avg_resolution_minutes' => $row->avg_resolution_minutes !== null ? round((float) $row->avg_resolution_minutes, 2) : null,
                'total_worklog_minutes' => (int) ($worklogByEngineer[(int) $row->engineer_id] ?? 0),
                'effectiveness_score' => $effectivenessScore,
            ];
        })->sortByDesc('effectiveness_score')->values();

        if ($limit !== null) {
            return $mapped->take($limit)->values();
        }

        return $mapped;
    }

    private function recentTicketsForEngineer(User $engineer, CarbonImmutable $from, CarbonImmutable $to, array $filters = [], int $limit = 8): array
    {
        $query = Ticket::query()
            ->with(['status:id,name,code', 'priority:id,name'])
            ->where('assigned_engineer_id', $engineer->id)
            ->whereBetween('created_at', [$from, $to]);

        $this->applyTicketFilters($query, $filters);

        return $query->orderByDesc('updated_at')
            ->limit($limit)
            ->get()
            ->map(fn (Ticket $ticket): array => [
                'id' => $ticket->id,
                'ticket_number' => $ticket->ticket_number,
                'title' => $ticket->title,
                'status_name' => $ticket->status?->name,
                'priority_name' => $ticket->priority?->name,
                'created_at' => $ticket->created_at,
                'started_at' => $ticket->started_at,
                'responded_at' => $ticket->responded_at,
                'completed_at' => $ticket->completed_at,
            ])
            ->all();
    }

    private function ticketBaseQuery(?User $actor, CarbonImmutable $from, CarbonImmutable $to, array $filters = []): Builder
    {
        $query = Ticket::query()->whereBetween('tickets.created_at', [$from, $to]);

        $this->applyTicketAccessScope($query, $actor);
        $this->applyTicketFilters($query, $filters);

        return $query;
    }

    private function reportStructure(?User $actor, CarbonImmutable $from, CarbonImmutable $to, array $filters = [], int $limit = 10): array
    {
        $baseQuery = $this->ticketBaseQuery($actor, $from, $to, $filters);

        $taxonomyBreakdown = (clone $baseQuery)
            ->leftJoin('ticket_categories', 'tickets.ticket_category_id', '=', 'ticket_categories.id')
            ->leftJoin('ticket_subcategories', 'tickets.ticket_subcategory_id', '=', 'ticket_subcategories.id')
            ->leftJoin('ticket_detail_subcategories', 'tickets.ticket_detail_subcategory_id', '=', 'ticket_detail_subcategories.id')
            ->selectRaw(
                'tickets.ticket_category_id,
                ticket_categories.name as ticket_type_name,
                tickets.ticket_subcategory_id,
                ticket_subcategories.name as ticket_category_name,
                tickets.ticket_detail_subcategory_id,
                ticket_detail_subcategories.name as ticket_sub_category_name,
                COUNT(*) as total_tickets,
                SUM(CASE WHEN tickets.completed_at IS NULL THEN 1 ELSE 0 END) as open_tickets,
                SUM(CASE WHEN tickets.completed_at IS NOT NULL THEN 1 ELSE 0 END) as completed_tickets'
            )
            ->groupBy(
                'tickets.ticket_category_id',
                'ticket_categories.name',
                'tickets.ticket_subcategory_id',
                'ticket_subcategories.name',
                'tickets.ticket_detail_subcategory_id',
                'ticket_detail_subcategories.name'
            )
            ->orderByDesc('total_tickets')
            ->limit($limit)
            ->get()
            ->map(fn (object $row): array => [
                'ticket_type_id' => $row->ticket_category_id ? (int) $row->ticket_category_id : null,
                'ticket_type_name' => $row->ticket_type_name ?? 'Unclassified Type',
                'ticket_category_id' => $row->ticket_subcategory_id ? (int) $row->ticket_subcategory_id : null,
                'ticket_category_name' => $row->ticket_category_name ?? 'Unclassified Category',
                'ticket_sub_category_id' => $row->ticket_detail_subcategory_id ? (int) $row->ticket_detail_subcategory_id : null,
                'ticket_sub_category_name' => $row->ticket_sub_category_name ?? 'Unclassified Sub Category',
                'total_tickets' => (int) $row->total_tickets,
                'open_tickets' => (int) $row->open_tickets,
                'completed_tickets' => (int) $row->completed_tickets,
            ])
            ->all();

        $statusDistribution = (clone $baseQuery)
            ->leftJoin('ticket_statuses', 'tickets.ticket_status_id', '=', 'ticket_statuses.id')
            ->selectRaw('ticket_statuses.name as status_name, COUNT(*) as total_tickets')
            ->groupBy('ticket_statuses.name')
            ->orderByDesc('total_tickets')
            ->get()
            ->map(fn (object $row): array => [
                'status_name' => $row->status_name ?? 'Unassigned Status',
                'total_tickets' => (int) $row->total_tickets,
            ])
            ->all();

        $priorityDistribution = (clone $baseQuery)
            ->leftJoin('ticket_priorities', 'tickets.ticket_priority_id', '=', 'ticket_priorities.id')
            ->selectRaw('ticket_priorities.name as priority_name, COUNT(*) as total_tickets')
            ->groupBy('ticket_priorities.name', 'ticket_priorities.level')
            ->orderBy('ticket_priorities.level')
            ->orderByDesc('total_tickets')
            ->get()
            ->map(fn (object $row): array => [
                'priority_name' => $row->priority_name ?? 'No Priority',
                'total_tickets' => (int) $row->total_tickets,
            ])
            ->all();

        return [
            'taxonomy_breakdown' => $taxonomyBreakdown,
            'status_distribution' => $statusDistribution,
            'priority_distribution' => $priorityDistribution,
            'query_dimensions' => [
                'date_from',
                'date_to',
                'ticket_type',
                'ticket_category',
                'ticket_sub_category',
                'approval_status',
                'expected_approver',
                'expected_approver_role',
                'status',
                'priority',
                'assigned_engineer',
                'department',
                'service',
                'asset',
            ],
            'recommended_grouping' => [
                'ticket_type_name',
                'ticket_category_name',
                'ticket_sub_category_name',
            ],
        ];
    }

    private function applyTicketAccessScope(Builder $query, ?User $actor): void
    {
        if ($actor === null) {
            $query->whereRaw('1 = 0');
            return;
        }

        if ($actor->hasPermission('ticket.view_all')) {
            return;
        }

        $query->where(function (Builder $scopedQuery) use ($actor): void {
            $hasScope = false;

            if ($actor->hasPermission('ticket.view_department') && $actor->department_id !== null) {
                $scopedQuery->orWhere('tickets.requester_department_id', $actor->department_id);
                $hasScope = true;
            }

            if ($actor->hasPermission('ticket.view_assigned')) {
                $scopedQuery->orWhere('tickets.assigned_engineer_id', $actor->id);
                $hasScope = true;
            }

            if ($actor->hasPermission('ticket.view_own')) {
                $scopedQuery->orWhere('tickets.requester_id', $actor->id);
                $hasScope = true;
            }

            if (! $hasScope) {
                $scopedQuery->whereRaw('1 = 0');
            }
        });
    }

    private function applyTicketFilters(Builder $query, array $filters): void
    {
        if (($filters['ticket_category_id'] ?? null) !== null && $filters['ticket_category_id'] !== '') {
            $query->where('tickets.ticket_category_id', $filters['ticket_category_id']);
        }

        if (($filters['ticket_subcategory_id'] ?? null) !== null && $filters['ticket_subcategory_id'] !== '') {
            $query->where('tickets.ticket_subcategory_id', $filters['ticket_subcategory_id']);
        }

        if (($filters['ticket_detail_subcategory_id'] ?? null) !== null && $filters['ticket_detail_subcategory_id'] !== '') {
            $query->where('tickets.ticket_detail_subcategory_id', $filters['ticket_detail_subcategory_id']);
        }

        if (($filters['expected_approver_id'] ?? null) !== null && $filters['expected_approver_id'] !== '') {
            $query->where('tickets.expected_approver_id', $filters['expected_approver_id']);
        }

        if (($filters['expected_approver_role_code'] ?? null) !== null && $filters['expected_approver_role_code'] !== '') {
            $query->where('tickets.expected_approver_role_code', $filters['expected_approver_role_code']);
        }

        if (($filters['approval_status'] ?? null) !== null && $filters['approval_status'] !== '') {
            $query->where('tickets.approval_status', $filters['approval_status']);
        }
    }

    private function applyInspectionAccessScope(Builder $query, ?User $actor): void
    {
        if ($actor === null) {
            return;
        }

        if ($actor->hasPermission('dashboard.view_ops')) {
            return;
        }

        if ($actor->hasAnyPermission(['inspection_task.view_assigned', 'inspection_result.view_assigned'])) {
            $query->where('inspection_officer_id', $actor->id);

            return;
        }

        $query->whereRaw('1 = 0');
    }

    private function resolvePeriod(array $filters): array
    {
        $now = CarbonImmutable::now();

        $from = isset($filters['date_from']) && $filters['date_from'] !== ''
            ? CarbonImmutable::parse($filters['date_from'])->startOfDay()
            : $now->subDays(29)->startOfDay();

        $to = isset($filters['date_to']) && $filters['date_to'] !== ''
            ? CarbonImmutable::parse($filters['date_to'])->endOfDay()
            : $now->endOfDay();

        if ($from->gt($to)) {
            [$from, $to] = [$to->startOfDay(), $from->endOfDay()];
        }

        return [$from, $to];
    }

    private function serializePeriod(CarbonImmutable $from, CarbonImmutable $to): array
    {
        return [
            'date_from' => $from->toDateString(),
            'date_to' => $to->toDateString(),
        ];
    }

    private function percentage(int|float $numerator, int|float $denominator): float
    {
        if ($denominator <= 0) {
            return 0.0;
        }

        return round(($numerator / $denominator) * 100, 2);
    }
}
