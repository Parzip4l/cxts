<?php

namespace App\Modules\MasterData\Shifts\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ShiftResource;
use App\Models\Shift;
use App\Modules\MasterData\Shifts\Requests\StoreShiftRequest;
use App\Modules\MasterData\Shifts\Requests\UpdateShiftRequest;
use App\Modules\MasterData\Shifts\ShiftService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ShiftController extends Controller
{
    public function __construct(private readonly ShiftService $shiftService)
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

        return ShiftResource::collection($this->shiftService->paginate($filters, (int) $request->input('per_page', 15)));
    }

    public function store(StoreShiftRequest $request): JsonResponse
    {
        $shift = $this->shiftService->create($request->validated());

        return (new ShiftResource($shift))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Shift $shift): ShiftResource
    {
        return new ShiftResource($shift);
    }

    public function update(UpdateShiftRequest $request, Shift $shift): ShiftResource
    {
        return new ShiftResource($this->shiftService->update($shift, $request->validated()));
    }

    public function destroy(Shift $shift): JsonResponse
    {
        $this->shiftService->delete($shift);

        return response()->json(['message' => 'Shift deleted.']);
    }
}
