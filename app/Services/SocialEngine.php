<?php

namespace App\Services;

use App\Models\GameRoom;
use App\Models\GamePlayer;
use App\Models\GameTurn;
use App\Models\Vote;

class SocialEngine
{
    /**
     * Create a vote event.
     */
    public function createVote(
        GameRoom $room,
        GamePlayer $triggeringPlayer,
        string $topic,
        string $description,
        string $type,
        array $options,
        int $expiresInSeconds = 120,
    ): Vote {
        return Vote::create([
            'game_room_id'        => $room->id,
            'triggering_player_id' => $triggeringPlayer->id,
            'vote_topic'          => $topic,
            'vote_description'    => $description,
            'vote_type'            => $type,
            'options'              => $options,
            'expires_at'           => now()->addSeconds($expiresInSeconds),
        ]);
    }

    /**
     * Cast a vote.
     */
    public function castVote(Vote $vote, GamePlayer $player, string $choice): void
    {
        if ($vote->hasVoted($player->id)) {
            return;
        }

        $vote->castVote($player->id, $choice);

        // Check if all players have voted
        $totalPlayers = $vote->room->players()->where('is_active', true)->count();
        if ($vote->voteCount() >= $totalPlayers) {
            $this->resolveVote($vote);
        }
    }

    /**
     * Resolve a vote and apply the result.
     */
    public function resolveVote(Vote $vote): string
    {
        $winner = $vote->getWinner();
        $vote->result = $winner;
        $vote->is_resolved = true;
        $vote->save();

        return $winner;
    }

    /**
     * Get active votes for a room.
     */
    public function getActiveVotes(GameRoom $room): \Illuminate\Database\Eloquent\Collection
    {
        return Vote::where('game_room_id', $room->id)
            ->active()
            ->with(['triggeringPlayer.user'])
            ->get();
    }
}
