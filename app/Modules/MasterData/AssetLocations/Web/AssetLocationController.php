<?php

namespace App\Modules\MasterData\AssetLocations\Web;

use App\Http\Controllers\Controller;
use App\Models\AssetLocation;
use App\Models\Department;
use App\Modules\MasterData\AssetLocations\AssetLocationService;
use App\Modules\MasterData\AssetLocations\Requests\StoreAssetLocationRequest;
use App\Modules\MasterData\AssetLocations\Requests\UpdateAssetLocationRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AssetLocationController extends Controller
{
    public function __construct(private readonly AssetLocationService $assetLocationService)
    {
        $this->authorizeResource(AssetLocation::class, 'asset_location');
    }

    public function index(Request $request): View
    {
        $filters = [
            'search' => $request->input('search'),
            'department_id' => $request->input('department_id'),
        ];

        if ($request->has('is_active') && $request->input('is_active') !== '') {
            $filters['is_active'] = (bool) $request->input('is_active');
        }

        $assetLocations = $this->assetLocationService->paginate($filters);

        return view('modules.master-data.asset-locations.index', [
            'assetLocations' => $assetLocations,
            'departmentOptions' => Department::query()->orderBy('name')->get(['id', 'name']),
            'filters' => $filters,
        ]);
    }

    public function create(): View
    {
        return view('modules.master-data.asset-locations.form', [
            'assetLocation' => new AssetLocation(),
            'departmentOptions' => Department::query()->orderBy('name')->get(['id', 'name']),
            'action' => route('master-data.asset-locations.store'),
            'method' => 'POST',
            'pageTitle' => 'Create Asset Location',
        ]);
    }

    public function store(StoreAssetLocationRequest $request): RedirectResponse
    {
        $this->assetLocationService->create($request->validated());

        return redirect()
            ->route('master-data.asset-locations.index')
            ->with('success', 'Asset location has been created.');
    }

    public function edit(AssetLocation $assetLocation): View
    {
        return view('modules.master-data.asset-locations.form', [
            'assetLocation' => $assetLocation,
            'departmentOptions' => Department::query()->orderBy('name')->get(['id', 'name']),
            'action' => route('master-data.asset-locations.update', $assetLocation),
            'method' => 'PUT',
            'pageTitle' => 'Edit Asset Location',
        ]);
    }

    public function update(UpdateAssetLocationRequest $request, AssetLocation $assetLocation): RedirectResponse
    {
        $this->assetLocationService->update($assetLocation, $request->validated());

        return redirect()
            ->route('master-data.asset-locations.index')
            ->with('success', 'Asset location has been updated.');
    }

    public function destroy(AssetLocation $assetLocation): RedirectResponse
    {
        $this->assetLocationService->delete($assetLocation);

        return redirect()
            ->route('master-data.asset-locations.index')
            ->with('success', 'Asset location has been deleted.');
    }
}
