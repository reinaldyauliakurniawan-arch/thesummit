<?php

namespace App\Services;

use App\Models\GamePlayer;
use App\Models\GameTurn;
use App\Models\PlayerBehavior;
use Illuminate\Support\Facades\Log;

/**
 * BehaviorTracker — Evidence-driven leadership behavior inference.
 *
 * Implements the formal framework defined in docs/leadership-framework.md.
 * Every behavior label must be backed by accumulated evidence with confidence scoring.
 * No hardcoded single-turn thresholds. No premature labeling.
 */
class BehaviorTracker
{
    /**
     * Dimension definitions per leadership-framework.md.
     * Each dimension has a weight, and evidence is scored by signal, magnitude,
     * source reliability, and contextual modifiers.
     */
    private const DIMENSIONS = [
        'risk_taking'    => ['weight' => 1.5],
        'collaboration'  => ['weight' => 2.0],
        'empathy'        => ['weight' => 1.5],
        'decisiveness'   => ['weight' => 1.0],
        'coaching'       => ['weight' => 1.5],
        'control'        => ['weight' => 1.0],
        'adaptability'   => ['weight' => 1.0],
    ];

    /** Source reliability multipliers per leadership-framework.md. */
    private const SOURCE_RELIABILITY = [
        'explicit'  => 1.0,
        'structural' => 0.7,
        'pattern'   => 0.4,
    ];

    /** Minimum evidence weight required for any confidence (4.0 per framework). */
    private const MIN_WEIGHT_FOR_CONFIDENCE = 4.0;

    /** Minimum evidence count before a dimension can be labeled (calibration rule). */
    private const MIN_EVIDENCE_FOR_LABEL = 2;

    /**
     * Process a turn and record behavior evidence.
     *
     * Collects evidence from three sources per leadership-framework.md:
     * 1. Explicit tags (card author declared behavior_tags)
     * 2. Structural inference (stat deltas, cross-player effects, timing)
     * 3. Pattern inference (sequences across turns — computed lazily)
     *
     * @return array The raw evidence recorded this turn (not aggregated scores).
     */
    public function trackBehaviors(GameTurn $turn, GamePlayer $player, array $cardData): array
    {
        $evidence = [];
        $option = strtolower($turn->chosen_option);
        $allEffects = $cardData['effects'] ?? [];
        $behaviorTags = $cardData['behavior_tags'] ?? [];
        $isKrisis = $cardData['is_krisis'] ?? false;

        // ── Source 1: Explicit behavior tags from card JSON ──
        foreach ($behaviorTags as $dimension => $signal) {
            if (!isset(self::DIMENSIONS[$dimension])) {
                continue;
            }
            // signal: positive (1..2), neutral (0), negative (-1..-2)
            if ($signal == 0) {
                continue; // neutral tags are not evidence
            }

            $magnitude = abs($signal); // 1 or 2
            $polarity = $signal > 0 ? 'positive' : 'negative';
            $contextModifier = $this->computeContextModifier($player, $isKrisis, $turn);

            $this->recordEvidence($player, $turn, $dimension, $polarity, $magnitude, 'explicit', $contextModifier, "Explicit tag: option {$option}");
            $evidence[$dimension] = $signal;
        }

        // ── Source 2: Structural inference from stat deltas and effects ──
        $structuralSignals = $this->inferStructuralSignals($turn, $player, $cardData);
        foreach ($structuralSignals as $signal) {
            // Only record if no explicit tag already covered this dimension for this turn
            $existing = PlayerBehavior::where('game_player_id', $player->id)
                ->where('game_turn_id', $turn->id)
                ->where('behavior_type', $signal['dimension'])
                ->exists();
            if (!$existing) {
                $contextModifier = $this->computeContextModifier($player, $isKrisis, $turn);
                $this->recordEvidence($player, $turn, $signal['dimension'], $signal['polarity'], $signal['magnitude'], 'structural', $contextModifier, $signal['reason']);
                $evidence[$signal['dimension']] = $signal['polarity'] === 'positive' ? 1 : -1;
            }
        }

        // ── Source 3: Pattern inference (computed from turn history) ──
        $patternSignals = $this->inferPatternSignals($turn, $player);
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
     * Compute the full behavior profile with confidence scores per leadership-framework.md.
     *
     * Returns structured data — NO narrative text. Narrative is the Reflection Engine's job.
     *
     * @return array Structured profile matching the output schema in leadership-framework.md
     */
    public function getBehaviorProfile(GamePlayer $player): array
    {
        $totalTurns = $player->turns()->count();

        // Edge case: insufficient data
        if ($totalTurns < 5) {
            return $this->insufficientDataProfile($player, $totalTurns);
        }

        $dimensions = [];
        $totalEvidenceWeight = 0;
        $totalEvidenceCount = 0;

        foreach (self::DIMENSIONS as $dim => $config) {
            $evidencePoints = PlayerBehavior::where('game_player_id', $player->id)
                ->where('behavior_type', $dim)
                ->get();

            $dimResult = $this->scoreDimension($dim, $evidencePoints);
            $dimensions[$dim] = $dimResult;
            $totalEvidenceWeight += $dimResult['evidence_weight'];
            $totalEvidenceCount += $evidencePoints->count();
        }

        // Derive primary/secondary styles
        $style = $this->deriveStyle($dimensions);

        // Classify strengths, blind spots, and unexplored
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

    /**
     * Score a single dimension per the framework's scoring methodology.
     *
     * dimension_score = sum(signal * magnitude * context_modifier * source_reliability) / max(1, evidence_count)
     * confidence = min(1.0, evidence_weight / MIN_WEIGHT_FOR_CONFIDENCE) * (0.5 + 0.5 * consistency)
     */
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
            $sourceRel = self::SOURCE_RELIABILITY[$ep->source ?? 'structural'] ?? 0.7;
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

        // Consistency factor: balanced evidence reduces confidence
        $consistency = ($totalWeight > 0)
            ? abs($positiveWeight - $negativeWeight) / $totalWeight
            : 1.0;

        $effectiveConfidence = $rawConfidence * (0.5 + 0.5 * $consistency);

        // Determine classification
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

    /**
     * Classify a dimension per the framework thresholds.
     */
    private function classifyDimension(float $score, float $confidence, int $count): string
    {
        if ($count < self::MIN_EVIDENCE_FOR_LABEL) {
            return 'unexplored';
        }

        if ($confidence < 0.25) {
            return 'speculative';
        }

        if ($confidence < 0.5) {
            return 'emerging';
        }

        // Check for contradictory evidence (complex/inconsistent)
        // This is already captured in the consistency factor

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

    /**
     * Derive primary and secondary leadership styles from dimension scores.
     * Only dimensions with confidence >= 0.5 are considered.
     */
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

        // Detect opposing dimension tensions
        $tensions = $this->detectTensions($dimensions);

        return [
            'primary' => $primaryDim,
            'secondary' => $secondaryDim,
            'confidence' => round($primaryConf, 2),
            'tensions' => $tensions,
        ];
    }

    /**
     * Detect opposing dimension tensions per the framework's tension pairs.
     */
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

    /**
     * Detect strengths per framework: confidence >= 0.5, score > 0, top N/2.
     */
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

        // Top half
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

    /**
     * Detect blind spots per framework: confidence >= 0.5, score <= -1, >= 2 negative evidence.
     */
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

    /**
     * Detect unexplored dimensions: low confidence (not enough data).
     */
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

    /**
     * Compute overall confidence as average of dimension confidences.
     */
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

    /**
     * Return an insufficient-data profile (< 5 turns).
     */
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

    /**
     * Record a single evidence point.
     */
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

    /**
     * Compute context modifier per leadership-framework.md.
     * Crisis = 1.5x, Summit level = 1.2x, turn progression = 1.0-1.5x.
     */
    private function computeContextModifier(GamePlayer $player, bool $isKrisis, GameTurn $turn): float
    {
        $modifier = 1.0;

        // Crisis mode: 1.5x
        if ($isKrisis) {
            $modifier *= 1.5;
        }

        // Level modifier
        if ($player->current_level === 'summit') {
            $modifier *= 1.2;
        }

        // Turn number progression: 1.0 at turn 1, up to 1.5 at turn 20
        $turnIndex = $turn->turn_index ?? $player->turns()->count();
        $totalExpected = 20;
        $turnFactor = 1.0 + (0.5 * min($turnIndex / $totalExpected, 1.0));
        $modifier *= $turnFactor;

        return round($modifier, 2);
    }

    /**
     * Infer structural signals from stat deltas, cross-player effects, and timing.
     * Per the evidence signals tables in leadership-framework.md.
     */
    private function inferStructuralSignals(GameTurn $turn, GamePlayer $player, array $cardData): array
    {
        $signals = [];
        $option = strtolower($turn->chosen_option);
        $mpDelta = $turn->mp_effect;
        $spDelta = $turn->sp_effect;
        $ttDelta = $turn->tt_effect;
        $crossPlayer = $cardData['cross_player'] ?? [];
        $allEffects = $cardData['effects'] ?? [];

        // ── risk_taking signals ──
        if ($ttDelta <= -2 && ($mpDelta >= 2 || $spDelta >= 2)) {
            $signals[] = ['dimension' => 'risk_taking', 'polarity' => 'positive', 'magnitude' => 2, 'reason' => "Chose TT{$ttDelta} for MP/SP gain"];
        } elseif ($ttDelta < 0 && ($mpDelta > 0 || $spDelta > 0)) {
            $signals[] = ['dimension' => 'risk_taking', 'polarity' => 'positive', 'magnitude' => 1, 'reason' => "Minor risk: TT{$ttDelta} for MP/SP"];
        }

        // ── collaboration signals ──
        $hasPositiveCrossEffect = false;
        foreach ($crossPlayer as $cp) {
            if (($cp['stat'] === 'sp' || $cp['stat'] === 'tt') && $cp['delta'] > 0) {
                $hasPositiveCrossEffect = true;
            }
        }
        if ($hasPositiveCrossEffect) {
            $signals[] = ['dimension' => 'collaboration', 'polarity' => 'positive', 'magnitude' => 2, 'reason' => "Chose option with positive cross-player effects"];
        }
        if ($ttDelta >= 2 && ($mpDelta <= 0 && $spDelta <= 0)) {
            $signals[] = ['dimension' => 'collaboration', 'polarity' => 'positive', 'magnitude' => 2, 'reason' => "Sacrificed personal stats for team trust"];
        }

        // ── empathy signals ──
        $hasNegativeCrossEffect = false;
        foreach ($crossPlayer as $cp) {
            if ($cp['delta'] < 0) {
                $hasNegativeCrossEffect = true;
            }
        }
        if ($hasPositiveCrossEffect && ($mpDelta <= 0 || $spDelta <= 0)) {
            $signals[] = ['dimension' => 'empathy', 'polarity' => 'positive', 'magnitude' => 2, 'reason' => "Protected team at personal cost"];
        }

        // ── decisiveness signals ──
        $maxAbsDelta = max(abs($mpDelta), abs($spDelta), abs($ttDelta));
        if ($maxAbsDelta >= 3) {
            $signals[] = ['dimension' => 'decisiveness', 'polarity' => 'positive', 'magnitude' => 2, 'reason' => "Strong directional choice (max delta: {$maxAbsDelta})"];
        }

        // ── coaching signals ──
        foreach ($crossPlayer as $cp) {
            if (in_array($cp['stat'], ['sp', 'mp']) && $cp['delta'] > 0) {
                $ownStat = $cp['stat'] === 'sp' ? $spDelta : $mpDelta;
                if ($ownStat <= 0) {
                    $signals[] = ['dimension' => 'coaching', 'polarity' => 'positive', 'magnitude' => 2, 'reason' => "Boosted others' {$cp['stat']} at personal cost"];
                } else {
                    $signals[] = ['dimension' => 'coaching', 'polarity' => 'positive', 'magnitude' => 1, 'reason' => "Boosted others' {$cp['stat']} alongside personal gain"];
                }
            }
        }

        // ── control signals ──
        if (($mpDelta > 0 || $spDelta > 0) && $ttDelta <= -2) {
            $signals[] = ['dimension' => 'control', 'polarity' => 'positive', 'magnitude' => 2, 'reason' => "Maximized personal stats with TT{$ttDelta}"];
        }

        // ── adaptability signals ──
        foreach ($allEffects as $e) {
            if (($e['type'] ?? '') === 'modify_stat' && ($e['params']['stat'] ?? '') === 'flexibility' && ($e['params']['delta'] ?? 0) > 0) {
                $signals[] = ['dimension' => 'adaptability', 'polarity' => 'positive', 'magnitude' => 2, 'reason' => "Chose option that increases flexibility"];
                break;
            }
        }

        return $signals;
    }

    /**
     * Infer pattern signals from decision sequences across turns.
     * Per leadership-framework.md pattern inference signals.
     */
    private function inferPatternSignals(GameTurn $turn, GamePlayer $player): array
    {
        $signals = [];
        $previousTurns = $player->turns()
            ->where('id', '!=', $turn->id)
            ->orderBy('created_at', 'desc')
            ->take(4)
            ->get();

        if ($previousTurns->count() < 2) {
            return $signals;
        }

        // ── adaptability: switching strategies ──
        $choices = $previousTurns->pluck('chosen_option')->toArray();
        $choices[] = $turn->chosen_option;
        $uniqueChoices = count(array_unique($choices));

        if ($uniqueChoices >= 3 && $previousTurns->count() >= 3) {
            $signals[] = ['dimension' => 'adaptability', 'polarity' => 'positive', 'magnitude' => 1, 'reason' => "Switched strategies across recent turns"];
        }

        // ── adaptability negative: same option 5+ consecutive ──
        $recentChoices = $player->turns()
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->pluck('chosen_option')
            ->toArray();

        if (count($recentChoices) >= 5 && count(array_unique($recentChoices)) === 1) {
            $signals[] = ['dimension' => 'adaptability', 'polarity' => 'negative', 'magnitude' => 2, 'reason' => "Same option chosen 5+ consecutive turns"];
        }

        // ── risk_taking negative: consistently chose safer option during crisis ──
        $crisisTurns = $player->turns()
            ->where('dysfunction_triggered', '!=', null)
            ->orderBy('created_at', 'desc')
            ->take(3)
            ->get();

        if ($crisisTurns->count() >= 3) {
            $allSafe = $crisisTurns->every(function ($t) {
                return $t->tt_effect >= 0;
            });
            if ($allSafe) {
                $signals[] = ['dimension' => 'risk_taking', 'polarity' => 'negative', 'magnitude' => 2, 'reason' => "Consistently chose safe options during crisis turns"];
            }
        }

        return $signals;
    }
}
