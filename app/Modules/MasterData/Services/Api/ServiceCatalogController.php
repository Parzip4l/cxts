<?php

namespace App\Modules\MasterData\Services\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ServiceCatalogResource;
use App\Models\ServiceCatalog;
use App\Modules\MasterData\Services\Requests\StoreServiceCatalogRequest;
use App\Modules\MasterData\Services\Requests\UpdateServiceCatalogRequest;
use App\Modules\MasterData\Services\ServiceCatalogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ServiceCatalogController extends Controller
{
    public function __construct(private readonly ServiceCatalogService $serviceCatalogService)
    {
    }

    public function index(Request $request)
    {
        $filters = [
            'search' => $request->input('search'),
            'ownership_model' => $request->input('ownership_model'),
        ];

        if ($request->has('is_active') && $request->input('is_active') !== '') {
            $filters['is_active'] = (bool) $request->input('is_active');
        }

        $services = $this->serviceCatalogService->paginate($filters, (int) $request->input('per_page', 15));

        return ServiceCatalogResource::collection($services);
    }

    public function store(StoreServiceCatalogRequest $request): ServiceCatalogResource
    {
        return new ServiceCatalogResource($this->serviceCatalogService->create($request->validated()));
    }

    public function show(ServiceCatalog $service): ServiceCatalogResource
    {
        return new ServiceCatalogResource($service->load(['ownerDepartment:id,name', 'vendor:id,name', 'manager:id,name']));
    }

    public function update(UpdateServiceCatalogRequest $request, ServiceCatalog $service): ServiceCatalogResource
    {
        return new ServiceCatalogResource($this->serviceCatalogService->update($service, $request->validated()));
    }

    public function destroy(ServiceCatalog $service): JsonResponse
    {
        $this->serviceCatalogService->delete($service);

        return response()->json(['message' => 'Service catalog deleted.']);
    }
}
