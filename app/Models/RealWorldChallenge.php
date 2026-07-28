<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RealWorldChallenge extends Model
{
    use HasFactory;

    protected $fillable = [
        'game_player_id',
        'game_result_id',
        'challenge',
        'challenge_type',
        'why_this_challenge',
        'is_completed',
        'completion_notes',
        'deadline',
    ];

    protected function casts(): array
    {
        return [
            'is_completed' => 'boolean',
            'deadline'     => 'datetime',
        ];
    }

    // ─── Relationships ──────────────────────────────────────────

    public function player()
    {
        return $this->belongsTo(GamePlayer::class, 'game_player_id');
    }

    public function result()
    {
        return $this->belongsTo(GameResult::class, 'game_result_id');
    }

    // ─── Challenge Types ────────────────────────────────────────

    public static function challengeTypes(): array
    {
        return [
            'delegate'    => 'Delegasi Tugas',
            'feedback'     => 'Memberikan Feedback',
            'conversation' => 'Percakapan Sulit',
            'initiative'  => 'Inisiatif Baru',
            'reflection'  => 'Refleksi Diri',
        ];
    }
}
