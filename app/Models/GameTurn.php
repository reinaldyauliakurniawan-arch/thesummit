<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GameTurn extends Model
{
    use HasFactory;

    // Turns are immutable once created — no updated_at column
    const UPDATED_AT = null;

    protected $fillable = [
        'game_room_id',
        'game_player_id',
        'expedition_card_id',
        'chosen_option',
        'risk_die_result',
        'mp_effect',
        'sp_effect',
        'tt_effect',
        'extra_effect_applied',
        'rope_bridge_attempted',
        'rope_bridge_success',
        'dysfunction_triggered',
    ];

    protected function casts(): array
    {
        return [
            'risk_die_result'        => 'integer',
            'mp_effect'              => 'integer',
            'sp_effect'              => 'integer',
            'tt_effect'              => 'integer',
            'rope_bridge_attempted'  => 'boolean',
            'rope_bridge_success'    => 'boolean',
        ];
    }

    // ─── Relationships ──────────────────────────────────────────────

    public function player()
    {
        return $this->belongsTo(GamePlayer::class);
    }

    public function card()
    {
        return $this->belongsTo(ExpeditionCard::class);
    }
}