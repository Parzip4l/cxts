<?php

namespace App\Services\SLA;

use App\Models\SlaPolicy;
use App\Models\SlaPolicyAssignment;
use App\Models\TicketCategory;
use App\Models\TicketPriority;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class SLAResolverService
{
    private const MATCH_FIELDS = [
        'ticket_type',
        'category_id',
        'subcategory_id',
        'detail_subcategory_id',
        'service_item_id',
        'priority_id',
        'impact',
        'urgency',
    ];

    public function resolveSLA(array $context): ResolvedSLA
    {
        $context = $this->normalizeContext($context);

        return $this->resolveDirectPolicy($context)
            ?? $this->resolveBestAssignment($context)
            ?? $this->resolveDetailSubcategoryDefault($context)
            ?? $this->resolveSubcategoryDefault($context)
            ?? $this->resolveCategoryDefault($context)
            ?? $this->resolveLegacyPriority($context)
            ?? ResolvedSLA::empty();
    }

    private function normalizeContext(array $context): array
    {
        $categoryId = $this->normalizeInteger($context['category_id'] ?? $context['ticket_category_id'] ?? null);

        return [
            'ticket_type' => $this->normalizeString($context['ticket_type'] ?? $this->deriveTicketTypeFromCategory($categoryId)),
            'category_id' => $categoryId,
            'subcategory_id' => $this->normalizeInteger($context['subcategory_id'] ?? $context['ticket_subcategory_id'] ?? null),
            'detail_subcategory_id' => $this->normalizeInteger($context['detail_subcategory_id'] ?? $context['ticket_detail_subcategory_id'] ?? null),
            'service_item_id' => $this->normalizeInteger($context['service_item_id'] ?? $context['service_id'] ?? null),
            'priority_id' => $this->normalizeInteger($context['priority_id'] ?? $context['ticket_priority_id'] ?? null),
            'impact' => $this->normalizeString($context['impact'] ?? null),
            'urgency' => $this->normalizeString($context['urgency'] ?? null),
            'sla_policy_id' => $this->normalizeInteger($context['sla_policy_id'] ?? null),
            'sla_policy_name' => $this->normalizeName($context['sla_policy_name'] ?? null),
        ];
    }

    private function resolveDirectPolicy(array $context): ?ResolvedSLA
    {
        if ($context['sla_policy_id'] !== null) {
            $policy = SlaPolicy::query()
                ->whereKey($context['sla_policy_id'])
                ->where('is_active', true)
                ->first();

            if ($policy !== null) {
                return ResolvedSLA::fromPolicy($policy, 'direct_policy');
            }
        }

        if ($context['sla_policy_name'] === null) {
            return null;
        }

        $policy = SlaPolicy::query()
            ->where('name', $context['sla_policy_name'])
            ->where('is_active', true)
            ->first();

        return $policy !== null
            ? ResolvedSLA::fromPolicy($policy, 'direct_policy_name')
            : null;
    }

    private function resolveBestAssignment(array $context): ?ResolvedSLA
    {
        $assignment = $this->assignmentBaseQuery($context)
            ->with('policy:id,name,response_time_minutes,resolution_time_minutes,is_active')
            ->get()
            ->sort(function (SlaPolicyAssignment $left, SlaPolicyAssignment $right): int {
                $scoreComparison = $this->matchScore($right) <=> $this->matchScore($left);
                if ($scoreComparison !== 0) {
                    return $scoreComparison;
                }

                $sortOrderComparison = $left->sort_order <=> $right->sort_order;
                if ($sortOrderComparison !== 0) {
                    return $sortOrderComparison;
                }

                return $left->id <=> $right->id;
            })
            ->first();

        return $assignment?->policy !== null
            ? ResolvedSLA::fromPolicy($assignment->policy, 'policy_assignment')
            : null;
    }

    private function resolveSubcategoryDefault(array $context): ?ResolvedSLA
    {
        if ($context['subcategory_id'] === null) {
            return null;
        }

        $assignment = SlaPolicyAssignment::query()
            ->with('policy:id,name,response_time_minutes,resolution_time_minutes,is_active')
            ->where('is_active', true)
            ->whereHas('policy', fn ($query) => $query->where('is_active', true))
            ->where('subcategory_id', $context['subcategory_id'])
            ->whereNull('detail_subcategory_id')
            ->whereNull('ticket_type')
            ->whereNull('category_id')
            ->whereNull('service_item_id')
            ->whereNull('priority_id')
            ->whereNull('impact')
            ->whereNull('urgency')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->first();

        return $assignment?->policy !== null
            ? ResolvedSLA::fromPolicy($assignment->policy, 'subcategory_default')
            : null;
    }

    private function resolveCategoryDefault(array $context): ?ResolvedSLA
    {
        if ($context['category_id'] === null) {
            return null;
        }

        $assignment = SlaPolicyAssignment::query()
            ->with('policy:id,name,response_time_minutes,resolution_time_minutes,is_active')
            ->where('is_active', true)
            ->whereHas('policy', fn ($query) => $query->where('is_active', true))
            ->where('category_id', $context['category_id'])
            ->whereNull('ticket_type')
            ->whereNull('subcategory_id')
            ->whereNull('detail_subcategory_id')
            ->whereNull('service_item_id')
            ->whereNull('priority_id')
            ->whereNull('impact')
            ->whereNull('urgency')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->first();

        return $assignment?->policy !== null
            ? ResolvedSLA::fromPolicy($assignment->policy, 'category_default')
            : null;
    }

    private function resolveLegacyPriority(array $context): ?ResolvedSLA
    {
        if ($context['priority_id'] === null) {
            return null;
        }

        $priority = TicketPriority::query()->find($context['priority_id']);

        return $priority !== null
            ? ResolvedSLA::fromLegacyPriority($priority)
            : null;
    }

    private function resolveDetailSubcategoryDefault(array $context): ?ResolvedSLA
    {
        if ($context['detail_subcategory_id'] === null) {
            return null;
        }

        $assignment = SlaPolicyAssignment::query()
            ->with('policy:id,name,response_time_minutes,resolution_time_minutes,is_active')
            ->where('is_active', true)
            ->whereHas('policy', fn ($query) => $query->where('is_active', true))
            ->where('detail_subcategory_id', $context['detail_subcategory_id'])
            ->whereNull('ticket_type')
            ->whereNull('category_id')
            ->whereNull('subcategory_id')
            ->whereNull('service_item_id')
            ->whereNull('priority_id')
            ->whereNull('impact')
            ->whereNull('urgency')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->first();

        return $assignment?->policy !== null
            ? ResolvedSLA::fromPolicy($assignment->policy, 'detail_subcategory_default')
            : null;
    }

    private function assignmentBaseQuery(array $context)
    {
        $query = SlaPolicyAssignment::query()
            ->where('is_active', true)
            ->whereHas('policy', fn ($policyQuery) => $policyQuery->where('is_active', true));

        foreach (self::MATCH_FIELDS as $field) {
            $value = $context[$field] ?? null;

            if ($value === null) {
                $query->whereNull($field);
                continue;
            }

            $query->where(function ($subQuery) use ($field, $value): void {
                $subQuery->whereNull($field)
                    ->orWhere($field, $value);
            });
        }

        return $query;
    }

    private function matchScore(SlaPolicyAssignment $assignment): int
    {
        return Collection::make(self::MATCH_FIELDS)
            ->filter(fn (string $field): bool => $assignment->{$field} !== null)
            ->count();
    }

    private function deriveTicketTypeFromCategory(?int $categoryId): ?string
    {
        if ($categoryId === null) {
            return null;
        }

        $code = TicketCategory::query()->whereKey($categoryId)->value('code');
        if (! is_string($code) || $code === '') {
            return null;
        }

        return match (strtoupper($code)) {
            'INCIDENT' => 'incident',
            'REQUEST' => 'service_request',
            'MAINTENANCE' => 'change_request',
            default => Str::of($code)->lower()->snake()->toString(),
        };
    }

    private function normalizeInteger(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (int) $value;
    }

    private function normalizeString(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $value = Str::of($value)->trim()->lower()->toString();

        return $value !== '' ? $value : null;
    }

    private function normalizeName(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $value = trim($value);

        return $value !== '' ? $value : null;
    }
}
