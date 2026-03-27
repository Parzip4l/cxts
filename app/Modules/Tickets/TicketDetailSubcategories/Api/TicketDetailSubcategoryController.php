<?php

namespace App\Modules\Tickets\TicketDetailSubcategories\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TicketDetailSubcategoryResource;
use App\Models\TicketDetailSubcategory;
use App\Modules\Tickets\TicketDetailSubcategories\Requests\StoreTicketDetailSubcategoryRequest;
use App\Modules\Tickets\TicketDetailSubcategories\Requests\UpdateTicketDetailSubcategoryRequest;
use App\Modules\Tickets\TicketDetailSubcategories\TicketDetailSubcategoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TicketDetailSubcategoryController extends Controller
{
    public function __construct(private readonly TicketDetailSubcategoryService $ticketDetailSubcategoryService)
    {
    }

    public function index(Request $request)
    {
        $filters = [
            'search' => $request->input('search'),
            'ticket_subcategory_id' => $request->input('ticket_subcategory_id'),
        ];

        if ($request->has('is_active') && $request->input('is_active') !== '') {
            $filters['is_active'] = (bool) $request->input('is_active');
        }

        return TicketDetailSubcategoryResource::collection(
            $this->ticketDetailSubcategoryService->paginate($filters, (int) $request->input('per_page', 15))
        );
    }

    public function store(StoreTicketDetailSubcategoryRequest $request): TicketDetailSubcategoryResource
    {
        return new TicketDetailSubcategoryResource(
            $this->ticketDetailSubcategoryService->create($request->validated())->load(['category:id,name,ticket_category_id', 'category.category:id,name'])
        );
    }

    public function show(TicketDetailSubcategory $ticketDetailSubcategory): TicketDetailSubcategoryResource
    {
        return new TicketDetailSubcategoryResource($ticketDetailSubcategory->load(['category:id,name,ticket_category_id', 'category.category:id,name', 'approver:id,name']));
    }

    public function update(UpdateTicketDetailSubcategoryRequest $request, TicketDetailSubcategory $ticketDetailSubcategory): TicketDetailSubcategoryResource
    {
        return new TicketDetailSubcategoryResource(
            $this->ticketDetailSubcategoryService->update($ticketDetailSubcategory, $request->validated())
        );
    }

    public function destroy(TicketDetailSubcategory $ticketDetailSubcategory): JsonResponse
    {
        $this->ticketDetailSubcategoryService->delete($ticketDetailSubcategory);

        return response()->json(['message' => 'Ticket detail subcategory deleted.']);
    }
}
