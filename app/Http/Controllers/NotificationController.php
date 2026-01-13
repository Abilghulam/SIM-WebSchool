<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;

class NotificationController extends BaseController
{
    public function index(Request $request)
    {
        $user = $request->user();

        $tab = $request->get('tab', 'unread'); // unread|all
        $q = $user->notifications()->latest();

        if ($tab === 'unread') {
            $q->whereNull('read_at');
        }

        $notifications = $q->paginate(15)->withQueryString();

        return view('notifications.index', compact('notifications', 'tab'));
    }

    public function markAllRead(Request $request)
    {
        $request->user()->unreadNotifications->markAsRead();
        return back()->with('success', 'Semua notifikasi ditandai sudah dibaca.');
    }

    public function read(Request $request, string $id)
    {
        $n = $request->user()->notifications()->where('id', $id)->firstOrFail();
        $n->markAsRead();

        $url = data_get($n->data, 'action_url');
        return $url ? redirect($url) : back();
    }
}
