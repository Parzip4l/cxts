<?php

namespace App\Modules\Tickets\SlaPolicyAssignments;

use App\Models\SlaPolicyAssignment;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class SlaPolicyAssignmentService
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return SlaPolicyAssignment::query()
            ->with([
                'policy:id,name',
                'category:id,name',
                'subcategory:id,name,ticket_category_id',
                'detailSubcategory:id,name,ticket_subcategory_id',
                'serviceItem:id,name',
                'priority:id,name,code',
            ])
            ->when($filters['search'] ?? null, function ($query, string $search): void {
                $query->where(function ($subQuery) use ($search): void {
                    $subQuery->where('ticket_type', 'like', "%{$search}%")
                        ->orWhere('impact', 'like', "%{$search}%")
                        ->orWhere('urgency', 'like', "%{$search}%")
                        ->orWhereHas('policy', fn ($policyQuery) => $policyQuery->where('name', 'like', "%{$search}%"));
                });
            })
            ->when($filters['sla_policy_id'] ?? null, fn ($query, $policyId) => $query->where('sla_policy_id', $policyId))
            ->when(array_key_exists('is_active', $filters), fn ($query) => $query->where('is_active', (bool) $filters['is_active']))
            ->orderBy('sort_order')
            ->orderBy('id')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function create(array $data): SlaPolicyAssignment
    {
        return SlaPolicyAssignment::query()->create($this->preparePayload($data));
    }

    public function update(SlaPolicyAssignment $slaPolicyAssignment, array $data): SlaPolicyAssignment
    {
        $slaPolicyAssignment->update($this->preparePayload($data));

        return $slaPolicyAssignment->fresh([
            'policy:id,name',
            'category:id,name',
            'subcategory:id,name,ticket_category_id',
            'detailSubcategory:id,name,ticket_subcategory_id',
            'serviceItem:id,name',
            'priority:id,name,code',
        ]);
    }

    public function delete(SlaPolicyAssignment $slaPolicyAssignment): void
    {
        $slaPolicyAssignment->delete();
    }

    private function preparePayload(array $data): array
    {
        foreach (['ticket_type', 'category_id', 'subcategory_id', 'detail_subcategory_id', 'service_item_id', 'priority_id', 'impact', 'urgency'] as $field) {
            if (($data[$field] ?? null) === '') {
                $data[$field] = null;
            }
        }

        if (array_key_exists('is_active', $data)) {
            $data['is_active'] = (bool) $data['is_active'];
        }

        $data['sort_order'] = (int) ($data['sort_order'] ?? 100);

        return $data;
    }
}
