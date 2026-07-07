<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\GameRoom;
use Illuminate\Support\Facades\Auth;

class EnsureGamePlayer
{
    /**
     * Ensure the authenticated user is a player in the given game room.
     */
    public function handle(Request $request, Closure $next)
    {
        $room = $request->route('room');

        if (!$room instanceof GameRoom) {
            $room = GameRoom::findOrFail($room);
        }

        if (!$room->players()->where('user_id', Auth::id())->exists()) {
            abort(403);
        }

        return $next($request);
    }
}