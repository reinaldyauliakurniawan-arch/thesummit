<?php

namespace App\Services;

use App\Models\GameRoom;
use App\Models\GamePlayer;
use App\Models\GameTurn;
use App\Models\Promise;
use App\Models\Vote;
use Illuminate\Support\Facades\Log;

class SocialEngine
{
    /**
     * Create a promise between two players.
     */
    public function createPromise(
        GameRoom $room,
        GamePlayer $promiser,
        GamePlayer $recipient,
        string $type,
        string $description,
    ): Promise {
        return Promise::create([
            'game_room_id'        => $room->id,
            'promiser_player_id'   => $promiser->id,
            'recipient_player_id' => $recipient->id,
            'promise_type'         => $type,
            'description'          => $description,
        ]);
    }

    /**
     * Mark a promise as fulfilled.
     */
    public function fulfillPromise(Promise $promise): void
    {
        $promise->is_fulfilled = true;
        $promise->resolved_at = now();
        $promise->save();

        // Update promiser's reputation
        $promiser = $promise->promiser;
        $promiser->promises_kept = ($promiser->promises_kept ?? 0) + 1;
        $promiser->reputation = ($promiser->reputation ?? 0) + 2;
        $promiser->save();
    }

    /**
     * Mark a promise as broken.
     */
    public function breakPromise(Promise $promise): void
    {
        $promise->is_broken = true;
        $promise->resolved_at = now();
        $promise->save();

        // Update promiser's reputation
        $promiser = $promise->promiser;
        $promiser->promises_broken = ($promiser->promises_broken ?? 0) + 1;
        $promiser->reputation = ($promiser->reputation ?? 0) - 3;
        $promiser->tt = max(0, $promiser->tt - 1);
        $promiser->save();
    }

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
     * Get active promises for a room.
     */
    public function getActivePromises(GameRoom $room): \Illuminate\Database\Eloquent\Collection
    {
        return Promise::where('game_room_id', $room->id)
            ->active()
            ->with(['promiser.user', 'recipient.user'])
            ->get();
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

    /**
     * Check for stale promises — tracked for analytics/reputation only.
     *
     * Per PRD Feature 5: Promises are purely social. They are NEVER auto-resolved
     * by the system. Instead, lingering unfulfilled promises contribute to
     * a reputation decay signal that is surfaced to players but is non-blocking.
     *
     * The old auto-break behavior has been removed. This method now only
     * applies a soft reputation nudge (logged, non-mechanical) for promises
     * that have been active for 5+ turns without resolution.
     *
     * @return array List of stale promise IDs and their decay applied (for logging)
     */
    public function checkExpiredPromises(GameRoom $room, int $currentTurnCount): array
    {
        $activePromises = Promise::where('game_room_id', $room->id)
            ->active()
            ->get();

        $stalePromises = [];

        foreach ($activePromises as $promise) {
            $turnsSinceCreation = $room->turns()
                ->where('created_at', '>=', $promise->created_at)
                ->count();

            if ($turnsSinceCreation >= 5) {
                // Soft reputation decay: non-blocking, logged for analytics
                // This replaces the old auto-break that forcibly resolved promises.
                // The promise remains active — it's up to the players to fulfill or break it.
                Log::info("social_engine:stale_promise", [
                    'promise_id' => $promise->id,
                    'promiser' => $promise->promiser_player_id,
                    'turns_stale' => $turnsSinceCreation,
                    'event' => 'reputation_decay_signal',
                    'mechanism' => 'non_blocking',
                    'replaced' => 'auto_break_removed',
                ]);

                $stalePromises[] = [
                    'promise_id' => $promise->id,
                    'turns_stale' => $turnsSinceCreation,
                    'decay_applied' => 'reputation_signal_only',
                ];
            }
        }

        return $stalePromises;
    }
}
