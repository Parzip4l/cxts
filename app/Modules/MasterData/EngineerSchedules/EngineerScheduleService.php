<?php

namespace App\Modules\MasterData\EngineerSchedules;

use App\Models\EngineerSchedule;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EngineerScheduleService
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return EngineerSchedule::query()
            ->with(['engineer:id,name', 'shift:id,name,start_time,end_time', 'assignedBy:id,name'])
            ->when($filters['search'] ?? null, function ($query, $search) {
                $query->whereHas('engineer', fn ($engineerQuery) => $engineerQuery->where('name', 'like', "%{$search}%"));
            })
            ->when($filters['user_id'] ?? null, fn ($query, $userId) => $query->where('user_id', $userId))
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when($filters['work_date'] ?? null, fn ($query, $workDate) => $query->whereDate('work_date', $workDate))
            ->orderByDesc('work_date')
            ->orderBy('user_id')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function paginateForEngineer(User $engineer, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return EngineerSchedule::query()
            ->with(['shift:id,name,start_time,end_time', 'assignedBy:id,name'])
            ->where('user_id', $engineer->id)
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when($filters['work_date_from'] ?? null, fn ($query, $startDate) => $query->whereDate('work_date', '>=', $startDate))
            ->when($filters['work_date_to'] ?? null, fn ($query, $endDate) => $query->whereDate('work_date', '<=', $endDate))
            ->orderBy('work_date')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function create(array $data): EngineerSchedule
    {
        return EngineerSchedule::query()->create($this->preparePayload($data))
            ->fresh(['engineer:id,name', 'shift:id,name,start_time,end_time', 'assignedBy:id,name']);
    }

    public function update(EngineerSchedule $engineerSchedule, array $data): EngineerSchedule
    {
        $engineerSchedule->update($this->preparePayload($data));

        return $engineerSchedule->fresh(['engineer:id,name', 'shift:id,name,start_time,end_time', 'assignedBy:id,name']);
    }

    public function delete(EngineerSchedule $engineerSchedule): void
    {
        $engineerSchedule->delete();
    }

    private function preparePayload(array $data): array
    {
        return $data;
    }
}
