<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ExpeditionCard;
use Illuminate\Support\Facades\DB;

/**
 * V2 Enhancement Seeder: Adds delayed effects, cross-player effects,
 * hidden information, reputation/resources, and behavior tags to existing cards.
 *
 * Run AFTER the base ExpeditionCardSeeder.
 * Usage: php artisan db:seed --class=V2CardEnhancementSeeder
 */
class V2CardEnhancementSeeder extends Seeder
{
    public function run(): void
    {
        // ── Basecamp Mindset Cards ──

        // Card: Task baru dengan deadline ketat
        $this->enhanceCard('Minta klarifikasi scope', [
            'opsi_a_delayed_effects' => [
                ['after_rounds' => 3, 'stat' => 'mp', 'delta' => 1, 'description' => 'Scope yang jelas mempercepat kerja di giliran berikutnya', 'is_hidden' => false],
            ],
            'opsi_a_behavior_tags' => ['decisiveness' => 0, 'control' => 1],
            'opsi_b_behavior_tags' => ['risk_taking' => 1, 'adaptability' => 1],
            'opsi_a_flexibility' => -1,
            'opsi_b_flexibility' => 1,
        ]);

        // Card: Feedback senior
        $this->enhanceCard('Terima semua feedback', [
            'opsi_a_behavior_tags' => ['empathy' => 1, 'control' => -1],
            'opsi_b_behavior_tags' => ['decisiveness' => 1, 'empathy' => 0],
            'opsi_a_reputation' => 1,
            'opsi_b_reputation' => 0,
        ]);

        // Card: Networking event
        $this->enhanceCard('Pergi ke event', [
            'opsi_a_delayed_effects' => [
                ['after_rounds' => 4, 'stat' => 'sp', 'delta' => 1, 'description' => 'Relasi baru membuka peluang kolaborasi', 'is_hidden' => true],
            ],
            'opsi_a_behavior_tags' => ['collaboration' => 1],
            'opsi_b_behavior_tags' => ['decisiveness' => 0, 'control' => 1],
            'opsi_a_resources' => -1,
        ]);

        // Card: Kesalahan laporan rekan
        $this->enhanceCard('Bicara privat dengan rekanmu', [
            'opsi_a_cross_player' => [
                ['target_type' => 'other_players', 'stat' => 'tt', 'delta' => 1, 'description' => 'Tim merasa kamu bisa dipercaya', 'effect_type' => 'bonus'],
            ],
            'opsi_b_delayed_effects' => [
                ['after_rounds' => 2, 'stat' => 'tt', 'delta' => 2, 'description' => 'Tim akhirnya menghargai integritasmu', 'is_hidden' => true],
            ],
            'opsi_a_behavior_tags' => ['empathy' => 2, 'control' => -1],
            'opsi_b_behavior_tags' => ['decisiveness' => 2, 'empathy' => -2],
            'opsi_a_reputation' => 1,
            'opsi_b_reputation' => -2,
        ]);

        // Card: Nilai vs arahan atasan
        $this->enhanceCard('Ikuti arahan atasan', [
            'opsi_b_behavior_tags' => ['decisiveness' => 1, 'adaptability' => 1],
            'opsi_a_behavior_tags' => ['control' => -1, 'risk_taking' => 0],
            'opsi_b_reputation' => 1,
            'opsi_b_flexibility' => -1,
            'has_hidden_info' => true,
            'hidden_info_reveal' => 'Atasan sebenarnya sedang diuji oleh manajemen atas. Keputusanmu mempengaruhi evaluasinya.',
        ]);

        // ── Basecamp Skillset Cards ──

        // Card: Presentasi progress
        $this->enhanceCard('Fokus pada 3 poin kunci', [
            'opsi_a_behavior_tags' => ['decisiveness' => 1],
            'opsi_b_behavior_tags' => ['collaboration' => 1, 'adaptability' => 1],
            'opsi_a_cross_player' => [
                ['target_type' => 'other_players', 'stat' => 'sp', 'delta' => 1, 'description' => 'Presentasi yang jelas membantu tim memahami progress', 'effect_type' => 'bonus'],
            ],
        ]);

        // Card: Tools baru
        $this->enhanceCard('Ikut training resmi', [
            'opsi_a_delayed_effects' => [
                ['after_rounds' => 3, 'stat' => 'sp', 'delta' => 2, 'description' => 'Sertifikat training meningkatkan kredibilitas', 'is_hidden' => false],
            ],
            'opsi_b_behavior_tags' => ['adaptability' => 1, 'risk_taking' => 1],
            'opsi_a_behavior_tags' => ['control' => 0, 'coaching' => 0],
            'opsi_a_resources' => -2,
        ]);

        // Card: Email tidak dibaca
        $this->enhanceCard('Kirim ulang email', [
            'opsi_b_behavior_tags' => ['collaboration' => 1],
            'opsi_a_behavior_tags' => ['decisiveness' => 1],
        ]);

        // ── Camp Mindset Cards ──
        $campMindset = ExpeditionCard::where('level', 'camp')
            ->where('kategori', 'mindset')
            ->get();

        foreach ($campMindset as $card) {
            // Add some cross-player effects to camp mindset cards
            if ($card->opsi_a_tt >= 1) {
                $card->update([
                    'opsi_a_cross_player' => $card->opsi_a_cross_player ?? [
                        ['target_type' => 'other_players', 'stat' => 'tt', 'delta' => 1, 'description' => 'Keputusanmu meningkatkan trust tim', 'effect_type' => 'bonus'],
                    ],
                    'opsi_a_behavior_tags' => ['empathy' => 1, 'collaboration' => 1],
                ]);
            }

            if ($card->opsi_b_tt >= 1) {
                $card->update([
                    'opsi_b_cross_player' => $card->opsi_b_cross_player ?? [
                        ['target_type' => 'other_players', 'stat' => 'mp', 'delta' => 1, 'description' => 'Tim termotivasi oleh keputusanmu', 'effect_type' => 'bonus'],
                    ],
                    'opsi_b_behavior_tags' => ['coaching' => 1, 'collaboration' => 1],
                ]);
            }

            // Add behavior tags to crisis cards
            if ($card->isKrisis()) {
                $card->update([
                    'opsi_a_behavior_tags' => $card->opsi_a_behavior_tags ?? ['risk_taking' => 0],
                    'opsi_b_behavior_tags' => $card->opsi_b_behavior_tags ?? ['control' => 1],
                    'opsi_a_reputation' => ($card->opsi_a_reputation ?? 0) + 1,
                ]);
            }
        }

        // ── Camp Skillset Cards ──
        $campSkillset = ExpeditionCard::where('level', 'camp')
            ->where('kategori', 'skillset')
            ->get();

        foreach ($campSkillset as $card) {
            // Add delayed effects to some skillset cards
            if ($card->opsi_a_sp >= 2) {
                $card->update([
                    'opsi_a_delayed_effects' => $card->opsi_a_delayed_effects ?? [
                        ['after_rounds' => 2, 'stat' => 'sp', 'delta' => 1, 'description' => 'Skill baru terbukti berguna dalam situasi berikutnya', 'is_hidden' => false],
                    ],
                ]);
            }
        }

        // ── Summit Cards ──
        $summitCards = ExpeditionCard::where('level', 'summit')->get();

        foreach ($summitCards as $card) {
            // All summit cards get stronger cross-player effects
            if ($card->opsi_a_tt >= 1) {
                $card->update([
                    'opsi_a_cross_player' => [
                        ['target_type' => 'all_players', 'stat' => 'tt', 'delta' => 1, 'description' => 'Kepemimpinanmu menginspirasi seluruh tim', 'effect_type' => 'bonus'],
                    ],
                    'opsi_a_reputation' => ($card->opsi_a_reputation ?? 0) + 2,
                    'opsi_a_resources' => ($card->opsi_a_resources ?? 0) - 1,
                ]);
            }

            // Add hidden info to ~30% of summit cards
            if (rand(1, 10) <= 3) {
                $hiddenReveals = [
                    'Ada informasi penting tentang kondisi tim yang baru terungkap setelah keputusanmu.',
                    'Ternyata keputusanmu mempengaruhi strategi pemain lain secara tidak langsung.',
                    'Faktor eksternal yang tidak terlihat sebelumnya memperbesar dampak keputusanmu.',
                    'Tim menilai keputusanmu sebagai benchmark untuk giliran mereka selanjutnya.',
                ];
                $card->update([
                    'has_hidden_info' => true,
                    'hidden_info_reveal' => $hiddenReveals[array_rand($hiddenReveals)],
                ]);
            }

            // Summit crisis cards have shared penalties
            if ($card->isKrisis()) {
                $card->update([
                    'opsi_b_behavior_tags' => ['risk_taking' => 2, 'control' => 1],
                    'opsi_a_behavior_tags' => ['empathy' => 2, 'collaboration' => 1],
                ]);
            }
        }

        $this->command->info('V2 card enhancements applied successfully.');
        $this->command->info('Added: delayed effects, cross-player effects, hidden info, behavior tags, reputation/resources.');
    }

    /**
     * Enhance a card by finding it via text match on option A.
     */
    protected function enhanceCard(string $optionAText, array $updates): void
    {
        $card = ExpeditionCard::where('opsi_a_teks', 'like', "%{$optionAText}%")->first();
        if ($card) {
            $card->update($updates);
        }
    }
}
