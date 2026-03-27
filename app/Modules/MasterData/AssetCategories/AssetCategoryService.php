<?php

namespace App\Modules\MasterData\AssetCategories;

use App\Models\AssetCategory;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class AssetCategoryService
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return AssetCategory::query()
            ->when($filters['search'] ?? null, function ($query, $search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('code', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%");
                });
            })
            ->when(array_key_exists('is_active', $filters), fn ($query) => $query->where('is_active', (bool) $filters['is_active']))
            ->orderBy('name')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function create(array $data): AssetCategory
    {
        $skillIds = $this->extractSkillIds($data);
        $assetCategory = AssetCategory::create($this->preparePayload($data));
        $assetCategory->engineerSkills()->sync($skillIds);

        return $assetCategory->fresh(['engineerSkills:id,name']);
    }

    public function update(AssetCategory $assetCategory, array $data): AssetCategory
    {
        $skillIds = $this->extractSkillIds($data);
        $assetCategory->update($this->preparePayload($data));
        $assetCategory->engineerSkills()->sync($skillIds);

        return $assetCategory->fresh(['engineerSkills:id,name']);
    }

    public function delete(AssetCategory $assetCategory): void
    {
        $assetCategory->delete();
    }

    private function preparePayload(array $data): array
    {
        if (array_key_exists('is_active', $data)) {
            $data['is_active'] = (bool) $data['is_active'];
        }

        unset($data['engineer_skill_ids']);

        return $data;
    }

    private function extractSkillIds(array $data): array
    {
        return collect($data['engineer_skill_ids'] ?? [])
            ->filter(fn ($value) => $value !== null && $value !== '')
            ->map(fn ($value) => (int) $value)
            ->unique()
            ->values()
            ->all();
    }
}
