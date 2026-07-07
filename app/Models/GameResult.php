<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GameResult extends Model
{
    use HasFactory;

    protected $fillable = [
        'game_room_id',
        'game_player_id',
        'final_level',
        'final_mp',
        'final_sp',
        'final_tt',
        'final_score',
        'badge',
        'rank',
    ];

    protected function casts(): array
    {
        return [
            'final_mp'    => 'integer',
            'final_sp'    => 'integer',
            'final_tt'    => 'integer',
            'final_score' => 'integer',
            'rank'        => 'integer',
        ];
    }

    // ─── Relationships ──────────────────────────────────────────────

    public function player()
    {
        return $this->belongsTo(GamePlayer::class);
    }
}