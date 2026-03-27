<?php

namespace App\Modules\Tickets\TicketSubcategories\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TicketSubcategoryResource;
use App\Models\TicketSubcategory;
use App\Modules\Tickets\TicketSubcategories\Requests\StoreTicketSubcategoryRequest;
use App\Modules\Tickets\TicketSubcategories\Requests\UpdateTicketSubcategoryRequest;
use App\Modules\Tickets\TicketSubcategories\TicketSubcategoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TicketSubcategoryController extends Controller
{
    public function __construct(private readonly TicketSubcategoryService $ticketSubcategoryService)
    {
    }

    public function index(Request $request)
    {
        $filters = [
            'search' => $request->input('search'),
            'ticket_category_id' => $request->input('ticket_category_id'),
        ];

        if ($request->has('is_active') && $request->input('is_active') !== '') {
            $filters['is_active'] = (bool) $request->input('is_active');
        }

        $ticketSubcategories = $this->ticketSubcategoryService->paginate($filters, (int) $request->input('per_page', 15));

        return TicketSubcategoryResource::collection($ticketSubcategories);
    }

    public function store(StoreTicketSubcategoryRequest $request): TicketSubcategoryResource
    {
        return new TicketSubcategoryResource(
            $this->ticketSubcategoryService->create($request->validated())->load('category:id,name')
        );
    }

    public function show(TicketSubcategory $ticketSubcategory): TicketSubcategoryResource
    {
        return new TicketSubcategoryResource($ticketSubcategory->load(['category:id,name', 'approver:id,name']));
    }

    public function update(UpdateTicketSubcategoryRequest $request, TicketSubcategory $ticketSubcategory): TicketSubcategoryResource
    {
        return new TicketSubcategoryResource($this->ticketSubcategoryService->update($ticketSubcategory, $request->validated()));
    }

    public function destroy(TicketSubcategory $ticketSubcategory): JsonResponse
    {
        $this->ticketSubcategoryService->delete($ticketSubcategory);

        return response()->json(['message' => 'Ticket subcategory deleted.']);
    }
}
