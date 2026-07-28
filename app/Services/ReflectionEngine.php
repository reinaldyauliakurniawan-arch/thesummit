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
 * Consumes the structured profile from BehaviorTracker (which includes confidence scores).
 * This engine is responsible ONLY for:
 * 1. Building the decision timeline
 * 2. Finding missed opportunities
 * 3. Detecting key turning points
 * 4. Generating coaching recommendations
 * 5. Persisting the LeadershipProfile
 *
 * Narrative generation is a separate concern — the UI layer handles display formatting.
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
     */
    public function generateProfile(GameResult $result): LeadershipProfile
    {
        $player = $result->player;
        $room = $result->room;
        $turns = $player->turns()->with('card')->orderBy('created_at')->get();

        // Get the formal behavior profile with confidence scores
        $behaviorProfile = $this->behaviorTracker->getBehaviorProfile($player);

        $decisionTimeline = $this->buildDecisionTimeline($turns);
        $missedOpportunities = $this->findMissedOpportunities($player, $turns);
        $keyTurningPoint = $this->findKeyTurningPoint($player, $turns);
        $coachingRecommendations = $this->generateCoachingRecommendations($behaviorProfile);

        $profileData = $behaviorProfile;
        $profileData['decision_timeline'] = $decisionTimeline;
        $profileData['missed_opportunities'] = $missedOpportunities;
        $profileData['key_turning_point'] = $keyTurningPoint;
        $profileData['coaching_recommendations'] = $coachingRecommendations;

        // Build human-readable style description from the structured data
        $styleDescription = $this->describeStyleFromProfile($behaviorProfile);

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
        ]);
    }

    /**
     * Describe leadership style based on the formal profile — distinguishing
     * "strong pattern" from "early signal" per the user's requirement.
     */
    protected function describeStyleFromProfile(array $profile): string
    {
        $style = $profile['style'] ?? [];
        $primary = $style['primary'] ?? 'emerging';
        $confidence = $style['confidence'] ?? 0;
        $secondary = $style['secondary'] ?? null;

        // Map structured styles to narrative descriptions WITH confidence context
        $descriptions = [
            'risk_taking'    => 'Visionary Leader — berani mengambil risiko besar demi tujuan jangka panjang.',
            'collaboration'  => 'Collaborative Leader — mengutamakan kerja tim dan keputusan kolektif.',
            'empathy'        => 'Empathetic Leader — memperhatikan perasaan dan kebutuhan tim.',
            'decisiveness'   => 'Decisive Leader — membuat keputusan cepat dan tegas.',
            'coaching'       => 'Developer Leader — aktif mengembangkan kemampuan tim.',
            'control'        => 'Commanding Leader — mengontrol situasi dengan tangan kuat.',
            'adaptability'   => 'Adaptive Leader — fleksibel dan berubah sesuai situasi.',
        ];

        if ($primary === 'insufficient_data') {
            return 'Data Belum Cukup — gaya kepemimpinanmu masih berkembang. Terus bermain untuk mengungkap lebih banyak.';
        }

        if ($primary === 'emerging') {
            return 'Emerging Leader — pola kepemimpinanmu mulai terlihat tapi belum cukup konsisten. Terus bermain dan refleksikan.';
        }

        $baseDescription = $descriptions[$primary] ?? "Leader dengan gaya {$primary}.";

        // Append confidence qualifier
        if ($confidence >= 0.75) {
            $baseDescription .= ' Pola ini sudah sangat konsisten — strong pattern.';
        } elseif ($confidence >= 0.5) {
            $baseDescription .= ' Pola ini cukup jelas tapi masih bisa berkembang — established signal.';
        } elseif ($confidence >= 0.25) {
            $baseDescription .= ' Arah sudah terlihat tapi data masih terbatas — early signal.';
        } else {
            $baseDescription .= ' Belum cukup data untuk mengkonfirmasi pola ini — speculative.';
        }

        // Add secondary style if present
        if ($secondary && isset($descriptions[$secondary])) {
            $baseDescription .= " Gaya sekunder: {$secondary}.";
        }

        // Add tension notes
        if (!empty($style['tensions'])) {
            $baseDescription .= ' Tensi terdeteksi: ' . implode(', ', $style['tensions']) . '.';
        }

        return $baseDescription;
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
     */
    protected function findMissedOpportunities(GamePlayer $player, $turns): string
    {
        $missed = [];
        foreach ($turns as $turn) {
            $card = $turn->card;
            if (!$card) continue;

            $chosen = strtolower($turn->chosen_option);
            $other = $chosen === 'a' ? 'b' : 'a';

            $chosenRep = $card->{"opsi_{$chosen}_reputation"} ?? 0;
            $otherRep = $card->{"opsi_{$other}_reputation"} ?? 0;

            if ($otherRep > $chosenRep + 2) {
                $turnNum = $turns->search($turn) + 1;
                $missed[] = "Turn {$turnNum}: Kamu melewatkan kesempatan meningkatkan reputasi tim";
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
