<?php

namespace App\Modules\MasterData\Departments\Web;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\User;
use App\Modules\MasterData\Departments\DepartmentService;
use App\Modules\MasterData\Departments\Requests\StoreDepartmentRequest;
use App\Modules\MasterData\Departments\Requests\UpdateDepartmentRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DepartmentController extends Controller
{
    public function __construct(private readonly DepartmentService $departmentService)
    {
        $this->authorizeResource(Department::class, 'department');
    }

    public function index(Request $request): View
    {
        $filters = [
            'search' => $request->input('search'),
        ];

        if ($request->has('is_active') && $request->input('is_active') !== '') {
            $filters['is_active'] = (bool) $request->input('is_active');
        }

        $departments = $this->departmentService->paginate($filters);

        return view('modules.master-data.departments.index', [
            'departments' => $departments,
            'filters' => $filters,
        ]);
    }

    public function create(): View
    {
        return view('modules.master-data.departments.form', [
            'department' => new Department(),
            'parentOptions' => Department::query()->orderBy('name')->get(['id', 'name']),
            'headOptions' => User::query()->orderBy('name')->get(['id', 'name']),
            'action' => route('master-data.departments.store'),
            'method' => 'POST',
            'pageTitle' => 'Create Department',
        ]);
    }

    public function store(StoreDepartmentRequest $request): RedirectResponse
    {
        $this->departmentService->create($request->validated());

        return redirect()
            ->route('master-data.departments.index')
            ->with('success', 'Department has been created.');
    }

    public function edit(Department $department): View
    {
        return view('modules.master-data.departments.form', [
            'department' => $department,
            'parentOptions' => Department::query()->whereKeyNot($department->id)->orderBy('name')->get(['id', 'name']),
            'headOptions' => User::query()->orderBy('name')->get(['id', 'name']),
            'action' => route('master-data.departments.update', $department),
            'method' => 'PUT',
            'pageTitle' => 'Edit Department',
        ]);
    }

    public function update(UpdateDepartmentRequest $request, Department $department): RedirectResponse
    {
        $this->departmentService->update($department, $request->validated());

        return redirect()
            ->route('master-data.departments.index')
            ->with('success', 'Department has been updated.');
    }

    public function destroy(Department $department): RedirectResponse
    {
        $this->departmentService->delete($department);

        return redirect()
            ->route('master-data.departments.index')
            ->with('success', 'Department has been deleted.');
    }
}
