<?php

namespace App\Modules\MasterData\AssetLocations\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AssetLocationResource;
use App\Models\AssetLocation;
use App\Modules\MasterData\AssetLocations\AssetLocationService;
use App\Modules\MasterData\AssetLocations\Requests\StoreAssetLocationRequest;
use App\Modules\MasterData\AssetLocations\Requests\UpdateAssetLocationRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AssetLocationController extends Controller
{
    public function __construct(private readonly AssetLocationService $assetLocationService)
    {
    }

    public function index(Request $request)
    {
        $filters = [
            'search' => $request->input('search'),
            'department_id' => $request->input('department_id'),
        ];

        if ($request->has('is_active') && $request->input('is_active') !== '') {
            $filters['is_active'] = (bool) $request->input('is_active');
        }

        $assetLocations = $this->assetLocationService->paginate($filters, (int) $request->input('per_page', 15));

        return AssetLocationResource::collection($assetLocations);
    }

    public function store(StoreAssetLocationRequest $request): AssetLocationResource
    {
        return new AssetLocationResource($this->assetLocationService->create($request->validated()));
    }

    public function show(AssetLocation $assetLocation): AssetLocationResource
    {
        return new AssetLocationResource($assetLocation->load('department:id,name'));
    }

    public function update(UpdateAssetLocationRequest $request, AssetLocation $assetLocation): AssetLocationResource
    {
        return new AssetLocationResource($this->assetLocationService->update($assetLocation, $request->validated()));
    }

    public function destroy(AssetLocation $assetLocation): JsonResponse
    {
        $this->assetLocationService->delete($assetLocation);

        return response()->json(['message' => 'Asset location deleted.']);
    }
}
