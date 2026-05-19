<?php

namespace App\Modules\Assistant\Web;

use App\Http\Controllers\Controller;
use App\Modules\Assistant\AssistantService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AssistantController extends Controller
{
    public function __construct(
        private readonly AssistantService $assistantService,
    ) {
    }

    public function respond(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'max:1000'],
        ]);

        return response()->json(
            $this->assistantService->respond($request->user(), $validated['message'])
        );
    }
}
