<?php

namespace App\Modules\MasterData\Departments\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\DepartmentResource;
use App\Models\Department;
use App\Modules\MasterData\Departments\DepartmentService;
use App\Modules\MasterData\Departments\Requests\StoreDepartmentRequest;
use App\Modules\MasterData\Departments\Requests\UpdateDepartmentRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    public function __construct(private readonly DepartmentService $departmentService)
    {
    }

    public function index(Request $request)
    {
        $filters = [
            'search' => $request->input('search'),
        ];

        if ($request->has('is_active') && $request->input('is_active') !== '') {
            $filters['is_active'] = (bool) $request->input('is_active');
        }

        $departments = $this->departmentService->paginate($filters, (int) $request->input('per_page', 15));

        return DepartmentResource::collection($departments);
    }

    public function store(StoreDepartmentRequest $request): DepartmentResource
    {
        $department = $this->departmentService->create($request->validated());

        return new DepartmentResource($department->load(['parentDepartment:id,name', 'head:id,name']));
    }

    public function show(Department $department): DepartmentResource
    {
        return new DepartmentResource($department->load(['parentDepartment:id,name', 'head:id,name']));
    }

    public function update(UpdateDepartmentRequest $request, Department $department): DepartmentResource
    {
        $updatedDepartment = $this->departmentService->update($department, $request->validated());

        return new DepartmentResource($updatedDepartment);
    }

    public function destroy(Department $department): JsonResponse
    {
        $this->departmentService->delete($department);

        return response()->json(['message' => 'Department deleted.']);
    }
}
