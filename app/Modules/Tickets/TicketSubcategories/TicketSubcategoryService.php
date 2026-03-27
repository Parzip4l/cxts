<?php

namespace App\Modules\Tickets\TicketSubcategories;

use App\Models\TicketSubcategory;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class TicketSubcategoryService
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return TicketSubcategory::query()
            ->with(['category:id,name', 'approver:id,name'])
            ->when($filters['search'] ?? null, function ($query, $search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('code', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%");
                });
            })
            ->when($filters['ticket_category_id'] ?? null, fn ($query, $ticketCategoryId) => $query->where('ticket_category_id', $ticketCategoryId))
            ->when(array_key_exists('is_active', $filters), fn ($query) => $query->where('is_active', (bool) $filters['is_active']))
            ->orderBy('name')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function create(array $data): TicketSubcategory
    {
        $skillIds = $this->extractSkillIds($data);
        $ticketSubcategory = TicketSubcategory::query()->create($this->preparePayload($data));
        $ticketSubcategory->engineerSkills()->sync($skillIds);

        return $ticketSubcategory->fresh(['category:id,name', 'engineerSkills:id,name', 'approver:id,name']);
    }

    public function update(TicketSubcategory $ticketSubcategory, array $data): TicketSubcategory
    {
        $skillIds = $this->extractSkillIds($data);
        $ticketSubcategory->update($this->preparePayload($data));
        $ticketSubcategory->engineerSkills()->sync($skillIds);

        return $ticketSubcategory->fresh(['category:id,name', 'engineerSkills:id,name', 'approver:id,name']);
    }

    public function delete(TicketSubcategory $ticketSubcategory): void
    {
        $ticketSubcategory->delete();
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
