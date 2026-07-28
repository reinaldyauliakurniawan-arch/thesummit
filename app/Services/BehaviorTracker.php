<?php

namespace App\Services;

use App\Models\GameRoom;
use App\Models\GamePlayer;
use App\Models\GameTurn;
use App\Models\CrossPlayerEffect;
use App\Models\PlayerBehavior;
use Illuminate\Support\Facades\DB;

class BehaviorTracker
{
    /**
     * Analyze a turn's decision and record behavior observations.
     */
    public function trackBehaviors(GameTurn $turn, GamePlayer $player, array $cardData): array
    {
        $behaviors = [];
        $option = strtolower($turn->chosen_option);
        $behaviorTags = $cardData['behavior_tags'] ?? [];
        $effects = $cardData['effects'] ?? [];

        // Analyze behavior from card tags
        foreach ($behaviorTags as $tag => $score) {
            PlayerBehavior::create([
                'game_player_id' => $player->id,
                'game_turn_id'   => $turn->id,
                'behavior_type'  => $tag,
                'score'          => $score,
                'evidence'       => "Pilihan {$turn->chosen_option}: {$cardData['option_text']}",
            ]);
            $behaviors[$tag] = $score;
        }

        // Auto-detect behaviors from decision patterns
        $this->detectRiskTaking($turn, $player, $cardData, $behaviors);
        $this->detectCollaboration($turn, $player, $cardData, $behaviors);
        $this->detectDecisiveness($turn, $player, $cardData, $behaviors);
        $this->detectEmpathy($turn, $player, $cardData, $behaviors);
        $this->detectControl($turn, $player, $cardData, $behaviors);
        $this->detectAdaptability($turn, $player, $cardData, $behaviors);
        $this->detectCoaching($turn, $player, $cardData, $behaviors);

        return $behaviors;
    }

    /**
     * Detect risk-taking behavior.
     */
    protected function detectRiskTaking(GameTurn $turn, GamePlayer $player, array $cardData, array &$behaviors): void
    {
        if (isset($behaviors['risk_taking'])) {
            return;
        }

        $isKrisis = $cardData['is_krisis'] ?? false;
        $mpDelta = $turn->mp_effect;
        $ttDelta = $turn->tt_effect;

        // Choosing lower TT for higher MP/SP = risk-taking
        if ($ttDelta < 0 && ($mpDelta > 0 || $turn->sp_effect > 0)) {
            PlayerBehavior::create([
                'game_player_id' => $player->id,
                'game_turn_id'   => $turn->id,
                'behavior_type'  => 'risk_taking',
                'score'          => 1,
                'evidence'       => "Memilih opsi berisiko: TT{$ttDelta} demi MP{$mpDelta}/SP{$turn->sp_effect}",
            ]);
            $behaviors['risk_taking'] = 1;
        } elseif ($isKrisis && $ttDelta >= 0) {
            PlayerBehavior::create([
                'game_player_id' => $player->id,
                'game_turn_id'   => $turn->id,
                'behavior_type'  => 'risk_taking',
                'score'          => 0,
                'evidence'       => "Memilih opsi aman saat krisis",
            ]);
            $behaviors['risk_taking'] = 0;
        }
    }

    /**
     * Detect collaboration behavior.
     */
    protected function detectCollaboration(GameTurn $turn, GamePlayer $player, array $cardData, array &$behaviors): void
    {
        if (isset($behaviors['collaboration'])) {
            return;
        }

        $ttDelta = $turn->tt_effect;
        $crossPlayer = $cardData['cross_player'] ?? [];

        if ($ttDelta > 0 && !empty($crossPlayer)) {
            PlayerBehavior::create([
                'game_player_id' => $player->id,
                'game_turn_id'   => $turn->id,
                'behavior_type'  => 'collaboration',
                'score'          => 2,
                'evidence'       => "Mengorbankan progress pribadi untuk tim: TT+{$ttDelta} dengan efek tim",
            ]);
            $behaviors['collaboration'] = 2;
        } elseif ($ttDelta >= 0 && $turn->mp_effect <= 0) {
            PlayerBehavior::create([
                'game_player_id' => $player->id,
                'game_turn_id'   => $turn->id,
                'behavior_type'  => 'collaboration',
                'score'          => 1,
                'evidence'       => "Memilih opsi yang mendukung tim di atas progress pribadi",
            ]);
            $behaviors['collaboration'] = 1;
        }
    }

    /**
     * Detect decisiveness behavior.
     */
    protected function detectDecisiveness(GameTurn $turn, GamePlayer $player, array $cardData, array &$behaviors): void
    {
        if (isset($behaviors['decisiveness'])) {
            return;
        }

        $effectsA = $cardData['effects_a'] ?? [];
        $effectsB = $cardData['effects_b'] ?? [];
        $choice = strtolower($turn->chosen_option);

        // Decisive = choosing the option with stronger trade-offs (one clear winner in one area)
        $chosenEffects = $choice === 'a' ? $effectsA : $effectsB;
        $maxEffect = max(abs($chosenEffects['mp'] ?? 0), abs($chosenEffects['sp'] ?? 0), abs($chosenEffects['tt'] ?? 0));

        if ($maxEffect >= 3) {
            PlayerBehavior::create([
                'game_player_id' => $player->id,
                'game_turn_id'   => $turn->id,
                'behavior_type'  => 'decisiveness',
                'score'          => 1,
                'evidence'       => "Keputusan tegas dengan dampak besar (max delta: {$maxEffect})",
            ]);
            $behaviors['decisiveness'] = 1;
        }
    }

    /**
     * Detect empathy behavior.
     */
    protected function detectEmpathy(GameTurn $turn, GamePlayer $player, array $cardData, array &$behaviors): void
    {
        if (isset($behaviors['empathy'])) {
            return;
        }

        $ttDelta = $turn->tt_effect;
        $crossPlayer = $cardData['cross_player'] ?? [];

        if ($ttDelta > 0 && !empty($crossPlayer)) {
            PlayerBehavior::create([
                'game_player_id' => $player->id,
                'game_turn_id'   => $turn->id,
                'behavior_type'  => 'empathy',
                'score'          => 1,
                'evidence'       => "Memilih opsi yang menunjukkan kepedulian terhadap tim",
            ]);
            $behaviors['empathy'] = 1;
        }
    }

    /**
     * Detect control behavior.
     */
    protected function detectControl(GameTurn $turn, GamePlayer $player, array $cardData, array &$behaviors): void
    {
        if (isset($behaviors['control'])) {
            return;
        }

        $mpDelta = $turn->mp_effect;
        $spDelta = $turn->sp_effect;
        $ttDelta = $turn->tt_effect;

        // Control = maximizing personal stats at expense of TT
        if (($mpDelta > 0 || $spDelta > 0) && $ttDelta < -1) {
            PlayerBehavior::create([
                'game_player_id' => $player->id,
                'game_turn_id'   => $turn->id,
                'behavior_type'  => 'control',
                'score'          => 1,
                'evidence'       => "Mengutamakan stat pribadi (MP+{$mpDelta}, SP+{$spDelta}) meski TT{$ttDelta}",
            ]);
            $behaviors['control'] = 1;
        }
    }

    /**
     * Detect adaptability behavior.
     */
    protected function detectAdaptability(GameTurn $turn, GamePlayer $player, array $cardData, array &$behaviors): void
    {
        if (isset($behaviors['adaptability'])) {
            return;
        }

        // Check if player has been changing strategies (alternating between A and B)
        $previousTurns = $player->turns()->orderBy('created_at', 'desc')->take(3)->get();
        if ($previousTurns->count() >= 2) {
            $choices = $previousTurns->pluck('chosen_option')->toArray();
            $uniqueChoices = count(array_unique($choices));

            if ($uniqueChoices >= 2) {
                PlayerBehavior::create([
                    'game_player_id' => $player->id,
                    'game_turn_id'   => $turn->id,
                    'behavior_type'  => 'adaptability',
                    'score'          => 1,
                    'evidence'       => "Berpindah strategi antar giliran",
                ]);
                $behaviors['adaptability'] = 1;
            }
        }
    }

    /**
     * Detect coaching behavior.
     */
    protected function detectCoaching(GameTurn $turn, GamePlayer $player, array $cardData, array &$behaviors): void
    {
        if (isset($behaviors['coaching'])) {
            return;
        }

        $crossPlayer = $cardData['cross_player'] ?? [];
        $spDelta = $turn->sp_effect;

        // Coaching = boosting others' SP or TT through cross-player effects
        $hasPositiveCrossEffect = false;
        foreach ($crossPlayer as $cp) {
            if (($cp['stat'] === 'sp' || $cp['stat'] === 'tt') && $cp['delta'] > 0) {
                $hasPositiveCrossEffect = true;
            }
        }

        if ($hasPositiveCrossEffect && $spDelta <= 0) {
            PlayerBehavior::create([
                'game_player_id' => $player->id,
                'game_turn_id'   => $turn->id,
                'behavior_type'  => 'coaching',
                'score'          => 2,
                'evidence'       => "Mengorbankan SP pribadi untuk meningkatkan tim",
            ]);
            $behaviors['coaching'] = 2;
        } elseif ($hasPositiveCrossEffect) {
            PlayerBehavior::create([
                'game_player_id' => $player->id,
                'game_turn_id'   => $turn->id,
                'behavior_type'  => 'coaching',
                'score'          => 1,
                'evidence'       => "Memilih opsi yang membantu pengembangan tim",
            ]);
            $behaviors['coaching'] = 1;
        }
    }

    /**
     * Get complete behavior analysis for a player.
     */
    public function getBehaviorProfile(GamePlayer $player): array
    {
        $scores = PlayerBehavior::getAggregateScores($player->id);
        $style = PlayerBehavior::getDominantStyle($player->id);

        // Determine strengths (top 3 positive scores)
        $strengths = collect($scores)
            ->filter(fn ($score) => $score > 0)
            ->sortDesc()
            ->take(3)
            ->mapWithKeys(fn ($score, $type) => [
                $type => PlayerBehavior::behaviorTypes()[$type] ?? $type,
            ])
            ->toArray();

        // Determine blind spots (top 3 negative or lowest scores)
        $blindSpots = collect($scores)
            ->sort()
            ->take(3)
            ->mapWithKeys(fn ($score, $type) => [
                $type => PlayerBehavior::behaviorTypes()[$type] ?? $type,
            ])
            ->toArray();

        return [
            'style'       => $style,
            'scores'      => $scores,
            'strengths'   => $strengths,
            'blind_spots' => $blindSpots,
        ];
    }
}
