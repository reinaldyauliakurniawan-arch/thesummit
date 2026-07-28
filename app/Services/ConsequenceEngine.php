<?php

namespace App\Services;

use App\Models\GameRoom;
use App\Models\GamePlayer;
use App\Models\GameTurn;
use App\Models\Consequence;
use App\Models\CrossPlayerEffect;
use App\Models\PlayerBehavior;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ConsequenceEngine
{
    /**
     * Create consequences from a card choice (delayed + conditional).
     */
    public function createConsequences(
        GamePlayer $player,
        GameTurn $turn,
        array $delayedEffects,
        array $conditionalEffects,
    ): array {
        $created = [];
        $room = $player->room;
        $currentTurnNumber = $room->turns()->count();

        foreach ($delayedEffects as $effect) {
            $consequence = Consequence::create([
                'game_room_id'        => $room->id,
                'game_player_id'      => $player->id,
                'originating_turn_id' => $turn->id,
                'effect_type'         => 'delayed',
                'trigger_type'        => $effect['trigger_type'] ?? 'after_rounds',
                'trigger_value'       => ($currentTurnNumber + ($effect['after_rounds'] ?? 2)),
                'stat'                => $effect['stat'] ?? 'mp',
                'delta'               => $effect['delta'] ?? 0,
                'description'        => $effect['description'] ?? 'Efek tertunda',
                'is_hidden'           => $effect['is_hidden'] ?? false,
            ]);
            $created[] = $consequence;
        }

        foreach ($conditionalEffects as $effect) {
            $condition = json_encode([
                'stat'     => $effect['condition_stat'] ?? 'mp',
                'operator' => $effect['condition_operator'] ?? '>=',
                'value'    => $effect['condition_value'] ?? 0,
            ]);

            $consequence = Consequence::create([
                'game_room_id'        => $room->id,
                'game_player_id'      => $player->id,
                'originating_turn_id' => $turn->id,
                'effect_type'         => 'conditional',
                'trigger_type'        => 'on_condition',
                'trigger_condition'    => $condition,
                'stat'                => $effect['stat'] ?? 'tt',
                'delta'               => $effect['delta'] ?? 0,
                'description'        => $effect['description'] ?? 'Efek kondisional',
                'is_hidden'           => $effect['is_hidden'] ?? false,
            ]);
            $created[] = $consequence;
        }

        return $created;
    }

    /**
     * Process all pending consequences for a room at the start of each turn.
     */
    public function processPendingConsequences(GameRoom $room): array
    {
        $currentRound = $room->turns()->count();
        $triggered = [];

        $pending = Consequence::where('game_room_id', $room->id)
            ->where('is_triggered', false)
            ->get();

        foreach ($pending as $consequence) {
            if ($consequence->shouldTrigger($currentRound)) {
                $this->triggerConsequence($consequence);
                $triggered[] = $consequence;
            }
        }

        return $triggered;
    }

    /**
     * Trigger a single consequence: apply its delta to the player.
     */
    public function triggerConsequence(Consequence $consequence): void
    {
        DB::transaction(function () use ($consequence) {
            $player = $consequence->player;
            $stat = $consequence->stat;

            if (in_array($stat, ['mp', 'sp', 'tt'])) {
                $player->$stat = max(0, $player->$stat + $consequence->delta);
            } elseif (in_array($stat, ['reputation', 'resources'])) {
                $player->$stat = $player->$stat + $consequence->delta;
            }

            $player->save();

            $consequence->is_triggered = true;
            $consequence->triggered_at = now();
            $consequence->save();
        });
    }

    /**
     * Get all active (pending) consequences for a room, optionally filtered by player.
     */
    public function getActiveConsequences(GameRoom $room, ?int $playerId = null): \Illuminate\Database\Eloquent\Collection
    {
        $query = Consequence::where('game_room_id', $room->id)
            ->where('is_triggered', false)
            ->with(['player.user', 'originatingTurn.card']);

        if ($playerId) {
            $query->where('game_player_id', $playerId);
        }

        return $query->get();
    }

    /**
     * Get consequences that are visible to the current player (not hidden).
     */
    public function getVisibleConsequences(GameRoom $room, int $playerId): \Illuminate\Database\Eloquent\Collection
    {
        return Consequence::where('game_room_id', $room->id)
            ->where('is_triggered', false)
            ->where(function ($q) use ($playerId) {
                $q->where('game_player_id', $playerId)
                  ->where('is_hidden', false);
            })
            ->with(['player.user'])
            ->get();
    }
}
