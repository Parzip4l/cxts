<?php

namespace App\Modules\Notifications\Web;

use App\Http\Controllers\Controller;
use App\Modules\Notifications\NotificationCenterService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function __construct(private readonly NotificationCenterService $notificationCenterService)
    {
    }

    public function index(Request $request): View
    {
        $user = $request->user();
        abort_unless($user !== null, 401);

        return view('modules.notifications.index', [
            'notifications' => $this->notificationCenterService->latestForUser($user, 30),
        ]);
    }
}
