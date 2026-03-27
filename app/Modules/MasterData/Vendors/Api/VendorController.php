<?php

namespace App\Modules\MasterData\Vendors\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\VendorResource;
use App\Models\Vendor;
use App\Modules\MasterData\Vendors\Requests\StoreVendorRequest;
use App\Modules\MasterData\Vendors\Requests\UpdateVendorRequest;
use App\Modules\MasterData\Vendors\VendorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VendorController extends Controller
{
    public function __construct(private readonly VendorService $vendorService)
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

        $vendors = $this->vendorService->paginate($filters, (int) $request->input('per_page', 15));

        return VendorResource::collection($vendors);
    }

    public function store(StoreVendorRequest $request): VendorResource
    {
        return new VendorResource($this->vendorService->create($request->validated()));
    }

    public function show(Vendor $vendor): VendorResource
    {
        return new VendorResource($vendor);
    }

    public function update(UpdateVendorRequest $request, Vendor $vendor): VendorResource
    {
        return new VendorResource($this->vendorService->update($vendor, $request->validated()));
    }

    public function destroy(Vendor $vendor): JsonResponse
    {
        $this->vendorService->delete($vendor);

        return response()->json(['message' => 'Vendor deleted.']);
    }
}
