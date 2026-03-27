<?php

namespace App\Modules\Inspections\InspectionTemplates;

use App\Models\InspectionTemplate;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class InspectionTemplateService
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return InspectionTemplate::query()
            ->with(['assetCategory:id,name'])
            ->when($filters['search'] ?? null, function ($query, $search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('code', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%");
                });
            })
            ->when($filters['asset_category_id'] ?? null, fn ($query, $assetCategoryId) => $query->where('asset_category_id', $assetCategoryId))
            ->when(array_key_exists('is_active', $filters), fn ($query) => $query->where('is_active', (bool) $filters['is_active']))
            ->orderBy('name')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function create(array $data, ?User $actor = null): InspectionTemplate
    {
        return DB::transaction(function () use ($data, $actor): InspectionTemplate {
            $template = InspectionTemplate::query()->create([
                'code' => $data['code'],
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'asset_category_id' => $data['asset_category_id'] ?? null,
                'is_active' => (bool) ($data['is_active'] ?? true),
                'created_by_id' => $actor?->id,
                'updated_by_id' => $actor?->id,
            ]);

            $this->syncItems($template, $data['items'] ?? []);

            return $template->fresh(['assetCategory:id,name', 'items']);
        });
    }

    public function update(InspectionTemplate $template, array $data, ?User $actor = null): InspectionTemplate
    {
        return DB::transaction(function () use ($template, $data, $actor): InspectionTemplate {
            $template->update([
                'code' => $data['code'],
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'asset_category_id' => $data['asset_category_id'] ?? null,
                'is_active' => (bool) ($data['is_active'] ?? true),
                'updated_by_id' => $actor?->id,
            ]);

            $this->syncItems($template, $data['items'] ?? []);

            return $template->fresh(['assetCategory:id,name', 'items']);
        });
    }

    public function delete(InspectionTemplate $template): void
    {
        $template->delete();
    }

    private function syncItems(InspectionTemplate $template, array $items): void
    {
        $template->items()->delete();

        foreach ($items as $index => $item) {
            $template->items()->create([
                'sequence' => (int) ($item['sequence'] ?? ($index + 1)),
                'item_label' => $item['item_label'],
                'item_type' => $item['item_type'] ?? 'boolean',
                'expected_value' => $item['expected_value'] ?? null,
                'is_required' => (bool) ($item['is_required'] ?? true),
                'is_active' => (bool) ($item['is_active'] ?? true),
            ]);
        }
    }
}
