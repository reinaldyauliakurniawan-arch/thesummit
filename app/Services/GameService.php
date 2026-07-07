<?php

namespace App\Services;

use App\Enums\GameStatus;
use App\Enums\Level;
use App\Enums\Badge;
use App\Models\GameRoom;
use App\Models\GamePlayer;
use App\Models\ExpeditionCard;
use App\Models\GameTurn;
use App\Models\GameResult;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GameService
{
    /**
     * Draw an expedition card for the player.
     * Tries 3 strategies: (1) unplayed cards, (2) all except last 2, (3) full pool reset.
     * Throws only if no cards exist at all for the level+category combination.
     */
    public function drawCard(GamePlayer $player, int $turnNumber): ExpeditionCard
    {
        $level = $player->current_level;
        $category = ($turnNumber % 2 === 1) ? 'mindset' : 'skillset';

        $playedIds = $player->getPlayedCardIds();
        $lastTwoIds = $player->getLastTwoCardIds();

        // Strategy 1: Exclude all previously played cards
        $pool = ExpeditionCard::where('level', $level)
            ->where('kategori', $category)
            ->when(!empty($playedIds), function ($query) use ($playedIds) {
                $query->whereNotIn('id', $playedIds);
            });

        if ($pool->count() > 0) {
            return $pool->inRandomOrder()->firstOrFail();
        }

        // Strategy 2: Exclude only the last 2 played cards (PRD 9.7 reset)
        $pool = ExpeditionCard::where('level', $level)
            ->where('kategori', $category)
            ->when(!empty($lastTwoIds), function ($query) use ($lastTwoIds) {
                $query->whereNotIn('id', $lastTwoIds);
            });

        if ($pool->count() > 0) {
            return $pool->inRandomOrder()->firstOrFail();
        }

        // Strategy 3: Full pool — no exclusions (complete reset)
        $pool = ExpeditionCard::where('level', $level)
            ->where('kategori', $category);

        if ($pool->count() > 0) {
            return $pool->inRandomOrder()->firstOrFail();
        }

        // Absolute fallback: should never happen with proper seeder data
        Log::error("drawCard: No cards available for level={$level}, category={$category}");
        throw new \RuntimeException(
            "Tidak ada kartu ekspedisi untuk level {$level} ({$category})."
        );
    }

    /**
     * Apply the chosen option's effects to the player's stats.
     * Returns the effects array for display.
     */
    public function applyCardEffects(GamePlayer $player, ExpeditionCard $card, string $option): array
    {
        $effects = $card->getEffects($option);

        $player->mp = max(0, $player->mp + $effects['mp']);
        $player->sp = max(0, $player->sp + $effects['sp']);
        $player->tt = max(0, $player->tt + $effects['tt']);
        $player->save();

        return $effects;
    }

    /**
     * Roll the Risk Die (1-6).
     */
    public function rollRiskDie(): int
    {
        return rand(1, 6);
    }

    /**
     * Resolve a Risk Die roll into TT delta and optional dysfunction trigger.
     */
    public function resolveRiskDie(int $roll): array
    {
        $config = config('summit.risk_die');
        $ttDelta = 0;
        $dysfunction = null;

        if (in_array($roll, $config['dysfunction_range'])) {
            $ttDelta = $config['dysfunction_tt_penalty'];
            $dysfunctions = config('summit.dysfunctions');
            $dysfunction = array_rand($dysfunctions);
        } elseif (in_array($roll, $config['bonus_range'])) {
            $ttDelta = $config['bonus_tt_reward'];
        }

        return [
            'roll'          => $roll,
            'tt_delta'      => $ttDelta,
            'dysfunction'   => $dysfunction,
        ];
    }

    /**
     * Check if the player meets the Rope Bridge threshold for the next level.
     * Returns 'success' (auto-advances), 'fail', or null (already at summit).
     */
    public function checkRopeBridge(GamePlayer $player): ?string
    {
        $currentLevel = Level::from($player->current_level);
        $nextLevel = $currentLevel->next();

        if (!$nextLevel) {
            return null;
        }

        $thresholdKey = 'to_' . $nextLevel->value;

        if ($player->meetsThreshold($thresholdKey)) {
            $player->current_level = $nextLevel->value;
            $player->save();
            return 'success';
        }

        return 'fail';
    }

    /**
     * Check if the player has reached the final win condition (summit + threshold).
     */
    public function checkFinalWin(GamePlayer $player): bool
    {
        return $player->current_level === 'summit'
            && $player->meetsThreshold('final_win');
    }

    /**
     * Set the room into Final Round status if conditions are met.
     * Returns true if final round was just triggered by this call.
     */
    protected function triggerFinalRoundIfNeeded(GameRoom $room, GamePlayer $player): bool
    {
        if ($room->status !== GameStatus::InProgress) {
            return false;
        }

        if (!$this->checkFinalWin($player)) {
            return false;
        }

        $room->status = GameStatus::FinalRound;
        $room->final_round_started_at = now();
        $room->save();

        return true;
    }

    /**
     * Process a player's turn: draw card, apply effects, roll risk die (krisis),
     * record turn, check final round trigger, and advance to next player.
     */
    public function processTurn(GamePlayer $player, string $chosenOption, ?ExpeditionCard $card = null): array
    {
        return DB::transaction(function () use ($player, $chosenOption, $card) {
            $room = $player->room;
            $turnNumber = $player->turns()->count() + 1;

            if (!$card) {
                $card = $this->drawCard($player, $turnNumber);
            }

            // Apply chosen option effects
            $effects = $this->applyCardEffects($player, $card, $chosenOption);
            $mpEffect = $effects['mp'];
            $spEffect = $effects['sp'];
            $ttEffect = $effects['tt'];
            $extraEffect = $effects['extra'];

            // Roll Risk Die for krisis cards
            $riskDieResult = null;
            $dysfunction = null;

            if ($card->isKrisis()) {
                $riskDieResult = $this->rollRiskDie();
                $riskResult = $this->resolveRiskDie($riskDieResult);

                if ($riskResult['tt_delta'] !== 0) {
                    $player->tt = max(0, $player->tt + $riskResult['tt_delta']);
                    $player->save();
                    $ttEffect += $riskResult['tt_delta'];
                }

                $dysfunction = $riskResult['dysfunction'];
            }

            // Record the turn
            GameTurn::create([
                'game_room_id'           => $room->id,
                'game_player_id'         => $player->id,
                'expedition_card_id'     => $card->id,
                'chosen_option'          => $chosenOption,
                'risk_die_result'        => $riskDieResult,
                'mp_effect'              => $mpEffect,
                'sp_effect'              => $spEffect,
                        'tt_effect'              => $ttEffect,
                'extra_effect_applied'   => $extraEffect,
                'dysfunction_triggered'  => $dysfunction,
            ]);

            // Check if this triggers Final Round
            $triggeredFinal = $this->triggerFinalRoundIfNeeded($room, $player);

            // Advance to the next player
            $this->advanceTurn($room);

            return [
                'card'                 => $card,
                'effects'              => [
                    'mp' => $mpEffect,
                    'sp' => $spEffect,
                    'tt' => $ttEffect,
                ],
                'risk_die'             => $riskDieResult,
                'dysfunction'          => $dysfunction,
                'extra'                => $extraEffect,
                'triggered_final_round' => $triggeredFinal,
                'player'               => $player->fresh(),
            ];
        });
    }

    /**
     * Attempt the Rope Bridge check for a player.
     * Only triggers final round if not already in final_round (prevents double-trigger).
     */
    public function attemptRopeBridge(GamePlayer $player): array
    {
        return DB::transaction(function () use ($player) {
            $result = $this->checkRopeBridge($player);

            // Record the attempt on the latest turn
            $latestTurn = $player->turns()->latest()->first();
            if ($latestTurn) {
                $latestTurn->rope_bridge_attempted = true;
                $latestTurn->rope_bridge_success = ($result === 'success');
                $latestTurn->save();
            }

            // Only check final win if we haven't already entered final round.
            // This prevents double-trigger from processTurn() + attemptRopeBridge()
            // both calling checkFinalWin on the same player.
            $triggeredFinal = false;
            $room = $player->room;
            if ($room->status === GameStatus::InProgress) {
                $triggeredFinal = $this->triggerFinalRoundIfNeeded($room, $player);
            }

            return [
                'result'                => $result,
                'player'                => $player->fresh(),
                'triggered_final_round' => $triggeredFinal,
            ];
        });
    }

    /**
     * Advance the turn to the next active player.
     *
     * CRITICAL FIX (Bug #2): In Final Round mode, we only check if the next player
     * has taken their FINAL ROUND turn (i.e., a turn created AFTER final_round_started_at).
     * Previously this checked ALL turns including pre-final-round ones, causing
     * premature finishGame() on the very next player who had any prior turn.
     */
    public function advanceTurn(GameRoom $room): void
    {
        if ($room->status === GameStatus::Finished) {
            return;
        }

        $activePlayers = $room->players()
            ->where('is_active', true)
            ->orderBy('turn_order')
            ->get();

        if ($activePlayers->isEmpty()) {
            return;
        }

        // Determine the next player in rotation
        if (!$room->current_turn_player_id) {
            $next = $activePlayers->first();
        } else {
            $current = $activePlayers->firstWhere('id', $room->current_turn_player_id);
            $currentIndex = $current ? $activePlayers->search($current) : -1;
            $nextIndex = ($currentIndex + 1) % $activePlayers->count();
            $next = $activePlayers[$nextIndex];
        }

        // Set the next player's turn
        $room->current_turn_player_id = $next->id;
        $room->current_turn_started_at = now();
        $room->save();

        // In Final Round, check if this player already had their final-round turn.
        // Only count turns taken AFTER final_round_started_at to avoid counting
        // turns from before final round was triggered (PRD 9.5 compliance).
        if ($room->status === GameStatus::FinalRound) {
            $hasFinalTurn = $room->turns()
                ->where('game_player_id', $next->id)
                ->where('created_at', '>=', $room->final_round_started_at)
                ->exists();

            if ($hasFinalTurn) {
                $this->finishGame($room);
                return;
            }
        }

        // Notify the next player
        $next->user->notify(new \App\Notifications\TurnNotification($room, $next));
    }

    /**
     * Process a timed-out turn by auto-playing the safer option.
     */
    public function processTimeout(GameRoom $room): void
    {
        if (!in_array($room->status->value, ['in_progress', 'final_round'])) {
            return;
        }

        $currentPlayer = $room->currentPlayer;
        if (!$currentPlayer) {
            return;
        }

        $timeoutHours = config('summit.turn_timeout_hours', 24);
        if (
            $room->current_turn_started_at &&
            $room->current_turn_started_at->addHours($timeoutHours)->isFuture()
        ) {
            return;
        }

        // Auto-play: pick the option with higher TT (safer for team)
        $turnNumber = $currentPlayer->turns()->count() + 1;
        $card = $this->drawCard($currentPlayer, $turnNumber);
        $autoOption = ($card->opsi_b_tt >= $card->opsi_a_tt) ? 'B' : 'A';

        $this->processTurn($currentPlayer, $autoOption, $card);
    }

    /**
     * Finish the game: calculate scores, assign badges, rank players.
     */
    public function finishGame(GameRoom $room): void
    {
        DB::transaction(function () use ($room) {
            $room->status = GameStatus::Finished;
            $room->current_turn_player_id = null;
            $room->current_turn_started_at = null;
            $room->save();

            // Calculate scores for all active players
            $players = $room->players()
                ->where('is_active', true)
                ->get()
                ->map(function ($player) {
                    $player->score = $player->calculateScore();
                    return $player;
                });

            // Sort: The Carrier (summit + TT>=8) first, then by score, then TT, then earlier turn_order
            $sorted = $players->sortByDesc(function ($player) {
                $isCarrier = ($player->current_level === 'summit' && $player->tt >= 8) ? '1' : '0';
                return $isCarrier . '.' . $player->score . '.' . $player->tt . '.' . str_pad(99 - $player->turn_order, 2, '0', STR_PAD_LEFT);
            });

            $rank = 1;
            foreach ($sorted as $player) {
                // Determine badge
                if ($player->current_level === 'summit' && $player->tt >= 8) {
                    $badge = 'the_carrier';
                } elseif ($player->current_level === 'summit' && $player->tt < 8) {
                    $badge = 'solo_peak';
                } else {
                    $badge = 'none';
                }

                GameResult::create([
                    'game_room_id'   => $room->id,
                    'game_player_id' => $player->id,
                    'final_level'    => $player->current_level,
                    'final_mp'       => $player->mp,
                    'final_sp'       => $player->sp,
                    'final_tt'       => $player->tt,
                    'final_score'    => $player->score,
                    'badge'          => $badge,
                    'rank'           => $rank,
                ]);

                $rank++;
            }

            // Notify all players
            foreach ($room->players as $gamePlayer) {
                $gamePlayer->user->notify(
                    new \App\Notifications\GameFinishedNotification($room, $gamePlayer)
                );
            }
        });
    }

    /**
     * Start the game: shuffle turn order and set first player.
     */
    public function startGame(GameRoom $room): void
    {
        DB::transaction(function () use ($room) {
            $players = $room->players()
                ->where('is_active', true)
                ->inRandomOrder()
                ->get();

            foreach ($players as $index => $player) {
                $player->turn_order = $index + 1;
                $player->save();
            }

            $room->status = GameStatus::InProgress;
            $room->current_turn_player_id = $players->first()->id;
            $room->current_turn_started_at = now();
            $room->save();

            $players->first()->user->notify(
                new \App\Notifications\TurnNotification($room, $players->first())
            );
        });
    }
}