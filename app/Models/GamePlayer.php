<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GamePlayer extends Model
{
    use HasFactory;

    protected $fillable = [
        'game_room_id',
        'user_id',
        'current_level',
        'mp',
        'sp',
        'tt',
        'turn_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'mp'         => 'integer',
            'sp'         => 'integer',
            'tt'         => 'integer',
            'turn_order' => 'integer',
            'is_active'  => 'boolean',
            'joined_at'  => 'datetime',
        ];
    }

    // ─── Relationships ──────────────────────────────────────────────

    public function room()
    {
        return $this->belongsTo(GameRoom::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function turns()
    {
        return $this->hasMany(GameTurn::class)->orderBy('created_at');
    }

    public function result()
    {
        return $this->hasOne(GameResult::class);
    }

    // ─── Card Tracking ──────────────────────────────────────────────

    /**
     * Get all expedition card IDs this player has already played.
     */
    public function getPlayedCardIds(): array
    {
        return $this->turns()->pluck('expedition_card_id')->toArray();
    }

    /**
     * Get the last 2 card IDs this player played (for soft-reset exclusion).
     */
    public function getLastTwoCardIds(): array
    {
        return $this->turns()
            ->latest()
            ->limit(2)
            ->pluck('expedition_card_id')
            ->toArray();
    }

    // ─── Threshold & Scoring ────────────────────────────────────────

    /**
     * Check if the player meets a named threshold from config.
     *
     * Respects 'tt_required' flag — when false, the TT value is ignored
     * (treated as always passing). This replaces the old implicit tt=>0
     * pattern which was technically correct but error-prone during refactors.
     */
    public function meetsThreshold(string $key): bool
    {
        $threshold = config("summit.thresholds.{$key}");

        if (!$threshold) {
            return false;
        }

        $mpOk = $this->mp >= $threshold['mp'];
        $spOk = $this->sp >= $threshold['sp'];

        // If tt_required is explicitly false, skip the TT check entirely
        $ttOk = ($threshold['tt_required'] === false)
            ? true
            : $this->tt >= $threshold['tt'];

        return $mpOk && $spOk && $ttOk;
    }

    /**
     * Calculate the player's score: (level_value * 10) + TT.
     */
    public function calculateScore(): int
    {
        $levelValue = config("summit.scoring.level_values.{$this->current_level}", 1);
        return ($levelValue * 10) + $this->tt;
    }
}