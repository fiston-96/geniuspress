<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

/**
 * Notifications de la personne connectée : administration (/admin/notifications) ou
 * espace client (/compte/notifications). La route indique le public (->defaults('audience', …)).
 */
class NotificationController extends Controller
{
    private const AUDIENCES = [
        'admin' => ['layout' => 'layouts.admin', 'prefix' => 'admin.notifications'],
        'customer' => ['layout' => 'platform.layouts.customer', 'prefix' => 'platform.customer.notifications'],
    ];

    public function index(Request $request)
    {
        $audience = $this->audience($request);

        return view('notifications.index', [
            'layout' => $audience['layout'],
            'prefix' => $audience['prefix'],
            'notifications' => $request->user()->notifications()->paginate(20),
            'unread' => $request->user()->unreadNotifications()->count(),
        ]);
    }

    /** Ouvre une notification : marquée comme lue, puis sa page (adresses du site seulement). */
    public function open(Request $request, string $id)
    {
        $audience = $this->audience($request);
        $notification = $request->user()->notifications()->whereKey($id)->firstOrFail();
        $notification->markAsRead();
        $url = (string) ($notification->data['url'] ?? '');

        return str_starts_with($url, url('/')) ? redirect()->to($url) : redirect()->route($audience['prefix'].'.index');
    }

    public function readAll(Request $request)
    {
        $request->user()->unreadNotifications()->update(['read_at' => now()]);

        return back()->with('status', __('Toutes les notifications sont marquées comme lues.'));
    }

    private function audience(Request $request): array
    {
        return self::AUDIENCES[$request->route('audience')] ?? abort(404);
    }
}
