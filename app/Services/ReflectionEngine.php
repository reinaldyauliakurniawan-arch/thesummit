<?php

namespace App\Services;

use App\Models\GameRoom;
use App\Models\GamePlayer;
use App\Models\GameResult;
use App\Models\GameTurn;
use App\Models\LeadershipProfile;
use App\Models\PlayerBehavior;
use App\Models\Consequence;
use App\Models\CrossPlayerEffect;
use Illuminate\Support\Facades\Log;

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

        $behaviorProfile = $this->behaviorTracker->getBehaviorProfile($player);
        $decisionTimeline = $this->buildDecisionTimeline($turns);
        $missedOpportunities = $this->findMissedOpportunities($player, $turns);
        $turningPoint = $this->findKeyTurningPoint($player, $turns);
        $coaching = $this->generateCoachingRecommendations($behaviorProfile);

        return LeadershipProfile::create([
            'game_player_id'           => $player->id,
            'game_result_id'            => $result->id,
            'leadership_style'          => $this->describeStyle($behaviorProfile['style']),
            'strengths'                 => array_values($behaviorProfile['strengths']),
            'blind_spots'               => array_values($behaviorProfile['blind_spots']),
            'decision_timeline'         => $decisionTimeline,
            'missed_opportunities'      => $missedOpportunities,
            'key_turning_point'         => $turningPoint,
            'coaching_recommendations'  => $coaching,
            'behavior_scores'           => $behaviorProfile['scores'],
        ]);
    }

    /**
     * Describe leadership style in narrative form.
     */
    protected function describeStyle(string $style): string
    {
        return match ($style) {
            'visionary'     => 'Visionary Leader — Kamu berani mengambil risiko besar demi tujuan jangka panjang. Tim mengikuti karena kamu menunjukkan kemungkinan yang tidak terlihat orang lain.',
            'cautious'      => 'Cautious Leader — Kamu lebih memilih keputusan yang terukur dan aman. Ini menciptakan stabilitas, tapi kadang membuat tim terlalu nyaman di zona aman.',
            'collaborative' => 'Collaborative Leader — Kamu mengutamakan kerja tim dan keputusan kolektif. Tim merasa dihargai, tapi keputusan bisa memakan waktu lebih lama.',
            'solo'          => 'Individual Contributor — Kamu cenderung bekerja sendiri dan mengandalkan kemampuan pribadi. Hasilmu bagus, tapi tim tidak berkembang karena kurang pelibatan.',
            'empathetic'    => 'Empathetic Leader — Kamu sangat memperhatikan perasaan dan kebutuhan tim. Ini membangun trust kuat, tapi kadang mengorbankan objektivitas.',
            'detached'      => 'Analytical Leader — Kamu fokus pada data dan logika. Keputusanmu terukur tapi tim kadang merasa tidak didengar secara emosional.',
            'decisive'      => 'Decisive Leader — Kamu membuat keputusan cepat dan tegas. Ini menciptakan momentum, tapi kadang keputusan dibuat tanpa input yang cukup.',
            'indecisive'    => 'Deliberate Leader — Kamu mempertimbangkan banyak faktor sebelum bertindak. Ini mengurangi risiko tapi bisa membuat tim merasa lambat.',
            'developer'     => 'Developer Leader — Kamu aktif mengembangkan kemampuan tim. Ini menciptakan pertumbuhan jangka panjang tapi kadang mengorbankan hasil jangka pendek.',
            'directive'     => 'Directive Leader — Kamu memberikan instruksi jelas dan mengarahkan tim. Ini efisien tapi bisa menekan kreativitas dan inisiatif tim.',
            'commanding'    => 'Commanding Leader — Kamu mengontrol situasi dengan tangan kuat. Efektif dalam krisis tapi bisa menghambat otonomi tim dalam situasi normal.',
            'empowering'    => 'Empowering Leader — Kamu memberikan kebebasan kepada tim untuk menentukan pendekatan sendiri. Ini membangun kepercayaan diri tim tapi memerlukan waktu.',
            'adaptive'      => 'Adaptive Leader — Kamu fleksibel dan berubah sesuai situasi. Ini adalah kekuatan besar, tim merasa kamu selalu menemukan jalan.',
            'rigid'         => 'Consistent Leader — Kamu setia pada satu pendekatan. Ini memberikan prediktabilitas tapi kurang efektif saat situasi berubah.',
            'balanced'      => 'Balanced Leader — Kamu menunjukkan keseimbangan antara berbagai gaya kepemimpinan. Ini adalah fondasi yang baik untuk dikembangkan lebih lanjut.',
            default         => 'Emerging Leader — Gaya kepemimpinanmu masih berkembang. Terus bermain dan refleksikan untuk memperjelas preferensi kepemimpinanmu.',
        };
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
                $missed[] = "Turn " . ($turns->search($turn) + 1) . ": Kamu melewatkan kesempatan meningkatkan reputasi tim";
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

        $card = $turningTurn->card;
        $turnNum = $turns->search($turningTurn) + 1;

        $description = "Turn {$turnNum} adalah momen paling berdampak dalam ekspedisimu.";
        if ($turningTurn->dysfunction_triggered) {
            $description .= " Dysfunction terpicu: " . str_replace('_', ' ', $turningTurn->dysfunction_triggered) . ". ";
            $description .= "Cara kamu merespons krisis ini menunjukkan karakter kepemimpinanmu.";
        }
        if ($turningTurn->mp_effect < -2 || $turningTurn->tt_effect < -2) {
            $description .= " Kamu mengorbankan stat penting demi pilihanmu — ini menunjukkan komitmen pada nilai yang kamu yakini.";
        }

        return $description;
    }

    /**
     * Generate coaching recommendations based on behavior profile.
     */
    protected function generateCoachingRecommendations(array $profile): string
    {
        $recommendations = [];

        $style = $profile['style'];
        $scores = $profile['scores'];

        // Style-specific recommendations
        $recommendations[] = match ($style) {
            'visionary' => "Latih diri untuk mengkomunikasikan visi dengan lebih detail ke tim agar mereka bisa eksekusi dengan percaya diri.",
            'cautious' => "Coba ambil satu keputusan berisiko per minggu di dunia nyata. Mulai kecil, evaluasi hasilnya.",
            'collaborative' => "Kerja tim bagus, tapi pastikan kamu juga bisa mengambil keputusan sendiri saat urgensi tinggi.",
            'solo' => "Latih delegasi. Pilih satu tugas penting minggu ini dan serahkan sepenuhnya ke anggota tim.",
            'empathetic' => "Keseimbangkan empati dengan objektivitas. Tidak semua keputusan populer adalah keputusan yang benar.",
            'detached' => "Luangkan waktu untuk memahami perspektif emosional tim sebelum membuat keputusan berbasis data.",
            'decisive' => "Sebelum memutuskan, tanyakan pada diri sendiri: 'Apa input yang belum saya dengar?'",
            'developer' => "Hasil jangka pendek juga penting. Temukan keseimbangan antara mengembangkan tim dan mencapai target.",
            'commanding' => "Praktikkan memberi kepercayaan lebih. Delegasikan keputusan kecil dan izinkan tim belajar dari hasilnya.",
            'adaptive' => "Kekuatan besar! Pastikan tim memahami alasan di balik perubahan arah agar mereka tetap percaya diri.",
            'empowering' => "Pastikan ada struktur yang cukup agar tim tidak merasa ditinggalkan tanpa arahan.",
            default => "Lanjutkan bermain dan perhatikan pola keputusanmu. Setiap sesi akan mengungkap lebih banyak tentang gaya kepemimpinanmu.",
        };

        // Stat-specific recommendations
        $avgRisk = $scores['risk_taking'] ?? 0;
        $avgCollab = $scores['collaboration'] ?? 0;

        if ($avgRisk <= -2) {
            $recommendations[] = "Kamu terlalu menghindari risiko. Dalam kepemimpinan, beberapa risiko perlu diambil agar tim berkembang.";
        }
        if ($avgCollab <= -2) {
            $recommendations[] = "Tingkatkan kolaborasi dengan actively meminta input tim sebelum keputusan penting.";
        }

        return implode(' ', $recommendations);
    }
}
