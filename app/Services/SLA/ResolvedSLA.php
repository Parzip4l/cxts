<?php

namespace App\Services\SLA;

use App\Models\SlaPolicy;
use App\Models\TicketPriority;
use Carbon\CarbonImmutable;

final class ResolvedSLA
{
    public function __construct(
        public readonly ?int $policyId,
        public readonly ?string $name,
        public readonly ?int $responseTimeMinutes,
        public readonly ?int $resolutionTimeMinutes,
        public readonly string $source,
    ) {
    }

    public static function fromPolicy(SlaPolicy $policy, string $source = 'policy_assignment'): self
    {
        return new self(
            policyId: $policy->id,
            name: $policy->name,
            responseTimeMinutes: $policy->response_time_minutes,
            resolutionTimeMinutes: $policy->resolution_time_minutes,
            source: $source,
        );
    }

    public static function fromLegacyPriority(?TicketPriority $priority): self
    {
        return new self(
            policyId: null,
            name: $priority !== null ? sprintf('LEGACY_PRIORITY_%s', $priority->code) : null,
            responseTimeMinutes: $priority?->response_target_minutes,
            resolutionTimeMinutes: $priority?->resolution_target_minutes,
            source: 'legacy_priority',
        );
    }

    public static function empty(): self
    {
        return new self(
            policyId: null,
            name: null,
            responseTimeMinutes: null,
            resolutionTimeMinutes: null,
            source: 'none',
        );
    }

    public function responseDueAt(CarbonImmutable $baseAt): ?CarbonImmutable
    {
        return $this->responseTimeMinutes !== null
            ? $baseAt->addMinutes($this->responseTimeMinutes)
            : null;
    }

    public function resolutionDueAt(CarbonImmutable $baseAt): ?CarbonImmutable
    {
        return $this->resolutionTimeMinutes !== null
            ? $baseAt->addMinutes($this->resolutionTimeMinutes)
            : null;
    }

    public function hasTargets(): bool
    {
        return $this->responseTimeMinutes !== null || $this->resolutionTimeMinutes !== null;
    }
}
