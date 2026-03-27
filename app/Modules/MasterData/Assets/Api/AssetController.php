<?php

namespace App\Modules\MasterData\Assets\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AssetResource;
use App\Models\Asset;
use App\Modules\MasterData\Assets\AssetService;
use App\Modules\MasterData\Assets\Requests\StoreAssetRequest;
use App\Modules\MasterData\Assets\Requests\UpdateAssetRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AssetController extends Controller
{
    public function __construct(private readonly AssetService $assetService)
    {
    }

    public function index(Request $request)
    {
        $filters = [
            'search' => $request->input('search'),
            'asset_category_id' => $request->input('asset_category_id'),
            'asset_status_id' => $request->input('asset_status_id'),
            'service_id' => $request->input('service_id'),
            'department_owner_id' => $request->input('department_owner_id'),
            'vendor_id' => $request->input('vendor_id'),
            'asset_location_id' => $request->input('asset_location_id'),
            'criticality' => $request->input('criticality'),
        ];

        if ($request->has('is_active') && $request->input('is_active') !== '') {
            $filters['is_active'] = (bool) $request->input('is_active');
        }

        $assets = $this->assetService->paginate($filters, (int) $request->input('per_page', 15));

        return AssetResource::collection($assets);
    }

    public function store(StoreAssetRequest $request): AssetResource
    {
        return new AssetResource($this->assetService->create($request->validated()));
    }

    public function show(Asset $asset): AssetResource
    {
        return new AssetResource($asset->load([
            'category:id,name',
            'service:id,name',
            'ownerDepartment:id,name',
            'vendor:id,name',
            'location:id,name',
            'status:id,name',
        ]));
    }

    public function update(UpdateAssetRequest $request, Asset $asset): AssetResource
    {
        return new AssetResource($this->assetService->update($asset, $request->validated()));
    }

    public function destroy(Asset $asset): JsonResponse
    {
        $this->assetService->delete($asset);

        return response()->json(['message' => 'Asset deleted.']);
    }
}
