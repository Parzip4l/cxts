<?php

namespace App\Modules\MasterData\EngineerSchedules\Web;

use App\Http\Controllers\Controller;
use App\Models\EngineerSchedule;
use App\Models\Shift;
use App\Models\User;
use App\Modules\MasterData\EngineerSchedules\EngineerScheduleService;
use App\Modules\MasterData\EngineerSchedules\Requests\StoreEngineerScheduleRequest;
use App\Modules\MasterData\EngineerSchedules\Requests\UpdateEngineerScheduleRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EngineerScheduleController extends Controller
{
    public function __construct(private readonly EngineerScheduleService $engineerScheduleService)
    {
        $this->authorizeResource(EngineerSchedule::class, 'engineer_schedule');
    }

    public function index(Request $request): View
    {
        $filters = [
            'search' => $request->input('search'),
            'user_id' => $request->input('user_id'),
            'status' => $request->input('status'),
            'work_date' => $request->input('work_date'),
        ];

        return view('modules.master-data.engineer-schedules.index', [
            'schedules' => $this->engineerScheduleService->paginate($filters),
            'filters' => $filters,
            'engineerOptions' => User::query()->where('role', 'engineer')->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function create(): View
    {
        return view('modules.master-data.engineer-schedules.form', [
            'schedule' => new EngineerSchedule(),
            'engineerOptions' => User::query()->where('role', 'engineer')->orderBy('name')->get(['id', 'name']),
            'shiftOptions' => Shift::query()->where('is_active', true)->orderBy('name')->get(['id', 'name', 'start_time', 'end_time']),
            'action' => route('master-data.engineer-schedules.store'),
            'method' => 'POST',
            'pageTitle' => 'Create Engineer Schedule',
        ]);
    }

    public function store(StoreEngineerScheduleRequest $request): RedirectResponse
    {
        $payload = $request->validated();
        $payload['assigned_by_id'] = $payload['assigned_by_id'] ?? $request->user()->id;

        $this->engineerScheduleService->create($payload);

        return redirect()->route('master-data.engineer-schedules.index')->with('success', 'Engineer schedule has been created.');
    }

    public function edit(EngineerSchedule $engineerSchedule): View
    {
        return view('modules.master-data.engineer-schedules.form', [
            'schedule' => $engineerSchedule,
            'engineerOptions' => User::query()->where('role', 'engineer')->orderBy('name')->get(['id', 'name']),
            'shiftOptions' => Shift::query()->where('is_active', true)->orderBy('name')->get(['id', 'name', 'start_time', 'end_time']),
            'action' => route('master-data.engineer-schedules.update', $engineerSchedule),
            'method' => 'PUT',
            'pageTitle' => 'Edit Engineer Schedule',
        ]);
    }

    public function update(UpdateEngineerScheduleRequest $request, EngineerSchedule $engineerSchedule): RedirectResponse
    {
        $payload = $request->validated();
        $payload['assigned_by_id'] = $payload['assigned_by_id'] ?? $request->user()->id;

        $this->engineerScheduleService->update($engineerSchedule, $payload);

        return redirect()->route('master-data.engineer-schedules.index')->with('success', 'Engineer schedule has been updated.');
    }

    public function destroy(EngineerSchedule $engineerSchedule): RedirectResponse
    {
        $this->engineerScheduleService->delete($engineerSchedule);

        return redirect()->route('master-data.engineer-schedules.index')->with('success', 'Engineer schedule has been deleted.');
    }
}
