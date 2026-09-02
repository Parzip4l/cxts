<?php

namespace App\Modules\Tickets\ServiceCommitments\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ServiceCommitmentResource;
use App\Models\ServiceCommitment;
use App\Modules\Tickets\ServiceCommitments\Requests\ServiceCommitmentRequest;
use App\Modules\Tickets\ServiceCommitments\ServiceCommitmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ServiceCommitmentController extends Controller
{
    public function __construct(private readonly ServiceCommitmentService $serviceCommitmentService)
    {
    }

    public function index(Request $request)
    {
        $filters = [
            'search' => $request->input('search'),
            'commitment_type' => $request->input('commitment_type'),
            'status' => $request->input('status'),
            'service_id' => $request->input('service_id'),
        ];

        return ServiceCommitmentResource::collection(
            $this->serviceCommitmentService->paginate($filters, (int) $request->input('per_page', 15))
        );
    }

    public function store(ServiceCommitmentRequest $request): JsonResponse
    {
        return (new ServiceCommitmentResource($this->serviceCommitmentService->create($request->validated(), $request->user())))
            ->response()
            ->setStatusCode(201);
    }

    public function show(ServiceCommitment $serviceCommitment): ServiceCommitmentResource
    {
        return new ServiceCommitmentResource($serviceCommitment->load(['service:id,name', 'providerDepartment:id,name', 'vendor:id,name']));
    }

    public function update(ServiceCommitmentRequest $request, ServiceCommitment $serviceCommitment): ServiceCommitmentResource
    {
        return new ServiceCommitmentResource($this->serviceCommitmentService->update($serviceCommitment, $request->validated(), $request->user()));
    }

    public function destroy(ServiceCommitment $serviceCommitment): JsonResponse
    {
        $this->serviceCommitmentService->delete($serviceCommitment);

        return response()->json(['message' => 'Service commitment deleted.']);
    }
}
