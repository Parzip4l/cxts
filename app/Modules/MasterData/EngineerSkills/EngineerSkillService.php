<?php

namespace App\Modules\MasterData\EngineerSkills;

use App\Models\EngineerSkill;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EngineerSkillService
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return EngineerSkill::query()
            ->when($filters['search'] ?? null, function ($query, $search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('code', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->when(array_key_exists('is_active', $filters), fn ($query) => $query->where('is_active', (bool) $filters['is_active']))
            ->orderBy('name')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function create(array $data): EngineerSkill
    {
        return EngineerSkill::query()->create($this->preparePayload($data));
    }

    public function update(EngineerSkill $engineerSkill, array $data): EngineerSkill
    {
        $engineerSkill->update($this->preparePayload($data));

        return $engineerSkill->fresh();
    }

    public function delete(EngineerSkill $engineerSkill): void
    {
        $engineerSkill->delete();
    }

    private function preparePayload(array $data): array
    {
        if (array_key_exists('is_active', $data)) {
            $data['is_active'] = (bool) $data['is_active'];
        }

        return $data;
    }
}
