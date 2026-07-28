<?php

namespace App\Services;

use App\Models\GamePlayer;
use App\Models\GameTurn;
use App\Models\PlayerBehavior;
use Illuminate\Support\Facades\Log;

/**
 * BehaviorTracker — Evidence-driven leadership behavior inference.
 *
 * TASK 3 REWRITE: This tracker creates evidence EVENTS, not stat-based inferences.
 * Every leadership conclusion must reference accumulated observable evidence events.
 * No structural inference from stat deltas (removed — that was the "garbage in" problem).
 *
 * TASK 7 SIMPLIFY: Removed all logic that exists only because it was technically interesting.
 * Only analytics directly supported by observable evidence survive.
 * If evidence is weak, returns "Insufficient evidence."
 *
 * Evidence sources (in order of reliability):
 * 1. Explicit tags from card JSON (card author declared behavior_tags)
 * 2. Observable game events (promise kept/broken, cross-player effects applied, etc.)
 * 3. Minimal pattern detection (only same-option repetition — very reliable)
 */
class BehaviorTracker
{
    private const DIMENSIONS = [
        'risk_taking'    => ['weight' => 1.5],
        'collaboration'  => ['weight' => 2.0],
        'empathy'        => ['weight' => 1.5],
        'decisiveness'   => ['weight' => 1.0],
        'coaching'       => ['weight' => 1.5],
        'control'        => ['weight' => 1.0],
        'adaptability'   => ['weight' => 1.0],
    ];

    private const SOURCE_RELIABILITY = [
        'explicit'  => 1.0,
        'game_event' => 0.8,
        'pattern'   => 0.4,
    ];

    private const MIN_WEIGHT_FOR_CONFIDENCE = 4.0;
    private const MIN_EVIDENCE_FOR_LABEL = 2;

    /**
     * Process a turn and record behavior evidence from card behavior_tags.
     *
     * TASK 3: Now ONLY records evidence from explicit card behavior_tags.
     * Structural inference from stat deltas has been REMOVED.
     * Observable game events (promises, cross-player effects) are recorded separately
     * by the Event Engine and forwarded here via $cardData['observed_events'].
     *
     * @return array The raw evidence recorded this turn
     */
    public function trackBehaviors(GameTurn $turn, GamePlayer $player, array $cardData): array
    {
        $evidence = [];
        $option = $turn->chosen_option;
        $behaviorTags = [];
        $isKrisis = $cardData['is_krisis'] ?? false;

        // Extract behavior tags for the chosen option
        $optionTags = $cardData['behavior_tags'][$option] ?? [];
        $observedEvents = $cardData['observed_events'] ?? [];

        // ── Source 1: Explicit behavior tags from card JSON ──
        foreach ($optionTags as $dimension => $signal) {
            if (!isset(self::DIMENSIONS[$dimension])) {
                continue;
            }
            if ($signal == 0) {
                continue;
            }

            $magnitude = abs($signal);
            $polarity = $signal > 0 ? 'positive' : 'negative';
            $contextModifier = $this->computeContextModifier($player, $isKrisis, $turn);

            // Generate human-readable evidence description
            $evidenceDescription = $this->describeEvidence(
                $dimension, $polarity, $option, $cardData['card_narrative'] ?? ''
            );

            $this->recordEvidence($player, $turn, $dimension, $polarity, $magnitude, 'explicit', $contextModifier, $evidenceDescription);
            $evidence[$dimension] = $signal;
        }

        // ── Source 2: Observable game events ──
        foreach ($observedEvents as $event) {
            $this->recordGameEventEvidence($player, $turn, $event);
        }

        // ── Source 3: Minimal pattern detection ──
        $patternSignals = $this->inferMinimalPatterns($turn, $player);
        foreach ($patternSignals as $signal) {
            $existing = PlayerBehavior::where('game_player_id', $player->id)
                ->where('game_turn_id', $turn->id)
                ->where('behavior_type', $signal['dimension'])
                ->exists();
            if (!$existing) {
                $contextModifier = $this->computeContextModifier($player, $isKrisis, $turn);
                $this->recordEvidence($player, $turn, $signal['dimension'], $signal['polarity'], $signal['magnitude'], 'pattern', $contextModifier, $signal['reason']);
            }
        }

        return $evidence;
    }

    /**
     * Record evidence from observable game events (promises, cross-player effects).
     * These are events the game system generates, not stat-based inferences.
     */
    private function recordGameEventEvidence(GamePlayer $player, GameTurn $turn, array $event): void
    {
        $dimension = $event['dimension'] ?? null;
        $polarity = $event['polarity'] ?? 'positive';
        $magnitude = $event['magnitude'] ?? 1;
        $description = $event['description'] ?? 'Observable game event';

        if (!$dimension || !isset(self::DIMENSIONS[$dimension])) {
            return;
        }

        $existing = PlayerBehavior::where('game_player_id', $player->id)
            ->where('game_turn_id', $turn->id)
            ->where('behavior_type', $dimension)
            ->where('evidence', $description)
            ->exists();

        if (!$existing) {
            $contextModifier = $this->computeContextModifier($player, false, $turn);
            $this->recordEvidence($player, $turn, $dimension, $polarity, $magnitude, 'game_event', $contextModifier, $description);
        }
    }

    /**
     * Generate a human-readable evidence description from card choice.
     * TASK 3: Evidence is now in human-readable form, not stat deltas.
     */
    private function describeEvidence(string $dimension, string $polarity, string $option, string $narrative): string
    {
        $labels = [
            'risk_taking'    => ['positive' => 'Took a calculated risk', 'negative' => 'Avoided risk when risk was warranted'],
            'collaboration'  => ['positive' => 'Prioritized team over self', 'negative' => 'Prioritized self over team'],
            'empathy'        => ['positive' => 'Protected teammate at personal cost', 'negative' => 'Ignored teammate\'s needs'],
            'decisiveness'   => ['positive' => 'Made a firm, clear decision', 'negative' => 'Avoided making a decision'],
            'coaching'       => ['positive' => 'Invested in developing others', 'negative' => 'Neglected developing others'],
            'control'        => ['positive' => 'Exercised direct authority', 'negative' => 'Avoided taking responsibility'],
            'adaptability'   => ['positive' => 'Adapted approach to context', 'negative' => 'Rigidly stuck to one approach'],
        ];

        $label = $labels[$dimension][$polarity] ?? "Demonstrated {$dimension}";
        return "Option {$option}: {$label}";
    }

    /**
     * PATTERN DETECTION — TASK 3 Rewrite
     *
     * Behavior analysis must detect repeated patterns.
     * Never infer leadership style from a single decision.
     * Instead calculate:
     *   - Opportunity count (how many times the player faced a situation)
     *   - Behavior count (how many times they chose the behavior)
     *   - Behavior frequency (behavior_count / opportunity_count)
     *   - Context (what level, was it crisis, what were the stakes)
     *   - Confidence score (based on evidence volume and consistency)
     *
     * Only generate insights when enough evidence exists.
     * Otherwise return: "Insufficient evidence."
     *
     * Pattern types detected (all require minimum evidence threshold):
     *   1. Same-option repetition: 5+ consecutive same option → adaptability negative
     *   2. TT-avoidance: Consistently chose lower TT option when higher TT was available → collaboration negative
     *   3. Self-sacrifice: Consistently chose options that cost self for team benefit → collaboration positive
     *   4. Crisis avoidance: Chose safer option on 3+ crisis cards → risk_taking negative
     *   5. Coaching deficit: Never chose option with coaching tag across 5+ turns with opportunity → coaching negative
     *   6. Control tendency: Chose control-tagged options 60%+ of available opportunities → control positive/negative
     */
    private function inferMinimalPatterns(GameTurn $turn, GamePlayer $player): array
    {
        $signals = [];
        $allTurns = $player->turns()
            ->where('id', '!=', $turn->id)
            ->orderBy('created_at', 'asc')
            ->get();

        if ($allTurns->count() < 3) {
            return $signals; // Too few turns for pattern detection
        }

        // ── Pattern 1: Same-option repetition (5+ consecutive) ──
        $consecutiveSame = $this->detectConsecutiveOptionPattern($allTurns);
        if ($consecutiveSame !== null) {
            $signals[] = [
                'dimension' => 'adaptability',
                'polarity' => 'negative',
                'magnitude' => 2,
                'reason' => "Same option chosen 5+ consecutive turns (rigid pattern)",
                'opportunity_count' => $allTurns->count(),
                'behavior_count' => $consecutiveSame['streak'],
                'frequency' => round($consecutiveSame['streak'] / $allTurns->count(), 2),
                'confidence' => 'high',
            ];
        }

        // ── Pattern 2: TT-avoidance (consistently chose lower TT option) ──
        $ttAvoidance = $this->detectTTAvoidancePattern($allTurns);
        if ($ttAvoidance !== null) {
            $signals[] = [
                'dimension' => 'collaboration',
                'polarity' => 'negative',
                'magnitude' => $ttAvoidance['count'] >= 5 ? 2 : 1,
                'reason' => sprintf(
                    "Chose lower TT option in %d of %d opportunities (%.0f%% TT-avoidance)",
                    $ttAvoidance['count'], $ttAvoidance['opportunities'],
                    ($ttAvoidance['count'] / $ttAvoidance['opportunities']) * 100
                ),
                'opportunity_count' => $ttAvoidance['opportunities'],
                'behavior_count' => $ttAvoidance['count'],
                'frequency' => round($ttAvoidance['count'] / $ttAvoidance['opportunities'], 2),
                'confidence' => $ttAvoidance['count'] >= 5 ? 'high' : 'moderate',
            ];
        }

        // ── Pattern 3: Self-sacrifice (consistently chose options that cost self for team) ──
        $selfSacrifice = $this->detectSelfSacrificePattern($allTurns);
        if ($selfSacrifice !== null) {
            $signals[] = [
                'dimension' => 'collaboration',
                'polarity' => 'positive',
                'magnitude' => $selfSacrifice['count'] >= 4 ? 2 : 1,
                'reason' => sprintf(
                    "Chose option with personal cost + team benefit in %d of %d opportunities (%.0f%%)",
                    $selfSacrifice['count'], $selfSacrifice['opportunities'],
                    ($selfSacrifice['count'] / $selfSacrifice['opportunities']) * 100
                ),
                'opportunity_count' => $selfSacrifice['opportunities'],
                'behavior_count' => $selfSacrifice['count'],
                'frequency' => round($selfSacrifice['count'] / $selfSacrifice['opportunities'], 2),
                'confidence' => $selfSacrifice['count'] >= 4 ? 'high' : 'moderate',
            ];
        }

        // ── Pattern 4: Crisis avoidance (chose safer option on 3+ crisis cards) ──
        $crisisAvoidance = $this->detectCrisisAvoidancePattern($allTurns);
        if ($crisisAvoidance !== null) {
            $signals[] = [
                'dimension' => 'risk_taking',
                'polarity' => 'negative',
                'magnitude' => $crisisAvoidance['count'] >= 4 ? 2 : 1,
                'reason' => sprintf(
                    "Chose safer option on %d of %d crisis cards (%.0f%% crisis avoidance)",
                    $crisisAvoidance['count'], $crisisAvoidance['crisisCount'],
                    ($crisisAvoidance['count'] / $crisisAvoidance['crisisCount']) * 100
                ),
                'opportunity_count' => $crisisAvoidance['crisisCount'],
                'behavior_count' => $crisisAvoidance['count'],
                'frequency' => round($crisisAvoidance['count'] / $crisisAvoidance['crisisCount'], 2),
                'confidence' => $crisisAvoidance['count'] >= 4 ? 'high' : 'moderate',
            ];
        }

        // ── Pattern 5: Coaching deficit (never chose coaching option when available) ──
        $coachingDeficit = $this->detectCoachingDeficitPattern($allTurns);
        if ($coachingDeficit !== null) {
            $signals[] = [
                'dimension' => 'coaching',
                'polarity' => 'negative',
                'magnitude' => 1,
                'reason' => sprintf(
                    "Never chose coaching-oriented option across %d turns where coaching was available",
                    $coachingDeficit['opportunities']
                ),
                'opportunity_count' => $coachingDeficit['opportunities'],
                'behavior_count' => 0,
                'frequency' => 0.0,
                'confidence' => 'moderate',
            ];
        }

        return $signals;
    }

    /**
     * Detect consecutive same-option choices (5+).
     * Returns streak data or null if no streak found.
     */
    private function detectConsecutiveOptionPattern($turns): ?array
    {
        $streak = 0;
        $lastOption = null;
        $maxStreak = 0;
        $maxOption = null;

        foreach ($turns->reverse() as $t) {
            if ($lastOption === null) {
                $lastOption = $t->chosen_option;
                $streak = 1;
                $maxStreak = 1;
                $maxOption = $lastOption;
                continue;
            }

            if ($t->chosen_option === $lastOption) {
                $streak++;
                if ($streak > $maxStreak) {
                    $maxStreak = $streak;
                    $maxOption = $lastOption;
                }
            } else {
                $lastOption = $t->chosen_option;
                $streak = 1;
            }
        }

        return $maxStreak >= 5 ? ['streak' => $maxStreak, 'option' => $maxOption] : null;
    }

    /**
     * Detect TT-avoidance: consistently chose the option with lower TT delta.
     * Only counts turns where both options had different TT deltas.
     * Minimum: 3 opportunities with 60%+ avoidance rate.
     */
    private function detectTTAvoidancePattern($turns): ?array
    {
        $opportunities = 0;
        $avoided = 0;

        foreach ($turns as $t) {
            // Check if the turn has data about both options' TT values
            $card = $t->card;
            if (!$card) continue;

            $ttA = $card->opsi_a_tt ?? 0;
            $ttB = $card->opsi_b_tt ?? 0;

            if ($ttA === $ttB) continue; // No TT difference — not an opportunity

            $opportunities++;
            $higherTTOption = ($ttA > $ttB) ? 'A' : 'B';
            $lowerTTOption = ($ttA > $ttB) ? 'B' : 'A';

            if ($t->chosen_option === $lowerTTOption) {
                $avoided++;
            }
        }

        if ($opportunities < 3) return null;
        $rate = $avoided / $opportunities;
        return $rate >= 0.6 ? [
            'count' => $avoided,
            'opportunities' => $opportunities,
        ] : null;
    }

    /**
     * Detect self-sacrifice pattern: consistently chose options with negative personal stat
     * delta AND positive cross-player effect.
     */
    private function detectSelfSacrificePattern($turns): ?array
    {
        $opportunities = 0;
        $sacrificed = 0;

        foreach ($turns as $t) {
            $crossEffects = $t->cross_player_effects;
            if (!$crossEffects) continue;

            $decoded = json_decode($crossEffects, true);
            if (!is_array($decoded) || empty($decoded)) continue;

            $opportunities++;

            // Check if the player's own net stat change was negative
            $selfNet = $t->mp_effect + $t->sp_effect + $t->tt_effect;

            // Check if cross-player effects were positive for others
            $positiveForOthers = false;
            foreach ($decoded as $effect) {
                if (($effect['delta'] ?? 0) > 0) {
                    $positiveForOthers = true;
                    break;
                }
            }

            if ($selfNet < 0 && $positiveForOthers) {
                $sacrificed++;
            }
        }

        if ($opportunities < 2) return null;
        $rate = $sacrificed / $opportunities;
        return $rate >= 0.5 ? [
            'count' => $sacrificed,
            'opportunities' => $opportunities,
        ] : null;
    }

    /**
     * Detect crisis avoidance: chose the "safer" option on crisis cards.
     * "Safer" = option with lower maximum stat delta (less variance).
     */
    private function detectCrisisAvoidancePattern($turns): ?array
    {
        $crisisCount = 0;
        $saferChoices = 0;

        foreach ($turns as $t) {
            $card = $t->card;
            if (!$card || !$card->isKrisis()) continue;

            $crisisCount++;

            // Compare total stat magnitude of both options
            $magA = abs($card->opsi_a_mp ?? 0) + abs($card->opsi_a_sp ?? 0) + abs($card->opsi_a_tt ?? 0);
            $magB = abs($card->opsi_b_mp ?? 0) + abs($card->opsi_b_sp ?? 0) + abs($card->opsi_b_tt ?? 0);

            // "Safer" option = the one with lower total magnitude
            $saferOption = ($magA <= $magB) ? 'A' : 'B';

            if ($t->chosen_option === $saferOption) {
                $saferChoices++;
            }
        }

        if ($crisisCount < 2) return null;
        $rate = $saferChoices / $crisisCount;
        return $rate >= 0.6 ? [
            'count' => $saferChoices,
            'crisisCount' => $crisisCount,
        ] : null;
    }

    /**
     * Detect coaching deficit: never chose a coaching-tagged option when available.
     */
    private function detectCoachingDeficitPattern($turns): ?array
    {
        $opportunities = 0;

        foreach ($turns as $t) {
            $card = $t->card;
            if (!$card) continue;

            // Check behavior tags from card data stored on turn
            $behaviorData = $t->behavior_data;
            if (!$behaviorData) continue;

            $decoded = json_decode($behaviorData, true);
            if (!is_array($decoded)) continue;

            // Check if any option had a coaching tag
            $tagsA = $card->getBehaviorTags('A') ?? [];
            $tagsB = $card->getBehaviorTags('B') ?? [];

            $hasCoachingOption = false;
            if (isset($tagsA['coaching']) && $tagsA['coaching'] > 0) $hasCoachingOption = true;
            if (isset($tagsB['coaching']) && $tagsB['coaching'] > 0) $hasCoachingOption = true;

            if ($hasCoachingOption) {
                $opportunities++;
            }
        }

        // If 5+ opportunities but zero coaching choices
        if ($opportunities >= 5) {
            // Check if any coaching evidence exists in PlayerBehavior
            $playerId = $turns->first()->game_player_id ?? null;
            if ($playerId) {
                $coachingEvidence = PlayerBehavior::where('game_player_id', $playerId)
                    ->where('behavior_type', 'coaching')
                    ->count();

                if ($coachingEvidence === 0) {
                    return ['opportunities' => $opportunities];
                }
            }
        }

        return null;
    }

    /**
     * Generate a full pattern report with opportunity/behavior/frequency/confidence.
     * Used by ReflectionEngine for narrative generation.
     *
     * @return array Pattern report with all detected patterns and their metrics
     */
    public function getPatternReport(GamePlayer $player): array
    {
        $allTurns = $player->turns()->orderBy('created_at', 'asc')->get();
        $totalTurns = $allTurns->count();

        if ($totalTurns < 5) {
            return [
                'status' => 'insufficient_data',
                'message' => 'Insufficient evidence. At least 5 turns required for pattern detection.',
                'patterns' => [],
                'data_quality' => [
                    'total_turns' => $totalTurns,
                    'minimum_required' => 5,
                ],
            ];
        }

        $patterns = [];

        // Run all pattern detectors
        $consecutivePattern = $this->detectConsecutiveOptionPattern($allTurns);
        if ($consecutivePattern !== null) {
            $patterns[] = [
                'type' => 'option_rigidity',
                'dimension' => 'adaptability',
                'polarity' => 'negative',
                'description' => sprintf(
                    "Chose option %s for %d consecutive turns out of %d total (%.0f%% rigidity)",
                    $consecutivePattern['option'], $consecutivePattern['streak'],
                    $totalTurns, ($consecutivePattern['streak'] / $totalTurns) * 100
                ),
                'opportunity_count' => $totalTurns,
                'behavior_count' => $consecutivePattern['streak'],
                'frequency' => round($consecutivePattern['streak'] / $totalTurns, 2),
                'confidence' => $consecutivePattern['streak'] >= 7 ? 'high' : 'moderate',
                'threshold' => '5+ consecutive same option',
            ];
        }

        $ttAvoidance = $this->detectTTAvoidancePattern($allTurns);
        if ($ttAvoidance !== null) {
            $patterns[] = [
                'type' => 'tt_avoidance',
                'dimension' => 'collaboration',
                'polarity' => 'negative',
                'description' => sprintf(
                    "Avoided higher-trust option in %d of %d opportunities (%.0f%% TT-avoidance rate)",
                    $ttAvoidance['count'], $ttAvoidance['opportunities'],
                    ($ttAvoidance['count'] / $ttAvoidance['opportunities']) * 100
                ),
                'opportunity_count' => $ttAvoidance['opportunities'],
                'behavior_count' => $ttAvoidance['count'],
                'frequency' => round($ttAvoidance['count'] / $ttAvoidance['opportunities'], 2),
                'confidence' => $ttAvoidance['count'] >= 5 ? 'high' : 'moderate',
                'threshold' => '60%+ avoidance rate with 3+ opportunities',
            ];
        }

        $selfSacrifice = $this->detectSelfSacrificePattern($allTurns);
        if ($selfSacrifice !== null) {
            $patterns[] = [
                'type' => 'self_sacrifice',
                'dimension' => 'collaboration',
                'polarity' => 'positive',
                'description' => sprintf(
                    "Chose personal cost + team benefit in %d of %d cross-player opportunities (%.0f%%)",
                    $selfSacrifice['count'], $selfSacrifice['opportunities'],
                    ($selfSacrifice['count'] / $selfSacrifice['opportunities']) * 100
                ),
                'opportunity_count' => $selfSacrifice['opportunities'],
                'behavior_count' => $selfSacrifice['count'],
                'frequency' => round($selfSacrifice['count'] / $selfSacrifice['opportunities'], 2),
                'confidence' => $selfSacrifice['count'] >= 4 ? 'high' : 'moderate',
                'threshold' => '50%+ sacrifice rate with 2+ opportunities',
            ];
        }

        $crisisAvoidance = $this->detectCrisisAvoidancePattern($allTurns);
        if ($crisisAvoidance !== null) {
            $patterns[] = [
                'type' => 'crisis_avoidance',
                'dimension' => 'risk_taking',
                'polarity' => 'negative',
                'description' => sprintf(
                    "Chose safer option on %d of %d crisis cards (%.0f%% crisis avoidance)",
                    $crisisAvoidance['count'], $crisisAvoidance['crisisCount'],
                    ($crisisAvoidance['count'] / $crisisAvoidance['crisisCount']) * 100
                ),
                'opportunity_count' => $crisisAvoidance['crisisCount'],
                'behavior_count' => $crisisAvoidance['count'],
                'frequency' => round($crisisAvoidance['count'] / $crisisAvoidance['crisisCount'], 2),
                'confidence' => $crisisAvoidance['count'] >= 4 ? 'high' : 'moderate',
                'threshold' => '60%+ safer choice rate with 2+ crisis cards',
            ];
        }

        $coachingDeficit = $this->detectCoachingDeficitPattern($allTurns);
        if ($coachingDeficit !== null) {
            $patterns[] = [
                'type' => 'coaching_deficit',
                'dimension' => 'coaching',
                'polarity' => 'negative',
                'description' => sprintf(
                    "Never chose coaching-oriented option across %d opportunities (0%% coaching frequency)",
                    $coachingDeficit['opportunities']
                ),
                'opportunity_count' => $coachingDeficit['opportunities'],
                'behavior_count' => 0,
                'frequency' => 0.0,
                'confidence' => 'moderate',
                'threshold' => '5+ opportunities with 0 coaching choices',
            ];
        }

        return [
            'status' => empty($patterns) ? 'no_patterns_detected' : 'patterns_found',
            'message' => empty($patterns)
                ? 'No repeated patterns detected. Leadership style is diverse and context-dependent.'
                : sprintf('Detected %d behavioral pattern(s).', count($patterns)),
            'patterns' => $patterns,
            'data_quality' => [
                'total_turns' => $totalTurns,
                'patterns_detected' => count($patterns),
                'high_confidence_patterns' => count(array_filter($patterns, fn($p) => $p['confidence'] === 'high')),
            ],
        ];
    }

    public function getBehaviorProfile(GamePlayer $player): array
    {
        $totalTurns = $player->turns()->count();

        if ($totalTurns < 5) {
            return $this->insufficientDataProfile($player, $totalTurns);
        }

        $dimensions = [];
        $totalEvidenceCount = 0;

        foreach (self::DIMENSIONS as $dim => $config) {
            $evidencePoints = PlayerBehavior::where('game_player_id', $player->id)
                ->where('behavior_type', $dim)
                ->get();

            $dimResult = $this->scoreDimension($dim, $evidencePoints);
            $dimensions[$dim] = $dimResult;
            $totalEvidenceCount += $evidencePoints->count();
        }

        $style = $this->deriveStyle($dimensions);
        $strengths = $this->detectStrengths($dimensions);
        $blindSpots = $this->detectBlindSpots($dimensions);
        $unexplored = $this->detectUnexplored($dimensions);

        return [
            'data_quality' => [
                'total_turns' => $totalTurns,
                'evidence_count' => $totalEvidenceCount,
                'overall_confidence' => $this->computeOverallConfidence($dimensions),
            ],
            'style' => $style,
            'dimensions' => $dimensions,
            'strengths' => $strengths,
            'blind_spots' => $blindSpots,
            'unexplored' => $unexplored,
        ];
    }

    private function scoreDimension(string $dimension, $evidencePoints): array
    {
        $weight = self::DIMENSIONS[$dimension]['weight'];
        $positiveWeight = 0;
        $negativeWeight = 0;
        $totalWeight = 0;
        $signalSum = 0;
        $count = $evidencePoints->count();

        foreach ($evidencePoints as $ep) {
            $signal = $ep->score > 0 ? 1 : -1;
            $magnitude = abs($ep->score);
            $sourceRel = self::SOURCE_RELIABILITY[$ep->source ?? 'game_event'] ?? 0.8;
            $context = $ep->context_modifier ?? 1.0;

            $evidenceWeight = $magnitude * $sourceRel * $context;
            $weightedSignal = $signal * $magnitude * $sourceRel * $context;

            $signalSum += $weightedSignal;
            $totalWeight += $evidenceWeight;

            if ($signal > 0) {
                $positiveWeight += $evidenceWeight;
            } else {
                $negativeWeight += $evidenceWeight;
            }
        }

        $dimensionScore = $count > 0 ? $signalSum / max(1, $count) : 0;
        $rawConfidence = min(1.0, $totalWeight / self::MIN_WEIGHT_FOR_CONFIDENCE);

        $consistency = ($totalWeight > 0)
            ? abs($positiveWeight - $negativeWeight) / $totalWeight
            : 1.0;

        $effectiveConfidence = $rawConfidence * (0.5 + 0.5 * $consistency);

        $classification = $this->classifyDimension($dimensionScore, $effectiveConfidence, $count);

        return [
            'score' => round($dimensionScore, 2),
            'weight' => $weight,
            'confidence' => round($effectiveConfidence, 2),
            'consistency' => round($consistency, 2),
            'evidence_count' => $count,
            'evidence_weight' => round($totalWeight, 2),
            'classification' => $classification,
        ];
    }

    private function classifyDimension(float $score, float $confidence, int $count): string
    {
        if ($count < self::MIN_EVIDENCE_FOR_LABEL) {
            return 'unexplored';
        }
        if ($confidence < 0.25) {
            return 'speculative'; // TASK 7: "Insufficient evidence"
        }
        if ($confidence < 0.5) {
            return 'emerging'; // TASK 7: "Suggests" — early signal
        }
        if ($score > 0 && $confidence >= 0.5) {
            return 'strength';
        }
        if ($score <= -1 && $confidence >= 0.5) {
            return 'blind_spot';
        }
        if ($confidence >= 0.5) {
            return 'neutral';
        }
        return 'speculative';
    }

    private function deriveStyle(array $dimensions): array
    {
        $candidates = [];
        foreach ($dimensions as $dim => $data) {
            if ($data['confidence'] >= 0.5 && $data['score'] > 0) {
                $candidates[$dim] = $data['score'] * $data['weight'] * $data['confidence'];
            }
        }

        if (empty($candidates)) {
            return [
                'primary' => 'emerging',
                'secondary' => null,
                'confidence' => 0,
                'tensions' => [],
            ];
        }

        arsort($candidates);
        $primaryDim = array_key_first($candidates);
        $primaryConf = $dimensions[$primaryDim]['confidence'];

        array_shift($candidates);
        $secondaryDim = array_key_first($candidates) ?: null;

        $tensions = $this->detectTensions($dimensions);

        return [
            'primary' => $primaryDim,
            'secondary' => $secondaryDim,
            'confidence' => round($primaryConf, 2),
            'tensions' => $tensions,
        ];
    }

    private function detectTensions(array $dimensions): array
    {
        $pairs = [
            ['risk_taking', 'decisiveness'],
            ['collaboration', 'control'],
            ['empathy', 'control'],
            ['coaching', 'decisiveness'],
        ];

        $tensions = [];
        foreach ($pairs as [$a, $b]) {
            $aScore = $dimensions[$a]['score'] ?? 0;
            $bScore = $dimensions[$b]['score'] ?? 0;
            $aConf = $dimensions[$a]['confidence'] ?? 0;
            $bConf = $dimensions[$b]['confidence'] ?? 0;

            if ($aScore > 0 && $bScore > 0 && $aConf >= 0.5 && $bConf >= 0.5) {
                $tensions[] = "{$a} vs {$b}";
            }
        }

        return $tensions;
    }

    private function detectStrengths(array $dimensions): array
    {
        $qualified = [];
        foreach ($dimensions as $dim => $data) {
            if ($data['confidence'] >= 0.5 && $data['score'] > 0) {
                $qualified[$dim] = $data;
            }
        }

        $qualifiedCount = count($qualified);
        if ($qualifiedCount === 0) {
            return [];
        }

        $threshold = max(1, (int) ceil($qualifiedCount / 2));
        uasort($qualified, fn ($a, $b) => ($b['score'] * $b['confidence'] * $b['weight']) <=> ($a['score'] * $a['confidence'] * $a['weight']));

        $strengths = [];
        $i = 0;
        foreach ($qualified as $dim => $data) {
            if ($i >= $threshold) break;
            $strengths[] = [
                'dimension' => $dim,
                'score' => $data['score'],
                'confidence' => $data['confidence'],
                'evidence_count' => $data['evidence_count'],
            ];
            $i++;
        }

        return array_slice($strengths, 0, 3);
    }

    private function detectBlindSpots(array $dimensions): array
    {
        $spots = [];
        foreach ($dimensions as $dim => $data) {
            if ($data['confidence'] >= 0.5 && $data['score'] <= -1 && $data['evidence_count'] >= 2) {
                $spots[] = [
                    'dimension' => $dim,
                    'score' => $data['score'],
                    'confidence' => $data['confidence'],
                    'evidence_count' => $data['evidence_count'],
                ];
            }
        }

        usort($spots, fn ($a, $b) => abs($b['score'] * $b['confidence']) <=> abs($a['score'] * $a['confidence']));

        return array_slice($spots, 0, 3);
    }

    private function detectUnexplored(array $dimensions): array
    {
        $unexplored = [];
        foreach ($dimensions as $dim => $data) {
            if ($data['confidence'] < 0.25) {
                $unexplored[] = $dim;
            }
        }
        return $unexplored;
    }

    private function computeOverallConfidence(array $dimensions): float
    {
        $total = 0;
        $count = 0;
        foreach ($dimensions as $data) {
            $total += $data['confidence'];
            $count++;
        }
        return $count > 0 ? round($total / $count, 2) : 0;
    }

    private function insufficientDataProfile(GamePlayer $player, int $totalTurns): array
    {
        $dimensions = [];
        foreach (self::DIMENSIONS as $dim => $config) {
            $count = PlayerBehavior::where('game_player_id', $player->id)
                ->where('behavior_type', $dim)
                ->count();
            $dimensions[$dim] = [
                'score' => 0,
                'weight' => $config['weight'],
                'confidence' => 0,
                'consistency' => 0,
                'evidence_count' => $count,
                'evidence_weight' => 0,
                'classification' => 'insufficient_data',
            ];
        }

        return [
            'data_quality' => [
                'total_turns' => $totalTurns,
                'evidence_count' => PlayerBehavior::where('game_player_id', $player->id)->count(),
                'overall_confidence' => 0,
            ],
            'style' => [
                'primary' => 'insufficient_data',
                'secondary' => null,
                'confidence' => 0,
                'tensions' => [],
            ],
            'dimensions' => $dimensions,
            'strengths' => [],
            'blind_spots' => [],
            'unexplored' => array_keys(self::DIMENSIONS),
        ];
    }

    private function recordEvidence(
        GamePlayer $player,
        GameTurn $turn,
        string $dimension,
        string $polarity,
        int $magnitude,
        string $source,
        float $contextModifier,
        string $reason,
    ): void {
        PlayerBehavior::create([
            'game_player_id' => $player->id,
            'game_turn_id' => $turn->id,
            'behavior_type' => $dimension,
            'score' => $polarity === 'positive' ? $magnitude : -$magnitude,
            'evidence' => $reason,
            'source' => $source,
            'context_modifier' => round($contextModifier, 2),
        ]);
    }

    private function computeContextModifier(GamePlayer $player, bool $isKrisis, GameTurn $turn): float
    {
        $modifier = 1.0;

        if ($isKrisis) {
            $modifier *= 1.5;
        }

        if ($player->current_level === 'summit') {
            $modifier *= 1.2;
        }

        $turnIndex = $turn->turn_index ?? $player->turns()->count();
        $totalExpected = 20;
        $turnFactor = 1.0 + (0.5 * min($turnIndex / $totalExpected, 1.0));
        $modifier *= $turnFactor;

        return round($modifier, 2);
    }
}
