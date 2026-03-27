<?php

namespace App\Modules\Tickets\TicketPriorities\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TicketPriorityResource;
use App\Models\TicketPriority;
use App\Modules\Tickets\TicketPriorities\Requests\StoreTicketPriorityRequest;
use App\Modules\Tickets\TicketPriorities\Requests\UpdateTicketPriorityRequest;
use App\Modules\Tickets\TicketPriorities\TicketPriorityService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TicketPriorityController extends Controller
{
    public function __construct(private readonly TicketPriorityService $ticketPriorityService)
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

        $ticketPriorities = $this->ticketPriorityService->paginate($filters, (int) $request->input('per_page', 15));

        return TicketPriorityResource::collection($ticketPriorities);
    }

    public function store(StoreTicketPriorityRequest $request): TicketPriorityResource
    {
        return new TicketPriorityResource($this->ticketPriorityService->create($request->validated()));
    }

    public function show(TicketPriority $ticketPriority): TicketPriorityResource
    {
        return new TicketPriorityResource($ticketPriority);
    }

    public function update(UpdateTicketPriorityRequest $request, TicketPriority $ticketPriority): TicketPriorityResource
    {
        return new TicketPriorityResource($this->ticketPriorityService->update($ticketPriority, $request->validated()));
    }

    public function destroy(TicketPriority $ticketPriority): JsonResponse
    {
        $this->ticketPriorityService->delete($ticketPriority);

        return response()->json(['message' => 'Ticket priority deleted.']);
    }
}
