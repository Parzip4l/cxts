<?php

namespace App\Modules\MasterData\Vendors\Web;

use App\Http\Controllers\Controller;
use App\Models\Vendor;
use App\Modules\MasterData\Vendors\Requests\StoreVendorRequest;
use App\Modules\MasterData\Vendors\Requests\UpdateVendorRequest;
use App\Modules\MasterData\Vendors\VendorService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VendorController extends Controller
{
    public function __construct(private readonly VendorService $vendorService)
    {
        $this->authorizeResource(Vendor::class, 'vendor');
    }

    public function index(Request $request): View
    {
        $filters = [
            'search' => $request->input('search'),
        ];

        if ($request->has('is_active') && $request->input('is_active') !== '') {
            $filters['is_active'] = (bool) $request->input('is_active');
        }

        $vendors = $this->vendorService->paginate($filters);

        return view('modules.master-data.vendors.index', [
            'vendors' => $vendors,
            'filters' => $filters,
        ]);
    }

    public function create(): View
    {
        return view('modules.master-data.vendors.form', [
            'vendor' => new Vendor(),
            'action' => route('master-data.vendors.store'),
            'method' => 'POST',
            'pageTitle' => 'Create Vendor',
        ]);
    }

    public function store(StoreVendorRequest $request): RedirectResponse
    {
        $this->vendorService->create($request->validated());

        return redirect()
            ->route('master-data.vendors.index')
            ->with('success', 'Vendor has been created.');
    }

    public function edit(Vendor $vendor): View
    {
        return view('modules.master-data.vendors.form', [
            'vendor' => $vendor,
            'action' => route('master-data.vendors.update', $vendor),
            'method' => 'PUT',
            'pageTitle' => 'Edit Vendor',
        ]);
    }

    public function update(UpdateVendorRequest $request, Vendor $vendor): RedirectResponse
    {
        $this->vendorService->update($vendor, $request->validated());

        return redirect()
            ->route('master-data.vendors.index')
            ->with('success', 'Vendor has been updated.');
    }

    public function destroy(Vendor $vendor): RedirectResponse
    {
        $this->vendorService->delete($vendor);

        return redirect()
            ->route('master-data.vendors.index')
            ->with('success', 'Vendor has been deleted.');
    }
}
