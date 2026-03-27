<?php

namespace App\Services\Tickets;

use App\Models\Department;
use App\Models\ServiceCatalog;
use App\Models\TicketCategory;
use App\Models\TicketDetailSubcategory;
use App\Models\TicketSubcategory;

class TicketFlowPolicyResolverService
{
    public function resolve(array $context): array
    {
        $type = $this->resolveType($context['ticket_category_id'] ?? null);
        $category = $this->resolveCategory($context['ticket_subcategory_id'] ?? null);
        $subCategory = $this->resolveSubCategory($context['ticket_detail_subcategory_id'] ?? null);

        $resolved = [
            'requires_approval' => false,
            'allow_direct_assignment' => true,
            'approver_user_id' => null,
            'approver_name' => null,
            'approver_strategy' => TicketCategory::APPROVER_STRATEGY_FALLBACK,
            'approver_role_code' => null,
            'source' => 'system_default',
        ];

        if ($type !== null) {
            $resolved = [
                'requires_approval' => (bool) $type->requires_approval,
                'allow_direct_assignment' => (bool) $type->allow_direct_assignment,
                ...$this->resolveApprover($type, $context),
                'source' => 'ticket_type',
            ];
        }

        if ($category !== null) {
            if ($category->requires_approval !== null) {
                $resolved['requires_approval'] = (bool) $category->requires_approval;
            }

            if ($category->allow_direct_assignment !== null) {
                $resolved['allow_direct_assignment'] = (bool) $category->allow_direct_assignment;
            }

            $resolved['source'] = 'ticket_category';
            $resolved = [...$resolved, ...$this->resolveApprover($category, $context, $resolved)];
        }

        if ($subCategory !== null) {
            if ($subCategory->requires_approval !== null) {
                $resolved['requires_approval'] = (bool) $subCategory->requires_approval;
            }

            if ($subCategory->allow_direct_assignment !== null) {
                $resolved['allow_direct_assignment'] = (bool) $subCategory->allow_direct_assignment;
            }

            $resolved['source'] = 'ticket_sub_category';
            $resolved = [...$resolved, ...$this->resolveApprover($subCategory, $context, $resolved)];
        }

        return $resolved;
    }

    private function resolveType(int|string|null $id): ?TicketCategory
    {
        if ($id === null || $id === '') {
            return null;
        }

        return TicketCategory::query()->with('approver:id,name')->find($id);
    }

    private function resolveCategory(int|string|null $id): ?TicketSubcategory
    {
        if ($id === null || $id === '') {
            return null;
        }

        return TicketSubcategory::query()->with('approver:id,name')->find($id);
    }

    private function resolveSubCategory(int|string|null $id): ?TicketDetailSubcategory
    {
        if ($id === null || $id === '') {
            return null;
        }

        return TicketDetailSubcategory::query()->with('approver:id,name')->find($id);
    }

    private function resolveApprover(object $config, array $context, array $current = []): array
    {
        $strategy = $config->approver_strategy ?: ($config->approver_user_id ? TicketCategory::APPROVER_STRATEGY_SPECIFIC_USER : null);

        if ($strategy === null) {
            return [
                'approver_user_id' => $current['approver_user_id'] ?? null,
                'approver_name' => $current['approver_name'] ?? null,
                'approver_strategy' => $current['approver_strategy'] ?? TicketCategory::APPROVER_STRATEGY_FALLBACK,
                'approver_role_code' => $current['approver_role_code'] ?? null,
            ];
        }

        return match ($strategy) {
            TicketCategory::APPROVER_STRATEGY_SPECIFIC_USER => [
                'approver_user_id' => $config->approver_user_id,
                'approver_name' => $config->approver?->name,
                'approver_strategy' => $strategy,
                'approver_role_code' => null,
            ],
            TicketCategory::APPROVER_STRATEGY_REQUESTER_DEPARTMENT_HEAD => $this->resolveRequesterDepartmentHead($context, $strategy),
            TicketCategory::APPROVER_STRATEGY_SERVICE_MANAGER => $this->resolveServiceManager($context, $strategy),
            TicketCategory::APPROVER_STRATEGY_ROLE_BASED => $this->resolveRoleBased($config->approver_role_code, $strategy),
            default => [
                'approver_user_id' => null,
                'approver_name' => null,
                'approver_strategy' => TicketCategory::APPROVER_STRATEGY_FALLBACK,
                'approver_role_code' => null,
            ],
        };
    }

    private function resolveRequesterDepartmentHead(array $context, string $strategy): array
    {
        $departmentId = $context['requester_department_id'] ?? null;
        $department = $departmentId ? Department::query()->with('head:id,name')->find($departmentId) : null;

        return [
            'approver_user_id' => $department?->head_user_id,
            'approver_name' => $department?->head?->name ?? ($department?->name ? $department->name . ' Head' : null),
            'approver_strategy' => $strategy,
            'approver_role_code' => null,
        ];
    }

    private function resolveServiceManager(array $context, string $strategy): array
    {
        $serviceId = $context['service_id'] ?? $context['service_item_id'] ?? null;
        $service = $serviceId ? ServiceCatalog::query()->with('manager:id,name')->find($serviceId) : null;

        return [
            'approver_user_id' => $service?->service_manager_user_id,
            'approver_name' => $service?->manager?->name ?? ($service?->name ? $service->name . ' Manager' : null),
            'approver_strategy' => $strategy,
            'approver_role_code' => null,
        ];
    }

    private function resolveRoleBased(?string $roleCode, string $strategy): array
    {
        $roleCode = $roleCode ?: 'supervisor';

        return [
            'approver_user_id' => null,
            'approver_name' => str($roleCode)->replace('_', ' ')->title()->toString(),
            'approver_strategy' => $strategy,
            'approver_role_code' => $roleCode,
        ];
    }
}
