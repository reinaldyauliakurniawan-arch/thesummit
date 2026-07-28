<?php

namespace App\Services;

use App\Models\GameResult;
use App\Models\GamePlayer;
use App\Models\RealWorldChallenge;
use App\Models\LeadershipProfile;
use App\Models\PlayerBehavior;

class ChallengeGenerator
{
    /**
     * Generate a personalized real-world leadership challenge.
     */
    public function generateChallenge(GameResult $result, ?LeadershipProfile $profile = null): RealWorldChallenge
    {
        $player = $result->player;

        // Determine challenge type based on leadership profile or default analysis
        if ($profile) {
            $challengeData = $this->generateFromProfile($profile);
        } else {
            $challengeData = $this->generateFromStats($result);
        }

        return RealWorldChallenge::create([
            'game_player_id'    => $player->id,
            'game_result_id'    => $result->id,
            'challenge'         => $challengeData['challenge'],
            'challenge_type'    => $challengeData['type'],
            'why_this_challenge' => $challengeData['why'],
            'deadline'          => now()->addWeek(),
        ]);
    }

    /**
     * Generate challenge based on leadership profile blind spots.
     */
    protected function generateFromProfile(LeadershipProfile $profile): array
    {
        $blindSpots = $profile->blind_spots ?? [];
        $style = $profile->leadership_style ?? '';

        // Map blind spots to challenges
        $challenges = [
            'risk_taking' => [
                'challenge' => 'Ambil satu keputusan berisiko yang kamu biasanya hindari — bisa berupa pendekatan baru ke proyek, memberikan tanggung jawab baru ke anggota tim, atau mengusulkan ide yang belum teruji.',
                'type'      => 'initiative',
                'why'       => 'Dalam permainan, kamu cenderung menghindari risiko. Latihan ini membantumu membangun keberanian mengambil keputusan dalam situasi tidak pasti.',
            ],
            'collaboration' => [
                'challenge' => 'Delegasikan satu tugas penting sepenuhnya ke anggota tim tanpa micromanage. Biarkan mereka menentukan pendekatan sendiri.',
                'type'      => 'delegate',
                'why'       => 'Kamu cenderung bekerja sendiri. Delegasi efektif adalah keterampilan kepemimpinan yang memperbanyak kapasitas tim.',
            ],
            'empathy' => [
                'challenge' => 'Lakukan percakapan 1-on-1 dengan satu anggota tim fokus pada perasaan dan kebutuhan mereka, bukan tugas atau KPI.',
                'type'      => 'conversation',
                'why'       => 'Kamu cenderung fokus pada hasil daripada orangnya. Memahami perspektif emosional tim membangun trust dan engagement.',
            ],
            'decisiveness' => [
                'challenge' => 'Buat keputusan penting dalam waktu 24 jam tanpa meminta input tambahan. Percayakan instinkmu.',
                'type'      => 'initiative',
                'why'       => 'Kamu cenderung terlalu lama mempertimbangkan sebelum bertindak. Kecepatan keputusan kadang lebih penting dari kesempurnaan.',
            ],
            'coaching' => [
                'challenge' => 'Berikan feedback konstruktif kepada satu anggota tim tentang area pengembangan mereka, spesifik dan actionable.',
                'type'      => 'feedback',
                'why'       => 'Kamu punya potensi sebagai developer leader tapi belum cukup mengaktifkannya. Feedback spesifik adalah langkah pertama coaching.',
            ],
            'control' => [
                'challenge' => 'Hari ini, tanyakan pendapat 3 anggota tim tentang keputusan yang akan kamu buat — dan dengarkan tanpa membela posisimu.',
                'type'      => 'conversation',
                'why'       => 'Kamu cenderung terlalu mengontrol. Mendengarkan tanpa mempertahankan posisi melatih inklusivitas.',
            ],
            'adaptability' => [
                'challenge' => 'Ubah satu proses atau routine kerjamu yang sudah berjalan lama. Coba pendekatan yang sama sekali berbeda selama satu minggu.',
                'type'      => 'initiative',
                'why'       => 'Kamu cenderung setia pada satu pendekatan. Eksperimen membuka perspektif baru dan membangun fleksibilitas.',
            ],
        ];

        // Pick from the biggest blind spot
        $scores = $profile->behavior_scores ?? [];
        arsort($scores);
        $weakest = array_key_last($scores);

        if (isset($challenges[$weakest])) {
            return $challenges[$weakest];
        }

        // Fallback
        return $this->getDefaultChallenge();
    }

    /**
     * Generate challenge based on game stats alone (no profile).
     */
    protected function generateFromStats(GameResult $result): array
    {
        $mp = $result->final_mp;
        $sp = $result->final_sp;
        $tt = $result->final_tt;
        $reputation = $result->final_reputation ?? 0;

        // TT is the team trust indicator
        if ($tt < 5) {
            return [
                'challenge' => 'Lakukan satu tindakan spesifik untuk membangun kepercayaan tim — bisa berupa mengakui kesalahan, berbagi informasi penting, atau memberikan apresiasi publik.',
                'type'      => 'conversation',
                'why'       => "Trust Token akhirmu hanya {$tt}. Dalam tim, trust adalah fondasi semua kolaborasi efektif.",
            ];
        }

        // SP is about leading others
        if ($sp < $mp) {
            return [
                'challenge' => 'Delegasikan satu tugas penting ke anggota tim dan berikan mereka kepercayaan penuh untuk menyelesaikannya.',
                'type'      => 'delegate',
                'why'       => "Skillset Pointsmu ({$sp}) lebih rendah dari Mindset Pointsmu ({$mp}). Ini menunjukkan kamu lebih kuat di self-mastery daripada memimpin orang lain.",
            ];
        }

        // Default
        return $this->getDefaultChallenge();
    }

    /**
     * Get a default challenge.
     */
    protected function getDefaultChallenge(): array
    {
        $challenges = [
            [
                'challenge' => 'Pilih satu anggota tim yang belum kamu ajak berdiskusi mendalam. Ajak ngopi atau lunch 1-on-1 dan tanyakan: "Apa yang bisa saya lakukan untuk mendukungmu lebih baik?"',
                'type'      => 'conversation',
                'why'       => 'Percakapan autentik dengan anggota tim membangun trust dan memberikan insight tentang kebutuhan yang mungkin tidak terlihat.',
            ],
            [
                'challenge' => 'Identifikasi satu keputusan yang sudah kamu tunda. Buat keputusan itu dalam 48 jam dan komunikasikan alasannya ke tim.',
                'type'      => 'initiative',
                'why'       => 'Keputusan yang ditunda menurunkan momentum dan kepercayaan tim pada kepemimpinanmu.',
            ],
        ];

        return $challenges[array_rand($challenges)];
    }
}
