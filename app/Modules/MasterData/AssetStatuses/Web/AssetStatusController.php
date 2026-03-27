<?php

namespace App\Modules\MasterData\AssetStatuses\Web;

use App\Http\Controllers\Controller;
use App\Models\AssetStatus;
use App\Modules\MasterData\AssetStatuses\AssetStatusService;
use App\Modules\MasterData\AssetStatuses\Requests\StoreAssetStatusRequest;
use App\Modules\MasterData\AssetStatuses\Requests\UpdateAssetStatusRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AssetStatusController extends Controller
{
    public function __construct(private readonly AssetStatusService $assetStatusService)
    {
        $this->authorizeResource(AssetStatus::class, 'asset_status');
    }

    public function index(Request $request): View
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

        $assetStatuses = $this->assetStatusService->paginate($filters);

        return view('modules.master-data.asset-statuses.index', [
            'assetStatuses' => $assetStatuses,
            'filters' => $filters,
        ]);
    }

    public function create(): View
    {
        return view('modules.master-data.asset-statuses.form', [
            'assetStatus' => new AssetStatus(),
            'action' => route('master-data.asset-statuses.store'),
            'method' => 'POST',
            'pageTitle' => 'Create Asset Status',
        ]);
    }

    public function store(StoreAssetStatusRequest $request): RedirectResponse
    {
        $this->assetStatusService->create($request->validated());

        return redirect()
            ->route('master-data.asset-statuses.index')
            ->with('success', 'Asset status has been created.');
    }

    public function edit(AssetStatus $assetStatus): View
    {
        return view('modules.master-data.asset-statuses.form', [
            'assetStatus' => $assetStatus,
            'action' => route('master-data.asset-statuses.update', $assetStatus),
            'method' => 'PUT',
            'pageTitle' => 'Edit Asset Status',
        ]);
    }

    public function update(UpdateAssetStatusRequest $request, AssetStatus $assetStatus): RedirectResponse
    {
        $this->assetStatusService->update($assetStatus, $request->validated());

        return redirect()
            ->route('master-data.asset-statuses.index')
            ->with('success', 'Asset status has been updated.');
    }

    public function destroy(AssetStatus $assetStatus): RedirectResponse
    {
        $this->assetStatusService->delete($assetStatus);

        return redirect()
            ->route('master-data.asset-statuses.index')
            ->with('success', 'Asset status has been deleted.');
    }
}
