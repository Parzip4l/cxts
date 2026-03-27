<?php

namespace App\Modules\MasterData\AssetCategories\Web;

use App\Http\Controllers\Controller;
use App\Models\AssetCategory;
use App\Models\EngineerSkill;
use App\Modules\MasterData\AssetCategories\AssetCategoryService;
use App\Modules\MasterData\AssetCategories\Requests\StoreAssetCategoryRequest;
use App\Modules\MasterData\AssetCategories\Requests\UpdateAssetCategoryRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AssetCategoryController extends Controller
{
    public function __construct(private readonly AssetCategoryService $assetCategoryService)
    {
        $this->authorizeResource(AssetCategory::class, 'asset_category');
    }

    public function index(Request $request): View
    {
        $filters = [
            'search' => $request->input('search'),
        ];

        if ($request->has('is_active') && $request->input('is_active') !== '') {
            $filters['is_active'] = (bool) $request->input('is_active');
        }

        $assetCategories = $this->assetCategoryService->paginate($filters);

        return view('modules.master-data.asset-categories.index', [
            'assetCategories' => $assetCategories,
            'filters' => $filters,
        ]);
    }

    public function create(): View
    {
        return view('modules.master-data.asset-categories.form', [
            'assetCategory' => new AssetCategory(),
            'engineerSkillOptions' => EngineerSkill::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'action' => route('master-data.asset-categories.store'),
            'method' => 'POST',
            'pageTitle' => 'Create Asset Category',
        ]);
    }

    public function store(StoreAssetCategoryRequest $request): RedirectResponse
    {
        $this->assetCategoryService->create($request->validated());

        return redirect()
            ->route('master-data.asset-categories.index')
            ->with('success', 'Asset category has been created.');
    }

    public function edit(AssetCategory $assetCategory): View
    {
        return view('modules.master-data.asset-categories.form', [
            'assetCategory' => $assetCategory,
            'engineerSkillOptions' => EngineerSkill::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'action' => route('master-data.asset-categories.update', $assetCategory),
            'method' => 'PUT',
            'pageTitle' => 'Edit Asset Category',
        ]);
    }

    public function update(UpdateAssetCategoryRequest $request, AssetCategory $assetCategory): RedirectResponse
    {
        $this->assetCategoryService->update($assetCategory, $request->validated());

        return redirect()
            ->route('master-data.asset-categories.index')
            ->with('success', 'Asset category has been updated.');
    }

    public function destroy(AssetCategory $assetCategory): RedirectResponse
    {
        $this->assetCategoryService->delete($assetCategory);

        return redirect()
            ->route('master-data.asset-categories.index')
            ->with('success', 'Asset category has been deleted.');
    }
}
