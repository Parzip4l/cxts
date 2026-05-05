<?php

namespace App\Modules\Notifications\Web;

use App\Http\Controllers\Controller;
use App\Modules\Notifications\NotificationCenterService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
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
            'unreadCount' => $this->notificationCenterService->unreadCountForUser($user),
        ]);
    }

    public function open(Request $request, string $notificationKey): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user !== null, 401);

        $notification = $this->notificationCenterService->findForUser($user, $notificationKey);

        if ($notification === null) {
            return redirect()
                ->route('notifications.center')
                ->with('error', 'Notification tidak ditemukan atau sudah tidak relevan.');
        }

        $this->notificationCenterService->markRead($user, $notificationKey);

        return redirect()->to($notification['url']);
    }

    public function markRead(Request $request, string $notificationKey): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user !== null, 401);

        $this->notificationCenterService->markRead($user, $notificationKey);

        return back()->with('success', 'Notification marked as read.');
    }

    public function acknowledge(Request $request, string $notificationKey): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user !== null, 401);

        $this->notificationCenterService->acknowledge($user, $notificationKey);

        return back()->with('success', 'Notification acknowledged.');
    }

    public function markAllRead(Request $request): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user !== null, 401);

        $count = $this->notificationCenterService->markAllRead($user);

        return back()->with('success', "{$count} notification(s) marked as read.");
    }
}
