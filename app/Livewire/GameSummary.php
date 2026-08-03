<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\GameRoom;
use App\Models\GameResult;
use Illuminate\Support\Facades\Auth;

class GameSummary extends Component
{
    public GameRoom $room;

    public function mount(GameRoom $room): void
    {
        if ($room->host_user_id !== Auth::id()) {
            abort(403);
        }

        $this->room = $room->load([
            'results.player.user',
            'results.leadershipProfile',
            'results.realWorldChallenge',
            'players.user',
        ]);
    }

    public function markChallengeCompleted(int $resultId): void
    {
        $result = GameResult::find($resultId);
        if (!$result || $result->game_room_id !== $this->room->id) return;

        $challenge = $result->realWorldChallenge;
        if ($challenge) {
            $challenge->is_completed = true;
            $challenge->completed_at = now();
            $challenge->save();
        }
    }

    public function render()
    {
        $results = $this->room->results()
            ->with(['player.user', 'leadershipProfile', 'realWorldChallenge'])
            ->orderBy('rank')
            ->get();

        $turns = $this->room->turns()
            ->with(['card', 'player.user'])
            ->orderBy('created_at')
            ->get();

        return view('livewire.game-summary', compact('results', 'turns'))
            ->layout('layouts.app');
    }
}
