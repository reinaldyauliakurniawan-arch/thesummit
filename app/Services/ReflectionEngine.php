<?php

namespace App\Services;

use App\Models\GameRoom;
use App\Models\GamePlayer;
use App\Models\GameResult;
use App\Models\GameTurn;
use App\Models\LeadershipProfile;
use App\Models\Consequence;
use Illuminate\Support\Facades\Log;

/**
 * ReflectionEngine — Generates leadership profiles from structured behavior data.
 *
 * LRA REDESIGN: Every insight now cites gameplay evidence.
 * Instead of saying "You are collaborative," it says:
 * "Based on these decisions, here is the evidence we observed..."
 *
 * Every insight must reference specific card decisions.
 * Every recommendation must cite the evidence that supports it.
 * Generic personality descriptions are AVOIDED.
 *
 * This engine produces two layers:
 * 1. LRA Assessment — per-item evidence with confidence scores (defensible)
 * 2. Dimension Profile — legacy 7-dimension analysis (supplementary)
 */
class ReflectionEngine
{
    private BehaviorTracker $behaviorTracker;

    public function __construct(BehaviorTracker $behaviorTracker)
    {
        $this->behaviorTracker = $behaviorTracker;
    }

    /**
     * Generate a full leadership profile for a player after game ends.
     * Now includes LRA assessment with evidence citations.
     */
    public function generateProfile(GameResult $result): LeadershipProfile
    {
        $player = $result->player;
        $room = $result->room;
        $turns = $player->turns()->with('card')->orderBy('created_at')->get();

        // Get the formal behavior profile with confidence scores
        $behaviorProfile = $this->behaviorTracker->getBehaviorProfile($player);

        // Get the LRA assessment with evidence citations
        $lraAssessment = $this->behaviorTracker->getLRAAssessment($player);

        $decisionTimeline = $this->buildDecisionTimeline($turns);
        $missedOpportunities = $this->findMissedOpportunities($player, $turns);
        $keyTurningPoint = $this->findKeyTurningPoint($player, $turns);
        $coachingRecommendations = $this->generateCoachingRecommendations($behaviorProfile);

        $profileData = $behaviorProfile;
        $profileData['decision_timeline'] = $decisionTimeline;
        $profileData['missed_opportunities'] = $missedOpportunities;
        $profileData['key_turning_point'] = $keyTurningPoint;
        $profileData['coaching_recommendations'] = $coachingRecommendations;

        // Build evidence-cited narrative (NOT personality label)
        $styleDescription = $this->describeEvidenceFromLRA($lraAssessment);

        // Generate the full LRA narrative for facilitators
        $lraNarrative = $this->generateLRANarrative($lraAssessment);

        // Extract strength and blind spot names for backward-compatible storage
        $strengthNames = array_column($behaviorProfile['strengths'] ?? [], 'dimension');
        $blindSpotNames = array_column($behaviorProfile['blind_spots'] ?? [], 'dimension');

        return LeadershipProfile::create([
            'game_player_id'           => $player->id,
            'game_result_id'            => $result->id,
            'leadership_style'          => $styleDescription,
            'strengths'                 => $strengthNames ?: array_values($strengthNames),
            'blind_spots'               => $blindSpotNames ?: array_values($blindSpotNames),
            'decision_timeline'         => $decisionTimeline,
            'missed_opportunities'      => $missedOpportunities,
            'key_turning_point'         => $keyTurningPoint,
            'coaching_recommendations'  => $coachingRecommendations,
            'behavior_scores'           => $this->extractScoresForStorage($behaviorProfile),
            'confidence_data'           => $this->extractConfidenceForStorage($behaviorProfile),
            'lra_assessment'             => $lraAssessment,
            'lra_narrative'              => $lraNarrative,
        ]);
    }

    /**
     * Describe leadership style based on LRA evidence — NOT personality labels.
     * Cites specific decisions and evidence patterns.
     */
    protected function describeEvidenceFromLRA(array $lraAssessment): string
    {
        // Find defensible items with strongest evidence
        $defensible = [];
        foreach ($lraAssessment as $code => $item) {
            if ($item['defensible'] && $item['suggested_score'] !== null && $item['suggested_score'] !== 'mixed') {
                $defensible[$code] = $item;
            }
        }

        if (empty($defensible)) {
            return 'Data Belum Cukup — belum cukup bukti konsisten untuk menarik kesimpulan leadership. Main lagi untuk menghasilkan lebih banyak evidence.';
        }

        // Sort by confidence (highest first)
        uasort($defensible, fn($a, $b) => ($b['confidence'] ?? 0) <=> ($a['confidence'] ?? 0));

        // Build evidence-cited narrative
        $narrative = 'Berdasarkan keputusan yang diambil selama permainan: ';

        $items = [];
        foreach (array_slice($defensible, 0, 5) as $code => $item) {
            $score = $item['suggested_score'];
            $label = $item['label'];
            $proving = $item['proving_count'];
            $total = $item['evidence_count'];
            $quality = $item['quality_level'];

            if ($score >= 4) {
                $items[] = ""{$label}" menunjukkan pola kuat ({$proving}/{$total} bukti, quality: {$quality})";
            } elseif ($score >= 3) {
                $items[] = ""{$label}" menunjukkan pola yang konsisten ({$proving}/{$total} bukti, quality: {$quality})";
            } elseif ($score >= 2) {
                $items[] = ""{$label}" menunjukkan area yang perlu pengembangan ({$proving}/{$total} bukti mendukung, quality: {$quality})";
            } else {
                $items[] = ""{$label}" menunjukkan kebutuhan intervensi aktif ({$proving}/{$total} bukti mendukung, quality: {$quality})";
            }
        }

        $narrative .= implode('. ', $items) . '.';

        // Add contradictory items note
        $contradictory = [];
        foreach ($lraAssessment as $code => $item) {
            if ($item['quality_level'] === 'contradictory') {
                $contradictory[] = $item['label'];
            }
        }
        if (!empty($contradictory)) {
            $narrative .= ' Perilaku yang konteks-dependent terdeteksi pada: ' . implode(', ', $contradictory) . '. Ini menunjukkan kemampuan adaptasi, bukan inkonsistensi.';
        }

        return $narrative;
    }

    /**
     * Generate a full LRA narrative for facilitators.
     * This is the evidence document a facilitator uses to defend every conclusion.
     *
     * TASK 1+3+4: Now includes opportunity counts, missed opportunities,
     * and fairness status for every item.
     */
    protected function generateLRANarrative(array $lraAssessment): string
    {
        $lines = [];
        $lines[] = '=== LEADERSHIP ROLE ASSESSMENT — EVIDENCE REPORT ===';
        $lines[] = '';

        // Include opportunity summary if available
        $oppSummary = $lraAssessment['_opportunity_summary'] ?? null;
        if ($oppSummary) {
            $lines[] = '-- ASSESSMENT FAIRNESS SUMMARY --';
            $lines[] = sprintf(
                'Total items: %d | Assessable: %d | No opportunity: %d | Insufficient opportunity: %d | Limited card coverage: %d',
                $oppSummary['total_items'],
                $oppSummary['items_assessable'],
                $oppSummary['items_no_opportunity'],
                $oppSummary['items_insufficient_opportunity'],
                $oppSummary['items_limited_coverage']
            );
            $lines[] = '';
        }

        // Group by tier
        $tiers = ['PtP' => 'Permission to Play', 'R1' => 'Individual Contributor', 'R2' => 'Leading Others', 'R3' => 'Leading Leaders'];

        foreach ($tiers as $tierCode => $tierLabel) {
            $tierItems = array_filter($lraAssessment, fn($item) => ($item['tier'] ?? '') === $tierCode);
            if (empty($tierItems)) continue;

            $lines[] = "--- {$tierLabel} ---";

            foreach ($tierItems as $code => $item) {
                $score = $item['suggested_score'];
                $quality = $item['quality_level'];
                $fairness = $item['fairness_status'] ?? 'unknown';
                $opps = $item['opportunities_presented'] ?? '?';
                $missed = $item['missed_proving_count'] ?? 0;

                if ($score === 'mixed') {
                    $lines[] = "{$item['label']}: KONTRADIKTIF ({$item['proving_count']} mendukung, {$item['disproving_count']} bertentangan, {$opps} opportunities)";
                } elseif ($score === null) {
                    $fairnessLabel = $fairness === 'no_opportunity'
                        ? 'NO OPPORTUNITY'
                        : ($fairness === 'insufficient_opportunity' ? 'INSUFFICIENT OPPORTUNITY' : 'INSUFFICIENT EVIDENCE');
                    $lines[] = "{$item['label']}: {$fairnessLabel} ({$opps} opportunity, {$item['evidence_count']} observasi)";
                } else {
                    $missedText = $missed > 0 ? ", {$missed} missed proving" : '';
                    $lines[] = "{$item['label']}: Score {$score}/5 ({$item['proving_count']}/{$item['evidence_count']} mendukung, quality: {$quality}, {$opps} opportunities{$missedText})";
                }
                $lines[] = "  Evidence: {$item['facilitator_explanation']}";
                $lines[] = '';
            }
        }

        return implode("\n", $lines);
    }

    /**
     * Build a decision timeline summary.
     */
    protected function buildDecisionTimeline($turns): array
    {
        $timeline = [];
        foreach ($turns as $i => $turn) {
            $timeline[] = [
                'turn'    => $i + 1,
                'option'  => $turn->chosen_option,
                'mp'      => $turn->mp_effect,
                'sp'      => $turn->sp_effect,
                'tt'      => $turn->tt_effect,
                'crisis'  => $turn->risk_die_result !== null,
                'dysfunction' => $turn->dysfunction_triggered,
                'level'   => $turn->card ? $turn->card->level : null,
            ];
        }
        return $timeline;
    }

    /**
     * Find missed opportunities based on unchosen options.
     * TASK 3: Now includes LRA-specific missed opportunities.
     */
    protected function findMissedOpportunities(GamePlayer $player, $turns): string
    {
        $missed = [];
        foreach ($turns as $turn) {
            $card = $turn->card;
            if (!$card) continue;

            $chosen = strtolower($turn->chosen_option);
            $other = $chosen === 'a' ? 'b' : 'a';

            // Check reputation opportunity
            $chosenRep = $card->{"opsi_{$chosen}_reputation"} ?? 0;
            $otherRep = $card->{"opsi_{$other}_reputation"} ?? 0;

            if ($otherRep > $chosenRep + 2) {
                $turnNum = $turns->search($turn) + 1;
                $missed[] = "Turn {$turnNum}: Kamu melewatkan kesempatan meningkatkan reputasi tim";
            }

            // TASK 3: Check for missed LRA proving opportunities
            $missedLRA = PlayerBehavior::where('game_player_id', $player->id)
                ->where('game_turn_id', $turn->id)
                ->where('source', 'missed_opportunity')
                ->where('lra_signal', 'missed_proving')
                ->count();

            if ($missedLRA > 0) {
                $turnNum = $turns->search($turn) + 1;
                $missedLraItems = PlayerBehavior::where('game_player_id', $player->id)
                    ->where('game_turn_id', $turn->id)
                    ->where('source', 'missed_opportunity')
                    ->where('lra_signal', 'missed_proving')
                    ->pluck('lra_item')
                    ->map(fn($code) => Config::get("summit.lra.items.{$code}.label", $code))
                    ->join(', ');
                $missed[] = "Turn {$turnNum}: Kamu melewatkan kesempatan mendemonstrasikan: {$missedLraItems}";
            }
        }

        if (empty($missed)) {
            return "Kamu cukup baik memanfaatkan peluang yang ada. Pertimbangkan untuk lebih sering melihat efek jangka panjang dari setiap keputusan.";
        }

        return implode('. ', $missed) . '. Refleksikan apakah ada pola yang bisa diimprovisasi.';
    }

    /**
     * Find the key turning point in the game.
     */
    protected function findKeyTurningPoint(GamePlayer $player, $turns): string
    {
        $maxImpact = 0;
        $turningTurn = null;

        foreach ($turns as $i => $turn) {
            $impact = abs($turn->mp_effect) + abs($turn->sp_effect) + abs($turn->tt_effect);
            if ($turn->risk_die_result) $impact += 2;
            if ($turn->dysfunction_triggered) $impact += 3;

            if ($impact > $maxImpact) {
                $maxImpact = $impact;
                $turningTurn = $turn;
            }
        }

        if (!$turningTurn) {
            return "Tidak ada momen turning point yang dominan. Setiap keputusan memiliki dampak yang relatif merata.";
        }

        $turnNum = $turns->search($turningTurn) + 1;
        $description = "Turn {$turnNum} adalah momen paling berdampak dalam ekspedisimu.";

        if ($turningTurn->dysfunction_triggered) {
            $description .= " Dysfunction terpicu: " . str_replace('_', ' ', $turningTurn->dysfunction_triggered) . ". Cara kamu merespons krisis ini menunjukkan karakter kepemimpinanmu.";
        }
        if ($turningTurn->mp_effect < -2 || $turningTurn->tt_effect < -2) {
            $description .= " Kamu mengorbankan stat penting demi pilihanmu — ini menunjukkan komitmen pada nilai yang kamu yakini.";
        }

        return $description;
    }

    /**
     * Generate coaching recommendations based on behavior profile —
     * now consumes confidence scores to distinguish strong patterns from early signals.
     */
    protected function generateCoachingRecommendations(array $profile): string
    {
        $recommendations = [];
        $strengths = $profile['strengths'] ?? [];
        $blindSpots = $profile['blind_spots'] ?? [];
        $unexplored = $profile['unexplored'] ?? [];
        $dimensions = $profile['dimensions'] ?? [];

        // Recommendations based on blind spots (high confidence = actionable)
        foreach ($blindSpots as $spot) {
            $dim = $spot['dimension'];
            $conf = $spot['confidence'];
            $dimRecs = [
                'risk_taking' => "Latih diri untuk mengambil satu keputusan berisiko per minggu di dunia nyata. Mulai kecil, evaluasi hasilnya.",
                'collaboration' => "Tingkatkan kolaborasi dengan actively meminta input tim sebelum keputusan penting.",
                'empathy' => "Luangkan waktu untuk memahami perspektif emosional tim sebelum membuat keputusan berbasis data.",
                'decisiveness' => "Coba buat keputusan penting dalam waktu 24 jam tanpa meminta input tambahan. Percayakan instinkmu.",
                'coaching' => "Berikan feedback konstruktif kepada satu anggota tim tentang area pengembangan mereka, spesifik dan actionable.",
                'control' => "Hari ini, tanyakan pendapat 3 anggota tim tentang keputusan yang akan kamu buat — dan dengarkan tanpa membela posisimu.",
                'adaptability' => "Ubah satu proses atau routine kerjamu yang sudah berjalan lama. Coba pendekatan yang sama sekali berbeda selama satu minggu.",
            ];

            if (isset($dimRecs[$dim])) {
                $confLabel = $conf >= 0.5 ? ' (pola kuat terdeteksi)' : ' (sinyal awal — perlu konfirmasi)';
                $recommendations[] = $dimRecs[$dim] . $confLabel;
            }
        }

        // Recommendations based on unexplored dimensions
        foreach ($unexplored as $dim) {
            $recommendations[] = "Dimensi '{$dim}' belum tereksplorasi cukup — coba hadapi situasi yang menguji area ini di sesi berikutnya.";
        }

        // Recommendations based on strengths (positive reinforcement)
        foreach ($strengths as $str) {
            $dim = $str['dimension'];
            $conf = $str['confidence'];
            if ($conf >= 0.75) {
                $recommendations[] = "Kekuatanmu di '{$dim}' sangat konsisten — pertimbangkan untuk mentoring orang lain dalam area ini.";
            }
        }

        // Tension-based recommendations
        $tensions = $profile['style']['tensions'] ?? [];
        foreach ($tensions as $tension) {
            $recommendations[] = "Ada ketegangan antara {$tension}. Perhatikan kapan masing-masing pendekatan lebih tepat — konteks menentukan.";
        }

        if (empty($recommendations)) {
            return "Lanjutkan bermain dan perhatikan pola keputusanmu. Setiap sesi akan mengungkap lebih banyak tentang gaya kepemimpinanmu.";
        }

        return implode(' ', $recommendations);
    }

    /**
     * Extract dimension scores for legacy storage format.
     */
    private function extractScoresForStorage(array $profile): array
    {
        $scores = [];
        foreach ($profile['dimensions'] ?? [] as $dim => $data) {
            $scores[$dim] = $data['score'];
        }
        return $scores;
    }

    /**
     * Extract confidence data for storage (new field).
     */
    private function extractConfidenceForStorage(array $profile): array
    {
        $confidenceData = [
            'overall_confidence' => $profile['data_quality']['overall_confidence'] ?? 0,
            'style_confidence' => $profile['style']['confidence'] ?? 0,
            'dimensions' => [],
        ];

        foreach ($profile['dimensions'] ?? [] as $dim => $data) {
            $confidenceData['dimensions'][$dim] = [
                'confidence' => $data['confidence'],
                'consistency' => $data['consistency'],
                'classification' => $data['classification'],
            ];
        }

        return $confidenceData;
    }
}
