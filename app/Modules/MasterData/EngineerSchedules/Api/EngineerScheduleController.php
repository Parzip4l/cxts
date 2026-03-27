<?php

namespace App\Modules\MasterData\EngineerSchedules\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\EngineerScheduleResource;
use App\Models\EngineerSchedule;
use App\Modules\MasterData\EngineerSchedules\EngineerScheduleService;
use App\Modules\MasterData\EngineerSchedules\Requests\StoreEngineerScheduleRequest;
use App\Modules\MasterData\EngineerSchedules\Requests\UpdateEngineerScheduleRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EngineerScheduleController extends Controller
{
    public function __construct(private readonly EngineerScheduleService $engineerScheduleService)
    {
    }

    public function index(Request $request)
    {
        $filters = [
            'search' => $request->input('search'),
            'user_id' => $request->input('user_id'),
            'status' => $request->input('status'),
            'work_date' => $request->input('work_date'),
        ];

        return EngineerScheduleResource::collection(
            $this->engineerScheduleService->paginate($filters, (int) $request->input('per_page', 15))
        );
    }

    public function store(StoreEngineerScheduleRequest $request): JsonResponse
    {
        $payload = $request->validated();
        $payload['assigned_by_id'] = $payload['assigned_by_id'] ?? $request->user()->id;

        return (new EngineerScheduleResource($this->engineerScheduleService->create($payload)))
            ->response()
            ->setStatusCode(201);
    }

    public function show(EngineerSchedule $engineerSchedule): EngineerScheduleResource
    {
        return new EngineerScheduleResource($engineerSchedule->load(['engineer:id,name', 'shift:id,name,start_time,end_time', 'assignedBy:id,name']));
    }

    public function update(UpdateEngineerScheduleRequest $request, EngineerSchedule $engineerSchedule): EngineerScheduleResource
    {
        $payload = $request->validated();
        $payload['assigned_by_id'] = $payload['assigned_by_id'] ?? $request->user()->id;

        return new EngineerScheduleResource($this->engineerScheduleService->update($engineerSchedule, $payload));
    }

    public function destroy(EngineerSchedule $engineerSchedule): JsonResponse
    {
        $this->engineerScheduleService->delete($engineerSchedule);

        return response()->json(['message' => 'Engineer schedule deleted.']);
    }
}
