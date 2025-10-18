<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LocalNotification;

class NotificationController extends Controller
{
    public function markAllRead(Request $req)
    {
        $user = $req->user();
        if (! $user) return response('', 401);
        LocalNotification::where('user_id', $user->id)->where('is_read', false)->update(['is_read' => true]);
        return response('', 204);
    }
}
