<?php

namespace App\Modules\Tickets\TicketDetailSubcategories;

use App\Models\TicketDetailSubcategory;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class TicketDetailSubcategoryService
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return TicketDetailSubcategory::query()
            ->with(['category:id,name,ticket_category_id', 'category.category:id,name', 'approver:id,name'])
            ->when($filters['search'] ?? null, function ($query, $search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('code', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%");
                });
            })
            ->when($filters['ticket_subcategory_id'] ?? null, fn ($query, $subcategoryId) => $query->where('ticket_subcategory_id', $subcategoryId))
            ->when(array_key_exists('is_active', $filters), fn ($query) => $query->where('is_active', (bool) $filters['is_active']))
            ->orderBy('name')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function create(array $data): TicketDetailSubcategory
    {
        $ticketDetailSubcategory = TicketDetailSubcategory::query()->create($this->preparePayload($data));
        $ticketDetailSubcategory->engineerSkills()->sync($data['engineer_skill_ids'] ?? []);

        return $ticketDetailSubcategory->fresh(['category:id,name,ticket_category_id', 'category.category:id,name', 'engineerSkills:id,name', 'approver:id,name']);
    }

    public function update(TicketDetailSubcategory $ticketDetailSubcategory, array $data): TicketDetailSubcategory
    {
        $ticketDetailSubcategory->update($this->preparePayload($data));
        $ticketDetailSubcategory->engineerSkills()->sync($data['engineer_skill_ids'] ?? []);

        return $ticketDetailSubcategory->fresh(['category:id,name,ticket_category_id', 'category.category:id,name', 'engineerSkills:id,name', 'approver:id,name']);
    }

    public function delete(TicketDetailSubcategory $ticketDetailSubcategory): void
    {
        $ticketDetailSubcategory->delete();
    }

    private function preparePayload(array $data): array
    {
        if (array_key_exists('requires_approval', $data)) {
            $data['requires_approval'] = ($data['requires_approval'] === null || $data['requires_approval'] === '')
                ? null
                : (bool) $data['requires_approval'];
        }

        if (array_key_exists('allow_direct_assignment', $data)) {
            $data['allow_direct_assignment'] = ($data['allow_direct_assignment'] === null || $data['allow_direct_assignment'] === '')
                ? null
                : (bool) $data['allow_direct_assignment'];
        }

        if (array_key_exists('approver_user_id', $data) && ($data['approver_user_id'] === '' || $data['approver_user_id'] === null)) {
            $data['approver_user_id'] = null;
        }

        if (array_key_exists('approver_strategy', $data) && ($data['approver_strategy'] === '' || $data['approver_strategy'] === null)) {
            $data['approver_strategy'] = null;
        }

        if (array_key_exists('approver_role_code', $data) && ($data['approver_role_code'] === '' || $data['approver_role_code'] === null)) {
            $data['approver_role_code'] = null;
        }

        if (array_key_exists('is_active', $data)) {
            $data['is_active'] = (bool) $data['is_active'];
        }

        unset($data['engineer_skill_ids']);

        return $data;
    }
}
