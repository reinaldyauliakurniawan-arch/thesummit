<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeadershipProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'game_player_id',
        'game_result_id',
        'leadership_style',
        'strengths',
        'blind_spots',
        'decision_timeline',
        'missed_opportunities',
        'key_turning_point',
        'coaching_recommendations',
        'behavior_scores',
        'confidence_data',
        'lra_assessment',
        'lra_narrative',
    ];

    protected function casts(): array
    {
        return [
            'strengths'         => 'array',
            'blind_spots'       => 'array',
            'decision_timeline' => 'array',
            'behavior_scores'   => 'array',
            'confidence_data'   => 'array',
            'lra_assessment'     => 'array',
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
}
