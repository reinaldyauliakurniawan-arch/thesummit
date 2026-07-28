<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlayerBehavior extends Model
{
    use HasFactory;

    const UPDATED_AT = null;

    protected $fillable = [
        'game_player_id',
        'game_turn_id',
        'behavior_type',
        'score',
        'evidence',
        'source',
        'context_modifier',
    ];

    protected function casts(): array
    {
        return [
            'score' => 'integer',
            'context_modifier' => 'float',
            'created_at' => 'datetime',
        ];
    }

    // ─── Relationships ──────────────────────────────────────────

    public function player()
    {
        return $this->belongsTo(GamePlayer::class, 'game_player_id');
    }

    public function turn()
    {
        return $this->belongsTo(GameTurn::class, 'game_turn_id');
    }

    // ─── Behavior types ──────────────────────────────────────────

    public static function behaviorTypes(): array
    {
        return [
            'risk_taking'    => 'Pengambilan Risiko',
            'collaboration'  => 'Kolaborasi',
            'empathy'        => 'Empati',
            'decisiveness'   => 'Keputusan Tegas',
            'coaching'       => 'Coaching',
            'control'        => 'Kontrol',
            'adaptability'   => 'Adaptabilitas',
        ];
    }

    // ─── Aggregation ─────────────────────────────────────────────

    /**
     * Get aggregated behavior scores for a player.
     */
    public static function getAggregateScores(int $playerId): array
    {
        return self::where('game_player_id', $playerId)
            ->selectRaw('behavior_type, SUM(score) as total, COUNT(*) as count')
            ->groupBy('behavior_type')
            ->pluck('total', 'behavior_type')
            ->toArray();
    }

    /**
     * Get the dominant behavior style for a player.
     */
    public static function getDominantStyle(int $playerId): string
    {
        $scores = self::getAggregateScores($playerId);
        if (empty($scores)) {
            return 'balanced';
        }

        $maxScore = max($scores);
        $dominant = array_search($maxScore, $scores);

        // Map to leadership styles
        return match ($dominant) {
            'risk_taking'    => $maxScore > 0 ? 'visionary' : 'cautious',
            'collaboration'  => $maxScore > 0 ? 'collaborative' : 'solo',
            'empathy'        => $maxScore > 0 ? 'empathetic' : 'detached',
            'decisiveness'   => $maxScore > 0 ? 'decisive' : 'indecisive',
            'coaching'       => $maxScore > 0 ? 'developer' : 'directive',
            'control'        => $maxScore > 0 ? 'commanding' : 'empowering',
            'adaptability'   => $maxScore > 0 ? 'adaptive' : 'rigid',
            default           => 'balanced',
        };
    }
}
