<?php

namespace App\Modules\Tickets\SlaPolicies;

use App\Models\SlaPolicy;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

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

    public function create(array $data): SlaPolicy
    {
        return SlaPolicy::query()->create($this->preparePayload($data));
    }

    public function update(SlaPolicy $slaPolicy, array $data): SlaPolicy
    {
        $slaPolicy->update($this->preparePayload($data));

        return $slaPolicy->fresh();
    }

    public function delete(SlaPolicy $slaPolicy): void
    {
        $slaPolicy->delete();
    }

    private function preparePayload(array $data): array
    {
        if (array_key_exists('is_active', $data)) {
            $data['is_active'] = (bool) $data['is_active'];
        }

        if (($data['working_hours_id'] ?? null) === '') {
            $data['working_hours_id'] = null;
        }

        return $data;
    }
}
