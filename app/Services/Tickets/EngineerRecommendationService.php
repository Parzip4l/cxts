<?php

namespace App\Services\Tickets;

use App\Models\EngineerSchedule;
use App\Models\Ticket;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class EngineerRecommendationService
{
    public function recommendForTicket(Ticket $ticket): array
    {
        $ticket->loadMissing([
            'service.engineerSkills:id,name',
            'subcategory.engineerSkills:id,name',
            'detailSubcategory.engineerSkills:id,name',
            'asset.category.engineerSkills:id,name',
            'assignedEngineer.engineerSkills:id,name',
        ]);

        $requiredSkills = collect()
            ->merge($ticket->detailSubcategory?->engineerSkills?->map(fn ($skill) => [
                'id' => (int) $skill->id,
                'name' => $skill->name,
                'source' => 'ticket_sub_category',
            ]) ?? [])
            ->merge($ticket->subcategory?->engineerSkills?->map(fn ($skill) => [
                'id' => (int) $skill->id,
                'name' => $skill->name,
                'source' => 'ticket_category',
            ]) ?? [])
            ->merge($ticket->service?->engineerSkills?->map(fn ($skill) => [
                'id' => (int) $skill->id,
                'name' => $skill->name,
                'source' => 'service',
            ]) ?? [])
            ->merge($ticket->asset?->category?->engineerSkills?->map(fn ($skill) => [
                'id' => (int) $skill->id,
                'name' => $skill->name,
                'source' => 'asset_category',
            ]) ?? [])
            ->unique('id')
            ->values();

        $requiredSkillIds = $requiredSkills->pluck('id')->all();
        $today = CarbonImmutable::now()->toDateString();

        $engineers = User::query()
            ->where('role', 'engineer')
            ->with(['department:id,name', 'engineerSkills:id,name'])
            ->orderBy('name')
            ->get(['id', 'name', 'department_id']);

        $todaySchedules = EngineerSchedule::query()
            ->with(['shift:id,name,start_time,end_time'])
            ->whereDate('work_date', $today)
            ->whereIn('user_id', $engineers->pluck('id')->all())
            ->get()
            ->keyBy('user_id');

        $workloadByEngineer = Ticket::query()
            ->selectRaw('assigned_engineer_id, COUNT(*) as open_ticket_count')
            ->whereNotNull('assigned_engineer_id')
            ->whereKeyNot($ticket->id)
            ->whereNull('closed_at')
            ->whereNull('completed_at')
            ->groupBy('assigned_engineer_id')
            ->pluck('open_ticket_count', 'assigned_engineer_id');

        $engineersWithMeta = $this->appendRecommendationMeta(
            $engineers,
            $requiredSkillIds,
            $todaySchedules,
            $workloadByEngineer,
            $requiredSkillIds !== []
        );

        if ($requiredSkillIds === []) {
            return [
                'required_skills' => [],
                'required_skill_labels' => [],
                'recommended_engineers' => collect(),
                'fallback_engineers' => $this->sortEngineers($engineersWithMeta),
                'has_recommendation' => false,
            ];
        }

        $recommended = $this->sortEngineers(
            $engineersWithMeta
            ->filter(fn (User $engineer) => $engineer->match_score > 0 && $engineer->availability_status !== 'unavailable')
        );

        $fallback = $this->sortEngineers(
            $engineersWithMeta
            ->reject(fn (User $engineer) => $recommended->contains('id', $engineer->id))
        );

        $assignedEngineer = $ticket->assignedEngineer;
        if ($assignedEngineer !== null && ! $recommended->contains('id', $assignedEngineer->id)) {
            $assignedWithMeta = $engineersWithMeta->firstWhere('id', $assignedEngineer->id);
            if ($assignedWithMeta !== null) {
                $recommended = collect([$assignedWithMeta])->merge($recommended)->unique('id')->values();
                $fallback = $fallback->reject(fn ($engineer) => (int) $engineer->id === (int) $assignedEngineer->id)->values();
            }
        }

        return [
            'required_skills' => $requiredSkills->all(),
            'required_skill_labels' => $requiredSkills->pluck('name')->all(),
            'recommended_engineers' => $recommended,
            'fallback_engineers' => $fallback,
            'has_recommendation' => $recommended->isNotEmpty(),
        ];
    }

    public function serializeRecommendation(array $recommendation): array
    {
        return [
            'required_skills' => Arr::get($recommendation, 'required_skills', []),
            'required_skill_labels' => Arr::get($recommendation, 'required_skill_labels', []),
            'has_recommendation' => (bool) Arr::get($recommendation, 'has_recommendation', false),
            'scoring_basis' => [
                'skill_match' => '0-60',
                'availability_schedule' => '0-25',
                'workload' => '0-20',
            ],
            'recommended_engineers' => $this->serializeEngineerCollection(Arr::get($recommendation, 'recommended_engineers', collect())),
            'fallback_engineers' => $this->serializeEngineerCollection(Arr::get($recommendation, 'fallback_engineers', collect())),
        ];
    }

    private function appendRecommendationMeta(
        Collection $engineers,
        array $requiredSkillIds,
        Collection $todaySchedules,
        Collection $workloadByEngineer,
        bool $useSkillScoring
    ): Collection {
        return $engineers->map(function (User $engineer) use ($requiredSkillIds, $todaySchedules, $workloadByEngineer, $useSkillScoring) {
            $matchedSkills = $engineer->engineerSkills
                ->filter(fn ($skill) => in_array((int) $skill->id, $requiredSkillIds, true))
                ->pluck('name')
                ->values();

            $schedule = $todaySchedules->get($engineer->id);
            $workloadCount = (int) ($workloadByEngineer->get($engineer->id) ?? 0);
            $availability = $this->availabilityMeta($schedule);
            $workload = $this->workloadMeta($workloadCount);

            $skillScore = $useSkillScoring && count($requiredSkillIds) > 0
                ? (int) round(($matchedSkills->count() / count($requiredSkillIds)) * 60)
                : 0;
            $workloadScore = $workload['score'];
            $recommendationScore = $skillScore + $availability['score'] + $workloadScore;

            $engineer->setAttribute('matched_skill_names', $matchedSkills->all());
            $engineer->setAttribute('match_score', $matchedSkills->count());
            $engineer->setAttribute('skill_score', $skillScore);
            $engineer->setAttribute('department_name', $engineer->department?->name);
            $engineer->setAttribute('availability_score', $availability['score']);
            $engineer->setAttribute('availability_status', $availability['status']);
            $engineer->setAttribute('availability_label', $availability['label']);
            $engineer->setAttribute('availability_reason', $availability['reason']);
            $engineer->setAttribute('workload_open_tickets', $workloadCount);
            $engineer->setAttribute('workload_status', $workload['status']);
            $engineer->setAttribute('workload_label', $workload['label']);
            $engineer->setAttribute('workload_score', $workloadScore);
            $engineer->setAttribute('recommendation_score', $recommendationScore);
            $engineer->setAttribute('today_schedule_status', $schedule?->status);
            $engineer->setAttribute('today_shift_name', $schedule?->shift?->name);
            $engineer->setAttribute('team_label', $schedule?->shift?->name ?? $engineer->department?->name);

            return $engineer;
        });
    }

    private function availabilityMeta(?EngineerSchedule $schedule): array
    {
        if ($schedule === null) {
            return [
                'status' => 'unknown',
                'label' => 'No Schedule',
                'score' => 10,
                'reason' => 'No shift assigned for today.',
            ];
        }

        return match ($schedule->status) {
            EngineerSchedule::STATUS_ASSIGNED => [
                'status' => 'available',
                'label' => 'On Schedule',
                'score' => 25,
                'reason' => $schedule->shift?->name
                    ? 'Scheduled on '.$schedule->shift->name
                    : 'Scheduled for today.',
            ],
            EngineerSchedule::STATUS_OFF => [
                'status' => 'unavailable',
                'label' => 'Off',
                'score' => 0,
                'reason' => 'Engineer is marked off today.',
            ],
            EngineerSchedule::STATUS_LEAVE => [
                'status' => 'unavailable',
                'label' => 'Leave',
                'score' => 0,
                'reason' => 'Engineer is on leave today.',
            ],
            EngineerSchedule::STATUS_SICK => [
                'status' => 'unavailable',
                'label' => 'Sick',
                'score' => 0,
                'reason' => 'Engineer is marked sick today.',
            ],
            default => [
                'status' => 'unknown',
                'label' => ucfirst((string) $schedule->status),
                'score' => 10,
                'reason' => 'Schedule is set, but availability needs manual review.',
            ],
        };
    }

    private function workloadMeta(int $workloadCount): array
    {
        if ($workloadCount >= 5) {
            return [
                'status' => 'busy',
                'label' => 'Busy',
                'score' => 0,
            ];
        }

        if ($workloadCount >= 3) {
            return [
                'status' => 'moderate',
                'label' => 'Moderate',
                'score' => 8,
            ];
        }

        return [
            'status' => 'light',
            'label' => 'Light',
            'score' => 20,
        ];
    }

    private function sortEngineers(Collection $engineers): Collection
    {
        return $engineers
            ->sort(function (User $left, User $right) {
                $scoreCompare = (($right->recommendation_score ?? 0) <=> ($left->recommendation_score ?? 0));
                if ($scoreCompare !== 0) {
                    return $scoreCompare;
                }

                $availabilityCompare = (($right->availability_score ?? 0) <=> ($left->availability_score ?? 0));
                if ($availabilityCompare !== 0) {
                    return $availabilityCompare;
                }

                $workloadCompare = (($left->workload_open_tickets ?? 0) <=> ($right->workload_open_tickets ?? 0));
                if ($workloadCompare !== 0) {
                    return $workloadCompare;
                }

                return strcasecmp((string) $left->name, (string) $right->name);
            })
            ->values();
    }

    private function serializeEngineerCollection($engineers): array
    {
        return collect($engineers)->map(function ($engineer) {
            return [
                'id' => (int) $engineer->id,
                'name' => $engineer->name,
                'department_id' => (int) ($engineer->department_id ?? 0),
                'department_name' => $engineer->department_name,
                'team_label' => $engineer->team_label,
                'matched_skill_names' => $engineer->matched_skill_names ?? [],
                'match_score' => (int) ($engineer->match_score ?? 0),
                'skill_score' => (int) ($engineer->skill_score ?? 0),
                'availability_status' => $engineer->availability_status ?? 'unknown',
                'availability_label' => $engineer->availability_label ?? 'Unknown',
                'availability_reason' => $engineer->availability_reason,
                'availability_score' => (int) ($engineer->availability_score ?? 0),
                'today_schedule_status' => $engineer->today_schedule_status,
                'today_shift_name' => $engineer->today_shift_name,
                'workload_open_tickets' => (int) ($engineer->workload_open_tickets ?? 0),
                'workload_status' => $engineer->workload_status ?? 'light',
                'workload_label' => $engineer->workload_label ?? 'Light',
                'workload_score' => (int) ($engineer->workload_score ?? 0),
                'recommendation_score' => (int) ($engineer->recommendation_score ?? 0),
            ];
        })->values()->all();
    }
}
