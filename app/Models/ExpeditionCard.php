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
        $suffix = strtoupper($option);
        if (!in_array($suffix, ['A', 'B'], true)) {
            throw new \InvalidArgumentException("Invalid option: {$option}. Must be 'A' or 'B'.");
        }

        return [
            'mp'    => $this->{"opsi_{$suffix}_mp"},
            'sp'    => $this->{"opsi_{$suffix}_sp"},
            'tt'    => $this->{"opsi_{$suffix}_tt"},
            'extra' => $this->{"opsi_{$suffix}_extra"},
        ];
    }
}