<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CrossPlayerEffect extends Model
{
    use HasFactory;

    protected $fillable = [
        'game_room_id',
        'source_player_id',
        'target_player_id',
        'game_turn_id',
        'stat',
        'delta',
        'description',
        'effect_type',
    ];

    protected function casts(): array
    {
        return [
            'delta' => 'integer',
        ];
    }

    // ─── Relationships ──────────────────────────────────────────

    public function room()
    {
        return $this->belongsTo(GameRoom::class, 'game_room_id');
    }

    public function sourcePlayer()
    {
        return $this->belongsTo(GamePlayer::class, 'source_player_id');
    }

    public function targetPlayer()
    {
        return $this->belongsTo(GamePlayer::class, 'target_player_id');
    }

    public function turn()
    {
        return $this->belongsTo(GameTurn::class, 'game_turn_id');
    }
}
