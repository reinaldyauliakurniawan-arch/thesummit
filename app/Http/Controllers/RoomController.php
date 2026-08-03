<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\GameRoom;
use App\Models\GamePlayer;
use App\Services\GameService;
use Illuminate\Support\Facades\Auth;

class RoomController extends Controller
{
    /**
     * Create a room and register all local players in one step (hotseat mode).
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'names'   => ['required', 'array', 'min:' . config('summit.min_players'), 'max:' . config('summit.max_players')],
            'names.*' => ['required', 'string', 'max:60'],
        ]);

        $room = GameRoom::create([
            'host_user_id' => Auth::id(),
        ]);

        foreach (array_values($validated['names']) as $index => $name) {
            GamePlayer::create([
                'game_room_id' => $room->id,
                'guest_name'   => trim($name),
                'turn_order'   => $index,
            ]);
        }

        return redirect()->route('rooms.lobby', $room);
    }

    public function start(Request $request, GameRoom $room, GameService $gameService)
    {
        if ($room->host_user_id !== Auth::id()) {
            abort(403);
        }

        if ($room->playerCount() < config('summit.min_players')) {
            return back()->withErrors(['msg' => 'Minimal 3 pemain.']);
        }

        $gameService->startGame($room);

        return redirect()->route('game.board', $room);
    }
}
