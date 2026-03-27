<?php

namespace App\Modules\MasterData\Shifts\Web;

use App\Http\Controllers\Controller;
use App\Models\Shift;
use App\Modules\MasterData\Shifts\Requests\StoreShiftRequest;
use App\Modules\MasterData\Shifts\Requests\UpdateShiftRequest;
use App\Modules\MasterData\Shifts\ShiftService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ShiftController extends Controller
{
    public function __construct(private readonly ShiftService $shiftService)
    {
        $this->authorizeResource(Shift::class, 'shift');
    }

    public function index(Request $request): View
    {
        $filters = [
            'search' => $request->input('search'),
        ];

        if ($request->has('is_active') && $request->input('is_active') !== '') {
            $filters['is_active'] = (bool) $request->input('is_active');
        }

        return view('modules.master-data.shifts.index', [
            'shifts' => $this->shiftService->paginate($filters),
            'filters' => $filters,
        ]);
    }

    public function create(): View
    {
        return view('modules.master-data.shifts.form', [
            'shift' => new Shift(),
            'action' => route('master-data.shifts.store'),
            'method' => 'POST',
            'pageTitle' => 'Create Shift',
        ]);
    }

    public function store(StoreShiftRequest $request): RedirectResponse
    {
        $this->shiftService->create($request->validated());

        return redirect()->route('master-data.shifts.index')->with('success', 'Shift has been created.');
    }

    public function edit(Shift $shift): View
    {
        return view('modules.master-data.shifts.form', [
            'shift' => $shift,
            'action' => route('master-data.shifts.update', $shift),
            'method' => 'PUT',
            'pageTitle' => 'Edit Shift',
        ]);
    }

    public function update(UpdateShiftRequest $request, Shift $shift): RedirectResponse
    {
        $this->shiftService->update($shift, $request->validated());

        return redirect()->route('master-data.shifts.index')->with('success', 'Shift has been updated.');
    }

    public function destroy(Shift $shift): RedirectResponse
    {
        $this->shiftService->delete($shift);

        return redirect()->route('master-data.shifts.index')->with('success', 'Shift has been deleted.');
    }
}
