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
        'guest_name',
        'current_level',
        'mp',
        'sp',
        'tt',
        'turn_order',
        'is_active',
        'reputation',
        'resources',
        'flexibility',
        'promises_kept',
        'promises_broken',
    ];

    protected function casts(): array
    {
        return [
            'mp'              => 'integer',
            'sp'              => 'integer',
            'tt'              => 'integer',
            'turn_order'      => 'integer',
            'is_active'       => 'boolean',
            'joined_at'       => 'datetime',
            'reputation'      => 'integer',
            'resources'       => 'integer',
            'flexibility'     => 'integer',
            'promises_kept'   => 'integer',
            'promises_broken' => 'integer',
        ];
    }

    // ─── Relationships ──────────────────────────────────────────────

    public function room()
    {
        return $this->belongsTo(GameRoom::class, 'game_room_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Display name for hotseat/local players: falls back to guest_name
     * when there is no linked User account.
     */
    public function getDisplayNameAttribute(): string
    {
        return $this->user?->name ?? $this->guest_name ?? 'Pemain';
    }

    public function turns()
    {
        return $this->hasMany(GameTurn::class)->orderBy('created_at');
    }

    public function result()
    {
        return $this->hasOne(GameResult::class);
    }

    public function consequences()
    {
        return $this->hasMany(Consequence::class, 'game_player_id');
    }

    public function behaviors()
    {
        return $this->hasMany(PlayerBehavior::class, 'game_player_id');
    }

    public function crossPlayerEffectsGiven()
    {
        return $this->hasMany(CrossPlayerEffect::class, 'source_player_id');
    }

    public function crossPlayerEffectsReceived()
    {
        return $this->hasMany(CrossPlayerEffect::class, 'target_player_id');
    }

    public function leadershipProfile()
    {
        return $this->hasOne(LeadershipProfile::class, 'game_player_id');
    }

    public function realWorldChallenge()
    {
        return $this->hasOne(RealWorldChallenge::class, 'game_player_id');
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
     * Calculate the player's score using gameplay-first formula.
     *
     * Old formula: (level * 10) + TT  — optimizer-friendly, no leadership signal.
     * New formula:
     *   base          = level_value * 10
     *   + TT bonus    = final_tt * 1.5  (still matters but not dominant)
     *   + rep bonus   = reputation (capped at +5, floor at -5)
     *   + leadership  = evidence diversity bonus (0-5 pts)
     *   - selfish tax  = penalty if promises_broken > promises_kept
     *
     * Key changes vs old:
     * 1. Reputation matters — selfish play now has a cost.
     * 2. Evidence diversity rewards versatile leaders over stat-hoarders.
     * 3. Selfish tax punishes promise-breaking.
     * 4. TT weighted at 1.5x instead of 1:1 — prevents pure TT optimization.
     */
    public function calculateScore(): float
    {
        $levelValue = config("summit.scoring.level_values.{$this->current_level}", 1);

        // Base: progression still matters most
        $base = $levelValue * 10;

        // TT bonus: team trust, but capped to prevent min-maxing
        $ttBonus = min($this->tt * 1.5, 15);

        // Reputation bonus: social capital (capped)
        $repBonus = max(-5, min(5, $this->reputation ?? 0));

        // Leadership diversity bonus: how many different behaviors demonstrated
        $diversityBonus = $this->calculateLeadershipDiversityBonus();

        // Selfish tax: if you broke more promises than you kept
        $selfishTax = 0;
        $kept = $this->promises_kept ?? 0;
        $broken = $this->promises_broken ?? 0;
        if ($broken > $kept && $broken > 0) {
            $selfishTax = min(($broken - $kept) * 2, 10);
        }

        return round($base + $ttBonus + $repBonus + $diversityBonus - $selfishTax, 1);
    }

    /**
     * Calculate leadership diversity bonus based on evidence spread.
     * Returns 0-5 points based on how many distinct behavior dimensions
     * the player demonstrated with confidence >= 0.5.
     *
     * Uses behavior_data stored on turns (via BehaviorTracker).
     * Falls back to 0 if insufficient data.
     */
    public function calculateLeadershipDiversityBonus(): int
    {
        // Count distinct behavior types from turn data
        $behaviorTypes = $this->turns()
            ->whereNotNull('behavior_data')
            ->pluck('behavior_data')
            ->flatMap(function ($data) {
                // behavior_data is cast to 'array' on GameTurn model, so Eloquent's
                // pluck() already returns decoded PHP arrays here — do NOT json_decode again.
                // Only fall back to json_decode if somehow still a raw string (defensive).
                $decoded = is_array($data) ? $data : json_decode((string) $data, true);
                if (!is_array($decoded)) return [];
                // Get keys where magnitude >= 1 (meaningful evidence)
                return array_keys(array_filter($decoded, fn($v) => abs($v ?? 0) >= 1));
            })
            ->unique()
            ->count();

        // 0 types = 0 bonus, 1 = 0, 2 = 1, 3 = 2, 4 = 3, 5 = 4, 6+ = 5
        return min(max($behaviorTypes - 1, 0), 5);
    }

    /**
     * Check if this player qualifies for The Carrier badge.
     * Requirements: Summit + TT >= 8 + reputation >= 0 + net positive promises.
     */
    public function qualifiesAsCarrier(): bool
    {
        return $this->current_level === 'summit'
            && $this->tt >= 8
            && ($this->reputation ?? 0) >= 0
            && ($this->promises_kept ?? 0) >= ($this->promises_broken ?? 0);
    }

    /**
     * Check if this player qualifies for The Catalyst badge.
     * Requirements: Did NOT summit + highest TT in room + gave positive cross-player effects.
     */
    public function qualifiesAsCatalyst(): bool
    {
        if ($this->current_level === 'summit') {
            return false;
        }

        $room = $this->room;
        if (!$room) return false;

        // Must have given positive cross-player effects
        $positiveEffectsGiven = $this->crossPlayerEffectsGiven()
            ->where('effect_delta', '>', 0)
            ->exists();

        if (!$positiveEffectsGiven) return false;

        // Must be top 2 in TT among non-summit players
        $maxTT = $room->players()
            ->where('is_active', true)
            ->where('current_level', '!=', 'summit')
            ->max('tt');

        return $this->tt >= ($maxTT - 1);
    }

    /**
     * Check if this player qualifies for The Strategist badge.
     * Requirements: 4+ distinct leadership behaviors with meaningful evidence.
     */
    public function qualifiesAsStrategist(): bool
    {
        return $this->calculateLeadershipDiversityBonus() >= 4;
    }
}
