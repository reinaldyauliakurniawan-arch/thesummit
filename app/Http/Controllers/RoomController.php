<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\GameRoom;
use App\Models\GamePlayer;
use App\Services\GameService;
use Illuminate\Support\Facades\Auth;

class RoomController extends Controller
{
    public function index()
    {
        $waitingRooms = GameRoom::where('status', 'waiting')
            ->whereHas('players', function ($query) {
                $query->where('user_id', Auth::id());
            })
            ->with(['host', 'players.user'])
            ->latest()
            ->get();

        $activeGames = GamePlayer::where('user_id', Auth::id())
            ->whereHas('room', function ($query) {
                $query->whereIn('status', ['in_progress', 'final_round']);
            })
            ->with('room')
            ->get()
            ->pluck('room')
            ->unique('id');

        return view('room.index', compact('waitingRooms', 'activeGames'));
    }

    public function store(Request $request)
    {
        $room = GameRoom::create([
            'host_user_id' => Auth::id(),
        ]);

        GamePlayer::create([
            'game_room_id' => $room->id,
            'user_id'      => Auth::id(),
            'turn_order'   => 0,
        ]);

        return redirect()->route('rooms.lobby', $room);
    }

    public function join(Request $request, string $code)
    {
        $room = GameRoom::where('code', $code)->firstOrFail();

        if ($room->status !== 'waiting') {
            return back()->withErrors(['msg' => 'Room sudah dimulai.']);
        }

        if ($room->players()->where('user_id', Auth::id())->exists()) {
            return redirect()->route('rooms.lobby', $room);
        }

        if ($room->playerCount() >= config('summit.max_players')) {
            return back()->withErrors(['msg' => 'Room penuh.']);
        }

        GamePlayer::create([
            'game_room_id' => $room->id,
            'user_id'      => Auth::id(),
            'turn_order'   => 0,
        ]);

        return redirect()->route('rooms.lobby', $room);
    }

    public function leave(Request $request, GameRoom $room)
    {
        if ($room->status !== 'waiting') {
            return back()->withErrors(['msg' => 'Tidak bisa keluar.']);
        }

        $player = $room->players()->where('user_id', Auth::id())->first();
        if ($player) {
            $player->delete();
        }

        return redirect()->route('rooms.index');
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