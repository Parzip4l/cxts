<?php

namespace App\Modules\MasterData\Services\Web;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\EngineerSkill;
use App\Models\ServiceCatalog;
use App\Models\User;
use App\Models\Vendor;
use App\Modules\MasterData\Services\Requests\StoreServiceCatalogRequest;
use App\Modules\MasterData\Services\Requests\UpdateServiceCatalogRequest;
use App\Modules\MasterData\Services\ServiceCatalogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ServiceCatalogController extends Controller
{
    public function __construct(private readonly ServiceCatalogService $serviceCatalogService)
    {
        $this->authorizeResource(ServiceCatalog::class, 'service');
    }

    public function index(Request $request): View
    {
        $filters = [
            'search' => $request->input('search'),
            'ownership_model' => $request->input('ownership_model'),
        ];

        if ($request->has('is_active') && $request->input('is_active') !== '') {
            $filters['is_active'] = (bool) $request->input('is_active');
        }

        $services = $this->serviceCatalogService->paginate($filters);

        return view('modules.master-data.services.index', [
            'services' => $services,
            'filters' => $filters,
            'ownershipOptions' => ServiceCatalog::ownershipOptions(),
        ]);
    }

    public function create(): View
    {
        return view('modules.master-data.services.form', [
            'service' => new ServiceCatalog(),
            'departmentOptions' => Department::query()->orderBy('name')->get(['id', 'name']),
            'vendorOptions' => Vendor::query()->orderBy('name')->get(['id', 'name']),
            'managerOptions' => User::query()->orderBy('name')->get(['id', 'name']),
            'engineerSkillOptions' => EngineerSkill::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'ownershipOptions' => ServiceCatalog::ownershipOptions(),
            'action' => route('master-data.services.store'),
            'method' => 'POST',
            'pageTitle' => 'Create Service Catalog',
        ]);
    }

    public function store(StoreServiceCatalogRequest $request): RedirectResponse
    {
        $this->serviceCatalogService->create($request->validated());

        return redirect()
            ->route('master-data.services.index')
            ->with('success', 'Service catalog has been created.');
    }

    public function edit(ServiceCatalog $service): View
    {
        return view('modules.master-data.services.form', [
            'service' => $service,
            'departmentOptions' => Department::query()->orderBy('name')->get(['id', 'name']),
            'vendorOptions' => Vendor::query()->orderBy('name')->get(['id', 'name']),
            'managerOptions' => User::query()->orderBy('name')->get(['id', 'name']),
            'engineerSkillOptions' => EngineerSkill::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'ownershipOptions' => ServiceCatalog::ownershipOptions(),
            'action' => route('master-data.services.update', $service),
            'method' => 'PUT',
            'pageTitle' => 'Edit Service Catalog',
        ]);
    }

    public function update(UpdateServiceCatalogRequest $request, ServiceCatalog $service): RedirectResponse
    {
        $this->serviceCatalogService->update($service, $request->validated());

        return redirect()
            ->route('master-data.services.index')
            ->with('success', 'Service catalog has been updated.');
    }

    public function destroy(ServiceCatalog $service): RedirectResponse
    {
        $this->serviceCatalogService->delete($service);

        return redirect()
            ->route('master-data.services.index')
            ->with('success', 'Service catalog has been deleted.');
    }
}
