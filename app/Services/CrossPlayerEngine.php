<?php

namespace App\Services;

use App\Models\GameRoom;
use App\Models\GamePlayer;
use App\Models\GameTurn;
use App\Models\CrossPlayerEffect;
use Illuminate\Support\Facades\DB;

class CrossPlayerEngine
{
    /**
     * Apply cross-player effects from a card choice.
     * Returns array of applied effects.
     */
    public function applyCrossPlayerEffects(
        GameRoom $room,
        GamePlayer $sourcePlayer,
        GameTurn $turn,
        array $crossPlayerData,
    ): array {
        $applied = [];

        foreach ($crossPlayerData as $effect) {
            $targetType = $effect['target_type'] ?? 'other_players';
            $stat = $effect['stat'] ?? 'tt';
            $delta = $effect['delta'] ?? 0;
            $description = $effect['description'] ?? 'Efek tim';
            $effectType = $effect['effect_type'] ?? 'bonus';

            $targets = $this->resolveTargets($room, $sourcePlayer, $targetType);

            foreach ($targets as $target) {
                // Apply the stat change
                $this->applyStatChange($target, $stat, $delta);

                // Record the cross-player effect
                CrossPlayerEffect::create([
                    'game_room_id'       => $room->id,
                    'source_player_id'   => $sourcePlayer->id,
                    'target_player_id'   => $target->id,
                    'game_turn_id'       => $turn->id,
                    'stat'               => $stat,
                    'delta'              => $delta,
                    'description'        => $description,
                    'effect_type'         => $effectType,
                ]);

                $applied[] = [
                    'target'     => $target->user->name,
                    'stat'       => $stat,
                    'delta'      => $delta,
                    'description' => $description,
                ];
            }
        }

        return $applied;
    }

    /**
     * Determine which players are affected by a cross-player effect.
     */
    protected function resolveTargets(GameRoom $room, GamePlayer $source, string $targetType): array
    {
        return match ($targetType) {
            'all_players' => $room->players()->where('is_active', true)->get()->all(),
            'other_players' => $room->players()
                ->where('is_active', true)
                ->where('id', '!=', $source->id)
                ->get()
                ->all(),
            'lowest_tt' => $this->getLowestStatPlayer($room, $source, 'tt'),
            'lowest_mp' => $this->getLowestStatPlayer($room, $source, 'mp'),
            'lowest_sp' => $this->getLowestStatPlayer($room, $source, 'sp'),
            'random_player' => $this->getRandomOtherPlayer($room, $source),
            'adjacent_players' => $this->getAdjacentPlayers($room, $source),
            default => $room->players()->where('is_active', true)->where('id', '!=', $source->id)->get()->all(),
        };
    }

    /**
     * Get the player(s) with the lowest value of a given stat.
     */
    protected function getLowestStatPlayer(GameRoom $room, GamePlayer $source, string $stat): array
    {
        $player = $room->players()
            ->where('is_active', true)
            ->where('id', '!=', $source->id)
            ->orderBy($stat, 'asc')
            ->first();

        return $player ? [$player] : [];
    }

    /**
     * Get a random other player.
     */
    protected function getRandomOtherPlayer(GameRoom $room, GamePlayer $source): array
    {
        $players = $room->players()
            ->where('is_active', true)
            ->where('id', '!=', $source->id)
            ->inRandomOrder()
            ->first();

        return $players ? [$players] : [];
    }

    /**
     * Get players adjacent in turn order.
     */
    protected function getAdjacentPlayers(GameRoom $room, GamePlayer $source): array
    {
        $allPlayers = $room->players()
            ->where('is_active', true)
            ->orderBy('turn_order')
            ->get()
            ->values();

        $currentIndex = $allPlayers->search(fn ($p) => $p->id === $source->id);
        $adjacent = [];

        if ($currentIndex !== false) {
            $prevIndex = ($currentIndex - 1 + $allPlayers->count()) % $allPlayers->count();
            $nextIndex = ($currentIndex + 1) % $allPlayers->count();

            $adjacent[] = $allPlayers[$prevIndex];
            if ($allPlayers->count() > 2) {
                $adjacent[] = $allPlayers[$nextIndex];
            }
        }

        return $adjacent;
    }

    /**
     * Apply a stat change to a target player.
     */
    protected function applyStatChange(GamePlayer $target, string $stat, int $delta): void
    {
        $allowedStats = ['mp', 'sp', 'tt', 'reputation', 'resources', 'flexibility'];
        if ($stat === '' || !in_array($stat, $allowedStats, true)) {
            return;
        }

        if (in_array($stat, ['mp', 'sp', 'tt'], true)) {
            $target->$stat = max(0, $target->$stat + $delta);
        } else {
            $target->$stat = $target->$stat + $delta;
        }
        $target->save();
    }

    /**
     * Apply shared penalty when a player fails a crisis check.
     */
    public function applySharedPenalty(GameRoom $room, GamePlayer $failedPlayer, int $ttPenalty): void
    {
        $otherPlayers = $room->players()
            ->where('is_active', true)
            ->where('id', '!=', $failedPlayer->id)
            ->get();

        $halfPenalty = (int) round($ttPenalty / 2);

        foreach ($otherPlayers as $player) {
            $player->tt = max(0, $player->tt + $halfPenalty);
            $player->save();
        }
    }
}
