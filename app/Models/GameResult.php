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
        'final_reputation',
        'final_resources',
        'final_flexibility',
    ];

    protected function casts(): array
    {
        return [
            'final_mp'          => 'integer',
            'final_sp'          => 'integer',
            'final_tt'          => 'integer',
            'final_score'       => 'float',
            'rank'              => 'integer',
            'final_reputation'  => 'integer',
            'final_resources'  => 'integer',
            'final_flexibility' => 'integer',
        ];
    }

    // ─── Relationships ──────────────────────────────────────────────

    public function player()
    {
        return $this->belongsTo(GamePlayer::class, 'game_player_id');
    }

    public function leadershipProfile()
    {
        return $this->hasOne(LeadershipProfile::class, 'game_result_id');
    }

    public function realWorldChallenge()
    {
        return $this->hasOne(RealWorldChallenge::class, 'game_result_id');
    }
}
