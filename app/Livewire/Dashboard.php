<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\GameRoom;

class Dashboard extends Component
{
    public function render()
    {
        $user = Auth::user();

        $waitingRooms = GameRoom::where('status', 'waiting')
            ->whereHas('players', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->with(['host', 'players.user'])
            ->latest()
            ->get();

        $activeGames = \App\Models\GamePlayer::where('user_id', $user->id)
            ->whereHas('room', function ($query) {
                $query->whereIn('status', ['in_progress', 'final_round', 'finished']);
            })
            ->with('room')
            ->get();

        $unreadNotifications = $user->unreadNotifications()->take(10)->get();

        return view('livewire.dashboard', [
            'wr' => $waitingRooms,
            'ag' => $activeGames,
            'un' => $unreadNotifications,
        ])->layout('layouts.app');
    }

    public function markRead($id)
    {
        Auth::user()
            ->unreadNotifications()
            ->where('id', $id)
            ->update(['read_at' => now()]);
    }
}