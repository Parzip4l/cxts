<?php

namespace App\Modules\Tickets\ServiceCommitments;

use App\Models\ServiceCommitment;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;

class ServiceCommitmentService
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return ServiceCommitment::query()
            ->with(['service:id,name', 'providerDepartment:id,name', 'vendor:id,name'])
            ->when($filters['search'] ?? null, function (Builder $query, string $search): void {
                $query->where(function (Builder $subQuery) use ($search): void {
                    $subQuery->where('commitment_number', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%")
                        ->orWhere('escalation_contact', 'like', "%{$search}%")
                        ->orWhere('notes', 'like', "%{$search}%");
                });
            })
            ->when($filters['commitment_type'] ?? null, fn (Builder $query, string $type) => $query->where('commitment_type', $type))
            ->when($filters['status'] ?? null, fn (Builder $query, string $status) => $query->where('status', $status))
            ->when($filters['service_id'] ?? null, fn (Builder $query, $serviceId) => $query->where('service_id', $serviceId))
            ->orderBy('commitment_type')
            ->orderBy('name')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function create(array $data, ?User $actor = null): ServiceCommitment
    {
        $commitment = ServiceCommitment::query()->create([
            ...$this->preparePayload($data),
            'commitment_number' => $this->generateCommitmentNumber($data['commitment_type']),
            'created_by_id' => $actor?->id,
            'updated_by_id' => $actor?->id,
        ]);

        return $commitment->fresh(['service:id,name', 'providerDepartment:id,name', 'vendor:id,name']);
    }

    public function update(ServiceCommitment $commitment, array $data, ?User $actor = null): ServiceCommitment
    {
        $commitment->update([
            ...$this->preparePayload($data),
            'updated_by_id' => $actor?->id,
        ]);

        return $commitment->fresh(['service:id,name', 'providerDepartment:id,name', 'vendor:id,name']);
    }

    public function delete(ServiceCommitment $commitment): void
    {
        $commitment->delete();
    }

    private function preparePayload(array $data): array
    {
        $payload = Arr::except($data, ['commitment_number']);

        foreach (['service_id', 'provider_department_id', 'vendor_id', 'response_target_minutes', 'resolution_target_minutes'] as $nullableKey) {
            if (($payload[$nullableKey] ?? null) === '') {
                $payload[$nullableKey] = null;
            }
        }

        if (($payload['availability_target_percent'] ?? null) === '') {
            $payload['availability_target_percent'] = null;
        }

        if (($payload['commitment_type'] ?? null) === ServiceCommitment::TYPE_OLA) {
            $payload['vendor_id'] = null;
        }

        if (($payload['commitment_type'] ?? null) === ServiceCommitment::TYPE_UNDERPINNING_CONTRACT) {
            $payload['provider_department_id'] = null;
        }

        return $payload;
    }

    private function generateCommitmentNumber(string $type): string
    {
        $prefix = $type === ServiceCommitment::TYPE_UNDERPINNING_CONTRACT ? 'UC' : 'OLA';
        $datePrefix = now()->format('Ymd');
        $base = "{$prefix}-{$datePrefix}";
        $lastNumber = ServiceCommitment::query()
            ->where('commitment_number', 'like', "{$base}-%")
            ->orderByDesc('commitment_number')
            ->value('commitment_number');

        $lastSequence = 0;
        if (is_string($lastNumber)) {
            $segments = explode('-', $lastNumber);
            $lastSequence = (int) end($segments);
        }

        return $base.'-'.str_pad((string) ($lastSequence + 1), 4, '0', STR_PAD_LEFT);
    }
}
