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
     * TASK 7 SIMPLIFY: Minimal pattern detection.
     * Only the most reliable pattern signals survive.
     * Removed: safe-during-crisis detection (unreliable with new card system).
     * Kept: same-option 5+ consecutive (very reliable indicator).
     */
    private function inferMinimalPatterns(GameTurn $turn, GamePlayer $player): array
    {
        $signals = [];

        // Only pattern: same option 5+ consecutive turns
        $recentChoices = $player->turns()
            ->where('id', '!=', $turn->id)
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->pluck('chosen_option')
            ->toArray();

        if (count($recentChoices) >= 5 && count(array_unique($recentChoices)) === 1) {
            $signals[] = [
                'dimension' => 'adaptability',
                'polarity' => 'negative',
                'magnitude' => 2,
                'reason' => "Same option chosen 5+ consecutive turns (rigid pattern)",
            ];
        }

        return $signals;
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
