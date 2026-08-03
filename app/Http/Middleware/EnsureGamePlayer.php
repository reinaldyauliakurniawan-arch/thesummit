<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\GameRoom;
use Illuminate\Support\Facades\Auth;

class EnsureGamePlayer
{
    /**
     * Ensure the authenticated user is the host of the given game room.
     * The game is played hotseat (one device, one host session) so only
     * the host needs to be authenticated to access the room.
     */
    public function handle(Request $request, Closure $next)
    {
        $room = $request->route('room');

        if (!$room instanceof GameRoom) {
            $room = GameRoom::findOrFail($room);
        }

        if ($room->host_user_id !== Auth::id()) {
            abort(403);
        }

        return $next($request);
    }
}
