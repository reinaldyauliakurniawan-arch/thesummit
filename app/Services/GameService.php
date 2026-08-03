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
use App\Models\LeadershipProfile;
use App\Models\RealWorldChallenge;
use App\Notifications\TurnNotification;
use App\Notifications\GameFinishedNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GameService
{
    protected ConsequenceEngine $consequenceEngine;
    protected CrossPlayerEngine $crossPlayerEngine;
    protected BehaviorTracker $behaviorTracker;
    protected SocialEngine $socialEngine;
    protected ReflectionEngine $reflectionEngine;
    protected ChallengeGenerator $challengeGenerator;
    protected ChallengeFollowUpService $challengeFollowUp;

    public function __construct()
    {
        $this->consequenceEngine = app(ConsequenceEngine::class);
        $this->crossPlayerEngine = app(CrossPlayerEngine::class);
        $this->behaviorTracker = app(BehaviorTracker::class);
        $this->socialEngine = app(SocialEngine::class);
        $this->reflectionEngine = app(ReflectionEngine::class);
        $this->challengeGenerator = app(ChallengeGenerator::class);
        $this->challengeFollowUp = app(ChallengeFollowUpService::class);
    }

    /**
     * Draw an expedition card for the player.
     * Tries 3 strategies: (1) unplayed cards, (2) all except last 2, (3) full pool reset.
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

        // Strategy 2: Exclude only the last 2 played cards
        $pool = ExpeditionCard::where('level', $level)
            ->where('kategori', $category)
            ->when(!empty($lastTwoIds), function ($query) use ($lastTwoIds) {
                $query->whereNotIn('id', $lastTwoIds);
            });

        if ($pool->count() > 0) {
            return $pool->inRandomOrder()->firstOrFail();
        }

        // Strategy 3: Full pool reset
        $card = ExpeditionCard::where('level', $level)
            ->where('kategori', $category)
            ->inRandomOrder()
            ->first();

        if ($card) {
            return $card;
        }

        Log::error("drawCard: No cards for level={$level}, category={$category}. Run seeder.");
        throw new \RuntimeException("Tidak ada kartu untuk {$level}/{$category}. Jalankan seeder.");
    }

    /**
     * Apply the chosen option's effects to the player's stats.
     * Now includes reputation, resources, flexibility, and hidden info.
     */
    public function applyCardEffects(GamePlayer $player, ExpeditionCard $card, string $option): array
    {
        $effects = $card->getEffects($option);
        $effects['mp'] = $effects['mp'] ?? 0;
        $effects['sp'] = $effects['sp'] ?? 0;
        $effects['tt'] = $effects['tt'] ?? 0;
        $effects['reputation'] = $effects['reputation'] ?? 0;
        $effects['resources'] = $effects['resources'] ?? 0;
        $effects['flexibility'] = $effects['flexibility'] ?? 0;

        // Apply core stats (floored at 0)
        $player->mp = max(0, $player->mp + $effects['mp']);
        $player->sp = max(0, $player->sp + $effects['sp']);
        $player->tt = max(0, $player->tt + $effects['tt']);

        // Apply new v2 stats (no floor for reputation/flexibility)
        $player->reputation = $player->reputation + $effects['reputation'];
        $player->resources = max(0, $player->resources + $effects['resources']);
        $player->flexibility = $player->flexibility + $effects['flexibility'];
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
            'roll'        => $roll,
            'tt_delta'    => $ttDelta,
            'dysfunction' => $dysfunction,
        ];
    }

    /**
     * Check if the player meets the Rope Bridge threshold for the next level.
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
     * Check if the player has reached the final win condition.
     */
    public function checkFinalWin(GamePlayer $player): bool
    {
        return $player->current_level === 'summit'
            && $player->meetsThreshold('final_win');
    }

    /**
     * Set the room into Final Round status if conditions are met.
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
     * record turn, create consequences, apply cross-player effects, track behaviors,
     * check final round trigger, and advance to next player.
     */
    public function processTurn(GamePlayer $player, string $chosenOption, ?ExpeditionCard $card = null): array
    {
        return DB::transaction(function () use ($player, $chosenOption, $card) {
            $room = $player->room;
            $turnNumber = $player->turns()->count() + 1;

            if (!$card) {
                $card = $this->drawCard($player, $turnNumber);
            }

            // ── V2: Process pending consequences BEFORE the turn ──
            $triggeredConsequences = $this->consequenceEngine->processPendingConsequences($room);

            // ── V2: Check expired promises ──
            $this->socialEngine->checkExpiredPromises($room, $turnNumber);

            // Apply chosen option effects (including v2 stats)
            $effects = $this->applyCardEffects($player, $card, $chosenOption);
            $mpEffect = $effects['mp'];
            $spEffect = $effects['sp'];
            $ttEffect = $effects['tt'];
            $extraEffect = $effects['extra'];
            $repEffect = $effects['reputation'];
            $resEffect = $effects['resources'];
            $flexEffect = $effects['flexibility'];

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

                // ── V2: Shared penalty on dysfunction trigger ──
                if ($dysfunction) {
                    $this->crossPlayerEngine->applySharedPenalty($room, $player, $riskResult['tt_delta']);
                }
            }

            // ── V2: Create delayed/conditional consequences ──
            $delayedEffects = $card->getDelayedEffects($chosenOption);
            $conditionalEffects = $card->getConditionalEffects($chosenOption);

            // Record the turn first (we need the turn ID for consequences)
            $turn = GameTurn::create([
                'game_room_id'          => $room->id,
                'game_player_id'        => $player->id,
                'expedition_card_id'    => $card->id,
                'chosen_option'         => $chosenOption,
                'risk_die_result'       => $riskDieResult,
                'mp_effect'             => $mpEffect,
                'sp_effect'             => $spEffect,
                'tt_effect'             => $ttEffect,
                'extra_effect_applied'  => $extraEffect,
                'dysfunction_triggered' => $dysfunction,
                'was_hidden'            => $card->hasHiddenInfo($chosenOption),
                'hidden_info_shown'     => $card->getHiddenInfoReveal(),
                'reputation_effect'     => $repEffect,
                'resources_effect'      => $resEffect,
                'flexibility_effect'    => $flexEffect,
            ]);

            // Create consequences
            $createdConsequences = $this->consequenceEngine->createConsequences(
                $player, $turn, $delayedEffects, $conditionalEffects
            );
            $turn->consequences_created = collect($createdConsequences)->pluck('id')->toArray();
            $turn->save();

            // ── V2: Apply cross-player effects ──
            $crossPlayerData = $card->getCrossPlayerEffects($chosenOption);
            $appliedCrossEffects = [];
            if (!empty($crossPlayerData)) {
                $appliedCrossEffects = $this->crossPlayerEngine->applyCrossPlayerEffects(
                    $room, $player, $turn, $crossPlayerData
                );
                $turn->cross_player_effects = $appliedCrossEffects;
                $turn->save();
            }

            // ── V2: Track behaviors ──
            $cardData = [
                'behavior_tags' => $card->getBehaviorTags($chosenOption),
                'is_krisis'     => $card->isKrisis(),
                'cross_player'  => $crossPlayerData,
                'option_text'   => $chosenOption === 'A' ? $card->opsi_a_teks : $card->opsi_b_teks,
                'effects_a'     => [
                    'mp' => $card->opsi_a_mp,
                    'sp' => $card->opsi_a_sp,
                    'tt' => $card->opsi_a_tt,
                ],
                'effects_b'     => [
                    'mp' => $card->opsi_b_mp,
                    'sp' => $card->opsi_b_sp,
                    'tt' => $card->opsi_b_tt,
                ],
                'lra_tags'      => [
                    'A' => $card->getLraTags('A'),
                    'B' => $card->getLraTags('B'),
                ],
                'card_narrative' => $card->teks_situasi,
            ];
            $trackedBehaviors = $this->behaviorTracker->trackBehaviors($turn, $player, $cardData);
            $turn->behavior_data = $trackedBehaviors;
            $turn->save();

            // Check if this triggers Final Round
            $triggeredFinal = $this->triggerFinalRoundIfNeeded($room, $player);

            // Advance to the next player
            $this->advanceTurn($room);

            return [
                'card'                   => $card,
                'effects'                => [
                    'mp'         => $mpEffect,
                    'sp'         => $spEffect,
                    'tt'         => $ttEffect,
                    'reputation' => $repEffect,
                    'resources'  => $resEffect,
                    'flexibility' => $flexEffect,
                ],
                'risk_die'               => $riskDieResult,
                'dysfunction'            => $dysfunction,
                'extra'                  => $extraEffect,
                'triggered_final_round'  => $triggeredFinal,
                'player'                 => $player->fresh(),
                // V2 additions
                'triggered_consequences' => $triggeredConsequences,
                'created_consequences'    => $createdConsequences,
                'cross_player_effects'   => $appliedCrossEffects,
                'tracked_behaviors'       => $trackedBehaviors,
                'was_hidden'             => $card->hasHiddenInfo($chosenOption),
                'hidden_info'            => $card->getHiddenInfoReveal(),
            ];
        });
    }

    /**
     * Attempt the Rope Bridge check for a player.
     */
    public function attemptRopeBridge(GamePlayer $player): array
    {
        return DB::transaction(function () use ($player) {
            $result = $this->checkRopeBridge($player);

            $latestTurn = $player->turns()->latest()->first();
            if ($latestTurn) {
                $latestTurn->rope_bridge_attempted = true;
                $latestTurn->rope_bridge_success = ($result === 'success');
                $latestTurn->save();
            }

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

        if (!$room->current_turn_player_id) {
            $next = $activePlayers->first();
        } else {
            $current = $activePlayers->firstWhere('id', $room->current_turn_player_id);
            $currentIndex = $current ? $activePlayers->search($current) : -1;
            $nextIndex = ($currentIndex + 1) % $activePlayers->count();
            $next = $activePlayers[$nextIndex];
        }

        $room->current_turn_player_id = $next->id;
        $room->current_turn_started_at = now();
        $room->save();

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

        $next->user->notify(new TurnNotification($room, $next));
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

        $turnNumber = $currentPlayer->turns()->count() + 1;
        $card = $this->drawCard($currentPlayer, $turnNumber);

        // Auto-play: pick the option with higher TT (safer for team)
        $autoOption = ($card->opsi_b_tt >= $card->opsi_a_tt) ? 'B' : 'A';

        $this->processTurn($currentPlayer, $autoOption, $card);
    }

    /**
     * Finish the game: calculate scores, assign badges, rank players,
     * generate leadership profiles and real-world challenges.
     */
    public function finishGame(GameRoom $room): void
    {
        DB::transaction(function () use ($room) {
            $room->status = GameStatus::Finished;
            $room->current_turn_player_id = null;
            $room->current_turn_started_at = null;
            $room->save();

            $players = $room->players()
                ->where('is_active', true)
                ->get()
                ->map(function ($player) {
                    $player->score = $player->calculateScore();
                    return $player;
                });

            // ── TASK 6: Gameplay-first ranking ──
            // Badge priority: Carrier > Catalyst > Strategist > SoloPeak > Climber
            // Within same badge tier: sort by score
            // Within same score: sort by TT (leadership signal)
            $badgePriority = [
                'the_carrier'    => 5,
                'the_catalyst'   => 4,
                'the_strategist' => 3,
                'solo_peak'      => 2,
                'none'           => 1,
            ];

            $sorted = $players->sortByDesc(function ($player) use ($badgePriority) {
                $badge = $this->assignBadge($player);
                $badgeScore = $badgePriority[$badge] ?? 0;
                // Sort: badge_tier . score . TT . turn_order
                return $badgeScore . '.' . $player->score . '.' . $player->tt . '.' . str_pad(99 - $player->turn_order, 2, '0', STR_PAD_LEFT);
            });

            $rank = 1;
            foreach ($sorted as $player) {
                $badge = $this->assignBadge($player);

                $result = GameResult::create([
                    'game_room_id'      => $room->id,
                    'game_player_id'    => $player->id,
                    'final_level'       => $player->current_level,
                    'final_mp'          => $player->mp,
                    'final_sp'          => $player->sp,
                    'final_tt'          => $player->tt,
                    'final_score'       => $player->score,
                    'badge'             => $badge,
                    'rank'              => $rank,
                    'final_reputation'  => $player->reputation ?? 0,
                    'final_resources'   => $player->resources ?? 0,
                    'final_flexibility' => $player->flexibility ?? 0,
                ]);

                // ── V2: Generate Leadership Profile ──
                $this->reflectionEngine->generateProfile($result);

                // ── V2: Generate Real-World Challenge ──
                $profile = $result->leadershipProfile;
                $this->challengeGenerator->generateChallenge($result, $profile);

                $rank++;
            }

            // Notify all players
            foreach ($room->players as $gamePlayer) {
                $gamePlayer->user->notify(
                    new GameFinishedNotification($room, $gamePlayer)
                );
            }
        });
    }

    /**
     * Assign badge based on gameplay-first criteria.
     *
     * Priority order (a player can only get ONE badge):
     * 1. The Carrier    — Summit + TT>=8 + reputation>=0 + promises_kept >= promises_broken
     * 2. The Catalyst   — Did NOT summit + highest TT + positive cross-player effects
     * 3. The Strategist — 4+ distinct leadership behaviors demonstrated
     * 4. Solo Peak      — Summit + TT<8 or reputation<0 or net negative promises
     * 5. Climber        — Default (did not summit and no special qualification)
     */
    protected function assignBadge(GamePlayer $player): string
    {
        if ($player->qualifiesAsCarrier()) {
            return 'the_carrier';
        }

        if ($player->qualifiesAsCatalyst()) {
            return 'the_catalyst';
        }

        if ($player->qualifiesAsStrategist()) {
            return 'the_strategist';
        }

        if ($player->current_level === 'summit') {
            return 'solo_peak';
        }

        return 'none';
    }

    /**
     * Start the game: shuffle turn order and set first player.
     * Also checks for unresolved RealWorldChallenges from prior sessions (PRD Feature 8).
     *
     * @return array Start result including any unresolved challenges
     */
    public function startGame(GameRoom $room): array
    {
        return DB::transaction(function () use ($room) {
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
                new TurnNotification($room, $players->first())
            );

            // ── PRD Feature 8: Real-World Action Loop follow-up ──
            $unresolvedChallenges = [];
            foreach ($players as $player) {
                $user = $player->user;
                if ($user) {
                    $userChallenges = $this->challengeFollowUp->getUnresolvedChallenges($user);
                    if (!empty($userChallenges)) {
                        $unresolvedChallenges[$player->id] = $userChallenges;
                    }
                }
            }

            return [
                'started' => true,
                'unresolved_challenges' => $unresolvedChallenges,
            ];
        });
    }

    // ── V2: Social Mechanics Methods ──

    /**
     * Create a promise between two players.
     */
    public function createPromise(GameRoom $room, GamePlayer $promiser, GamePlayer $recipient, string $type, string $description): \App\Models\Promise
    {
        return $this->socialEngine->createPromise($room, $promiser, $recipient, $type, $description);
    }

    /**
     * Fulfill a promise.
     */
    public function fulfillPromise(\App\Models\Promise $promise): void
    {
        $this->socialEngine->fulfillPromise($promise);
    }

    /**
     * Break a promise.
     */
    public function breakPromise(\App\Models\Promise $promise): void
    {
        $this->socialEngine->breakPromise($promise);
    }

    /**
     * Create a vote event.
     */
    public function createVote(GameRoom $room, GamePlayer $triggeringPlayer, string $topic, string $description, string $type, array $options): \App\Models\Vote
    {
        return $this->socialEngine->createVote($room, $triggeringPlayer, $topic, $description, $type, $options);
    }

    /**
     * Cast a vote.
     */
    public function castVote(\App\Models\Vote $vote, GamePlayer $player, string $choice): void
    {
        $this->socialEngine->castVote($vote, $player, $choice);
    }

    // ── V2: Get Active Consequences for UI ──

    /**
     * Get visible consequences for a player (for UI display).
     */
    public function getVisibleConsequences(GameRoom $room, int $playerId): \Illuminate\Database\Eloquent\Collection
    {
        return $this->consequenceEngine->getVisibleConsequences($room, $playerId);
    }

    /**
     * Get all active promises for a room.
     */
    public function getActivePromises(GameRoom $room): \Illuminate\Database\Eloquent\Collection
    {
        return $this->socialEngine->getActivePromises($room);
    }

    /**
     * Get all active votes for a room.
     */
    public function getActiveVotes(GameRoom $room): \Illuminate\Database\Eloquent\Collection
    {
        return $this->socialEngine->getActiveVotes($room);
    }
}
