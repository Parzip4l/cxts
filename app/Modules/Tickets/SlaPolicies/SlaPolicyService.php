<?php

namespace App\Modules\Tickets\SlaPolicies;

use App\Models\SlaPolicy;
use App\Models\SlaPolicyAuditLog;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class SlaPolicyService
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return SlaPolicy::query()
            ->when($filters['search'] ?? null, function ($query, string $search): void {
                $query->where(function ($subQuery) use ($search): void {
                    $subQuery->where('name', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->when(array_key_exists('is_active', $filters), fn ($query) => $query->where('is_active', (bool) $filters['is_active']))
            ->orderBy('name')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function create(array $data, ?User $actor = null): SlaPolicy
    {
        return DB::transaction(function () use ($data, $actor): SlaPolicy {
            $slaPolicy = SlaPolicy::query()->create($this->preparePayload($data));

            $this->recordAudit($slaPolicy, SlaPolicyAuditLog::ACTION_CREATED, null, $this->snapshot($slaPolicy), $actor);

            return $slaPolicy;
        });
    }

    public function update(SlaPolicy $slaPolicy, array $data, ?User $actor = null): SlaPolicy
    {
        return DB::transaction(function () use ($slaPolicy, $data, $actor): SlaPolicy {
            $before = $this->snapshot($slaPolicy);
            $slaPolicy->update($this->preparePayload($data));
            $slaPolicy->refresh();

            $this->recordAudit($slaPolicy, SlaPolicyAuditLog::ACTION_UPDATED, $before, $this->snapshot($slaPolicy), $actor);

            return $slaPolicy;
        });
    }

    public function delete(SlaPolicy $slaPolicy, ?User $actor = null): void
    {
        DB::transaction(function () use ($slaPolicy, $actor): void {
            $before = $this->snapshot($slaPolicy);
            SlaPolicyAuditLog::query()->create([
                'sla_policy_id' => $slaPolicy->id,
                'action' => SlaPolicyAuditLog::ACTION_DELETED,
                'before_snapshot' => $before,
                'after_snapshot' => null,
                'actor_user_id' => $actor?->id,
            ]);
            $slaPolicy->delete();
        });
    }

    private function preparePayload(array $data): array
    {
        if (array_key_exists('is_active', $data)) {
            $data['is_active'] = (bool) $data['is_active'];
        }

        foreach (['escalate_on_warning', 'escalate_on_breach'] as $booleanKey) {
            if (array_key_exists($booleanKey, $data)) {
                $data[$booleanKey] = (bool) $data[$booleanKey];
            }
        }

        if (($data['working_hours_id'] ?? null) === '') {
            $data['working_hours_id'] = null;
        }

        if (($data['escalation_role_code'] ?? null) === '') {
            $data['escalation_role_code'] = null;
        }

        return $data;
    }

    private function snapshot(SlaPolicy $slaPolicy): array
    {
        return [
            'name' => $slaPolicy->name,
            'description' => $slaPolicy->description,
            'response_time_minutes' => $slaPolicy->response_time_minutes,
            'resolution_time_minutes' => $slaPolicy->resolution_time_minutes,
            'working_hours_id' => $slaPolicy->working_hours_id,
            'escalate_on_warning' => (bool) $slaPolicy->escalate_on_warning,
            'escalate_on_breach' => (bool) $slaPolicy->escalate_on_breach,
            'escalation_role_code' => $slaPolicy->escalation_role_code,
            'escalation_note' => $slaPolicy->escalation_note,
            'is_active' => (bool) $slaPolicy->is_active,
        ];
    }

    private function recordAudit(SlaPolicy $slaPolicy, string $action, ?array $before, ?array $after, ?User $actor): void
    {
        SlaPolicyAuditLog::query()->create([
            'sla_policy_id' => $slaPolicy->id,
            'action' => $action,
            'before_snapshot' => $before,
            'after_snapshot' => $after,
            'actor_user_id' => $actor?->id,
        ]);
    }
}
