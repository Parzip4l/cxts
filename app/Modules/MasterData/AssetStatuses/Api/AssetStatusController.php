<?php

namespace App\Modules\MasterData\AssetStatuses\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AssetStatusResource;
use App\Models\AssetStatus;
use App\Modules\MasterData\AssetStatuses\AssetStatusService;
use App\Modules\MasterData\AssetStatuses\Requests\StoreAssetStatusRequest;
use App\Modules\MasterData\AssetStatuses\Requests\UpdateAssetStatusRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AssetStatusController extends Controller
{
    public function __construct(private readonly AssetStatusService $assetStatusService)
    {
    }

    public function index(Request $request)
    {
        $filters = [
            'search' => $request->input('search'),
        ];

        if ($request->has('is_operational') && $request->input('is_operational') !== '') {
            $filters['is_operational'] = (bool) $request->input('is_operational');
        }

        if ($request->has('is_active') && $request->input('is_active') !== '') {
            $filters['is_active'] = (bool) $request->input('is_active');
        }

        $assetStatuses = $this->assetStatusService->paginate($filters, (int) $request->input('per_page', 15));

        return AssetStatusResource::collection($assetStatuses);
    }

    public function store(StoreAssetStatusRequest $request): AssetStatusResource
    {
        return new AssetStatusResource($this->assetStatusService->create($request->validated()));
    }

    public function show(AssetStatus $assetStatus): AssetStatusResource
    {
        return new AssetStatusResource($assetStatus);
    }

    public function update(UpdateAssetStatusRequest $request, AssetStatus $assetStatus): AssetStatusResource
    {
        return new AssetStatusResource($this->assetStatusService->update($assetStatus, $request->validated()));
    }

    public function destroy(AssetStatus $assetStatus): JsonResponse
    {
        $this->assetStatusService->delete($assetStatus);

        return response()->json(['message' => 'Asset status deleted.']);
    }
}
