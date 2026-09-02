<?php

namespace App\Modules\Tickets\Problems;

use App\Models\Problem;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class ProblemService
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->query($filters)
            ->orderByDesc('created_at')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function create(array $data, ?User $actor = null): Problem
    {
        return DB::transaction(function () use ($data, $actor): Problem {
            $problem = Problem::query()->create([
                ...$this->preparePayload($data),
                'problem_number' => $this->generateProblemNumber(),
                'created_by_id' => $actor?->id,
                'updated_by_id' => $actor?->id,
            ]);

            $problem->tickets()->sync($this->ticketIds($data));

            return $problem->fresh($this->relations())->loadCount('tickets');
        });
    }

    public function update(Problem $problem, array $data, ?User $actor = null): Problem
    {
        return DB::transaction(function () use ($problem, $data, $actor): Problem {
            $problem->update([
                ...$this->preparePayload($data),
                'updated_by_id' => $actor?->id,
            ]);

            $problem->tickets()->sync($this->ticketIds($data));

            return $problem->fresh($this->relations())->loadCount('tickets');
        });
    }

    public function delete(Problem $problem): void
    {
        $problem->delete();
    }

    private function query(array $filters = []): Builder
    {
        return Problem::query()
            ->with($this->relations())
            ->withCount('tickets')
            ->when($filters['search'] ?? null, function (Builder $query, string $search): void {
                $query->where(function (Builder $subQuery) use ($search): void {
                    $subQuery->where('problem_number', 'like', "%{$search}%")
                        ->orWhere('title', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhere('root_cause', 'like', "%{$search}%");
                });
            })
            ->when($filters['status'] ?? null, fn (Builder $query, string $status) => $query->where('status', $status))
            ->when(array_key_exists('is_known_error', $filters), fn (Builder $query) => $query->where('is_known_error', (bool) $filters['is_known_error']))
            ->when($filters['owner_user_id'] ?? null, fn (Builder $query, $ownerId) => $query->where('owner_user_id', $ownerId));
    }

    private function relations(): array
    {
        return [
            'owner:id,name',
            'priority:id,name',
            'tickets:id,ticket_number,title,process_type,ticket_status_id,ticket_priority_id,created_at',
            'tickets.status:id,name,code',
            'tickets.priority:id,name',
        ];
    }

    private function preparePayload(array $data): array
    {
        $payload = Arr::except($data, ['ticket_ids']);
        $payload['is_known_error'] = (bool) ($payload['is_known_error'] ?? false);

        if (($payload['status'] ?? null) === Problem::STATUS_KNOWN_ERROR) {
            $payload['is_known_error'] = true;
        }

        return $payload;
    }

    private function ticketIds(array $data): array
    {
        return collect($data['ticket_ids'] ?? [])
            ->filter(fn ($value) => $value !== null && $value !== '')
            ->map(fn ($value) => (int) $value)
            ->unique()
            ->values()
            ->all();
    }

    private function generateProblemNumber(): string
    {
        $datePrefix = now()->format('Ymd');
        $base = "PRB-{$datePrefix}";
        $lastNumber = Problem::query()
            ->where('problem_number', 'like', "{$base}-%")
            ->orderByDesc('problem_number')
            ->value('problem_number');

        $lastSequence = 0;
        if (is_string($lastNumber)) {
            $segments = explode('-', $lastNumber);
            $lastSequence = (int) end($segments);
        }

        return $base.'-'.str_pad((string) ($lastSequence + 1), 4, '0', STR_PAD_LEFT);
    }
}
