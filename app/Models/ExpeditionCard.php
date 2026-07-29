<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExpeditionCard extends Model
{
    use HasFactory;

    protected $table = 'expedition_cards';

    protected $fillable = [
        'level',
        'kategori',
        'tipe',
        'teks_situasi',
        'opsi_a_teks',
        'opsi_a_mp',
        'opsi_a_sp',
        'opsi_a_tt',
        'opsi_a_extra',
        'opsi_b_teks',
        'opsi_b_mp',
        'opsi_b_sp',
        'opsi_b_tt',
        'opsi_b_extra',
        'dysfunction_tag',
        'opsi_a_delayed_effects',
        'opsi_b_delayed_effects',
        'opsi_a_conditional_effects',
        'opsi_b_conditional_effects',
        'opsi_a_cross_player',
        'opsi_b_cross_player',
        'has_hidden_info',
        'hidden_info_reveal',
        'opsi_a_reputation',
        'opsi_b_reputation',
        'opsi_a_resources',
        'opsi_b_resources',
        'opsi_a_flexibility',
        'opsi_b_flexibility',
        'opsi_a_behavior_tags',
        'opsi_b_behavior_tags',
    ];

    protected function casts(): array
    {
        return [
            'opsi_a_mp' => 'integer',
            'opsi_a_sp' => 'integer',
            'opsi_a_tt' => 'integer',
            'opsi_b_mp' => 'integer',
            'opsi_b_sp' => 'integer',
            'opsi_b_tt' => 'integer',
            'opsi_a_delayed_effects' => 'array',
            'opsi_b_delayed_effects' => 'array',
            'opsi_a_conditional_effects' => 'array',
            'opsi_b_conditional_effects' => 'array',
            'opsi_a_cross_player' => 'array',
            'opsi_b_cross_player' => 'array',
            'has_hidden_info' => 'boolean',
            'opsi_a_reputation' => 'integer',
            'opsi_b_reputation' => 'integer',
            'opsi_a_resources' => 'integer',
            'opsi_b_resources' => 'integer',
            'opsi_a_flexibility' => 'integer',
            'opsi_b_flexibility' => 'integer',
            'opsi_a_behavior_tags' => 'array',
            'opsi_b_behavior_tags' => 'array',
        ];
    }

    /**
     * Check if this is a crisis card (triggers Risk Die).
     */
    public function isKrisis(): bool
    {
        return $this->tipe === 'krisis';
    }

    /**
     * Get the effects array for the chosen option (A or B).
     */
    public function getEffects(string $option): array
    {
        $suffix = strtolower($option);
        if (!in_array($suffix, ['a', 'b'], true)) {
            throw new \InvalidArgumentException("Invalid option: {$option}. Must be 'A' or 'B'.");
        }

        return [
            'mp'             => $this->{"opsi_{$suffix}_mp"},
            'sp'             => $this->{"opsi_{$suffix}_sp"},
            'tt'             => $this->{"opsi_{$suffix}_tt"},
            'extra'          => $this->{"opsi_{$suffix}_extra"},
            'reputation'     => $this->{"opsi_{$suffix}_reputation"} ?? 0,
            'resources'      => $this->{"opsi_{$suffix}_resources"} ?? 0,
            'flexibility'    => $this->{"opsi_{$suffix}_flexibility"} ?? 0,
        ];
    }

    /**
     * Get delayed effects for an option.
     */
    public function getDelayedEffects(string $option): array
    {
        $suffix = strtolower($option);
        return $this->{"opsi_{$suffix}_delayed_effects"} ?? [];
    }

    /**
     * Get conditional effects for an option.
     */
    public function getConditionalEffects(string $option): array
    {
        $suffix = strtolower($option);
        return $this->{"opsi_{$suffix}_conditional_effects"} ?? [];
    }

    /**
     * Get cross-player effects for an option.
     */
    public function getCrossPlayerEffects(string $option): array
    {
        $suffix = strtolower($option);
        return $this->{"opsi_{$suffix}_cross_player"} ?? [];
    }

    /**
     * Get behavior tags for an option.
     */
    public function getBehaviorTags(string $option): array
    {
        $suffix = strtolower($option);
        return $this->{"opsi_{$suffix}_behavior_tags"} ?? [];
    }

    /**
     * Check if this card has hidden information for the chosen option.
     */
    public function hasHiddenInfo(string $option): bool
    {
        return $this->has_hidden_info;
    }

    /**
     * Get the hidden info reveal text.
     */
    public function getHiddenInfoReveal(): ?string
    {
        return $this->hidden_info_reveal;
    }
}
