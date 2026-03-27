<?php

namespace App\Modules\MasterData\AssetCategories\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AssetCategoryResource;
use App\Models\AssetCategory;
use App\Modules\MasterData\AssetCategories\AssetCategoryService;
use App\Modules\MasterData\AssetCategories\Requests\StoreAssetCategoryRequest;
use App\Modules\MasterData\AssetCategories\Requests\UpdateAssetCategoryRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AssetCategoryController extends Controller
{
    public function __construct(private readonly AssetCategoryService $assetCategoryService)
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

        $assetCategories = $this->assetCategoryService->paginate($filters, (int) $request->input('per_page', 15));

        return AssetCategoryResource::collection($assetCategories);
    }

    public function store(StoreAssetCategoryRequest $request): AssetCategoryResource
    {
        return new AssetCategoryResource($this->assetCategoryService->create($request->validated()));
    }

    public function show(AssetCategory $assetCategory): AssetCategoryResource
    {
        return new AssetCategoryResource($assetCategory);
    }

    public function update(UpdateAssetCategoryRequest $request, AssetCategory $assetCategory): AssetCategoryResource
    {
        return new AssetCategoryResource($this->assetCategoryService->update($assetCategory, $request->validated()));
    }

    public function destroy(AssetCategory $assetCategory): JsonResponse
    {
        $this->assetCategoryService->delete($assetCategory);

        return response()->json(['message' => 'Asset category deleted.']);
    }
}
