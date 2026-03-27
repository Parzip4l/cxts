<?php

namespace App\Modules\MasterData\Assets\Web;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\AssetLocation;
use App\Models\AssetStatus;
use App\Models\Department;
use App\Models\ServiceCatalog;
use App\Models\Vendor;
use App\Modules\MasterData\Assets\AssetService;
use App\Modules\MasterData\Assets\Requests\StoreAssetRequest;
use App\Modules\MasterData\Assets\Requests\UpdateAssetRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AssetController extends Controller
{
    public function __construct(private readonly AssetService $assetService)
    {
        $this->authorizeResource(Asset::class, 'asset');
    }

    public function index(Request $request): View
    {
        $locationViews = AssetLocation::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->limit(3)
            ->get(['id', 'name']);

        $locationViewIds = $locationViews->pluck('id')->all();
        $selectedLocationViewId = (int) $request->input('location_view');
        if (! in_array($selectedLocationViewId, $locationViewIds, true)) {
            $selectedLocationViewId = (int) ($locationViewIds[0] ?? 0);
        }

        $filters = [
            'search' => $request->input('search'),
            'asset_category_id' => $request->input('asset_category_id'),
            'asset_status_id' => $request->input('asset_status_id'),
            'service_id' => $request->input('service_id'),
            'department_owner_id' => $request->input('department_owner_id'),
            'vendor_id' => $request->input('vendor_id'),
            'criticality' => $request->input('criticality'),
        ];

        if ($selectedLocationViewId > 0) {
            $filters['asset_location_id'] = $selectedLocationViewId;
        }

        if ($request->has('is_active') && $request->input('is_active') !== '') {
            $filters['is_active'] = (bool) $request->input('is_active');
        }

        $assets = $this->assetService->paginate($filters);

        $locationViewCounts = [];
        foreach ($locationViews as $locationView) {
            $countFilters = $filters;
            $countFilters['asset_location_id'] = $locationView->id;
            $locationViewCounts[$locationView->id] = $this->assetService->count($countFilters);
        }

        return view('modules.master-data.assets.index', [
            'assets' => $assets,
            'filters' => $filters,
            'categoryOptions' => AssetCategory::query()->orderBy('name')->get(['id', 'name']),
            'statusOptions' => AssetStatus::query()->orderBy('name')->get(['id', 'name']),
            'serviceOptions' => ServiceCatalog::query()->orderBy('name')->get(['id', 'name']),
            'departmentOptions' => Department::query()->orderBy('name')->get(['id', 'name']),
            'vendorOptions' => Vendor::query()->orderBy('name')->get(['id', 'name']),
            'criticalityOptions' => Asset::criticalityOptions(),
            'locationViews' => $locationViews,
            'selectedLocationViewId' => $selectedLocationViewId,
            'locationViewCounts' => $locationViewCounts,
        ]);
    }

    public function create(): View
    {
        return view('modules.master-data.assets.form', [
            'asset' => new Asset(),
            'categoryOptions' => AssetCategory::query()->orderBy('name')->get(['id', 'name']),
            'statusOptions' => AssetStatus::query()->orderBy('name')->get(['id', 'name']),
            'serviceOptions' => ServiceCatalog::query()->orderBy('name')->get(['id', 'name']),
            'departmentOptions' => Department::query()->orderBy('name')->get(['id', 'name']),
            'vendorOptions' => Vendor::query()->orderBy('name')->get(['id', 'name']),
            'locationOptions' => AssetLocation::query()->orderBy('name')->get(['id', 'name']),
            'criticalityOptions' => Asset::criticalityOptions(),
            'action' => route('master-data.assets.store'),
            'method' => 'POST',
            'pageTitle' => 'Create Asset',
        ]);
    }

    public function store(StoreAssetRequest $request): RedirectResponse
    {
        $this->assetService->create($request->validated());

        return redirect()
            ->route('master-data.assets.index')
            ->with('success', 'Asset has been created.');
    }

    public function edit(Asset $asset): View
    {
        return view('modules.master-data.assets.form', [
            'asset' => $asset,
            'categoryOptions' => AssetCategory::query()->orderBy('name')->get(['id', 'name']),
            'statusOptions' => AssetStatus::query()->orderBy('name')->get(['id', 'name']),
            'serviceOptions' => ServiceCatalog::query()->orderBy('name')->get(['id', 'name']),
            'departmentOptions' => Department::query()->orderBy('name')->get(['id', 'name']),
            'vendorOptions' => Vendor::query()->orderBy('name')->get(['id', 'name']),
            'locationOptions' => AssetLocation::query()->orderBy('name')->get(['id', 'name']),
            'criticalityOptions' => Asset::criticalityOptions(),
            'action' => route('master-data.assets.update', $asset),
            'method' => 'PUT',
            'pageTitle' => 'Edit Asset',
        ]);
    }

    public function update(UpdateAssetRequest $request, Asset $asset): RedirectResponse
    {
        $this->assetService->update($asset, $request->validated());

        return redirect()
            ->route('master-data.assets.index')
            ->with('success', 'Asset has been updated.');
    }

    public function destroy(Asset $asset): RedirectResponse
    {
        $this->assetService->delete($asset);

        return redirect()
            ->route('master-data.assets.index')
            ->with('success', 'Asset has been deleted.');
    }
}
