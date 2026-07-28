<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Consequence extends Model
{
    use HasFactory;

    protected $fillable = [
        'game_room_id',
        'game_player_id',
        'originating_turn_id',
        'effect_type',
        'trigger_type',
        'trigger_value',
        'trigger_condition',
        'stat',
        'delta',
        'description',
        'is_hidden',
        'is_triggered',
        'triggered_at',
    ];

    protected function casts(): array
    {
        return [
            'delta'        => 'integer',
            'is_hidden'    => 'boolean',
            'is_triggered' => 'boolean',
            'trigger_value' => 'integer',
            'triggered_at'  => 'datetime',
        ];
    }

    // ─── Relationships ──────────────────────────────────────────

    public function room()
    {
        return $this->belongsTo(GameRoom::class, 'game_room_id');
    }

    public function player()
    {
        return $this->belongsTo(GamePlayer::class, 'game_player_id');
    }

    public function originatingTurn()
    {
        return $this->belongsTo(GameTurn::class, 'originating_turn_id');
    }

    // ─── Scopes ─────────────────────────────────────────────────

    public function scopePending($query)
    {
        return $query->where('is_triggered', false);
    }

    public function scopeDelayed($query)
    {
        return $query->pending()->where('trigger_type', 'after_rounds');
    }

    public function scopeConditional($query)
    {
        return $query->pending()->where('trigger_type', 'on_condition');
    }

    // ─── Helpers ────────────────────────────────────────────────

    /**
     * Check if this consequence should trigger given the current round number.
     */
    public function shouldTrigger(int $currentRound): bool
    {
        if ($this->is_triggered) {
            return false;
        }

        return match ($this->trigger_type) {
            'after_rounds' => $currentRound >= $this->trigger_value,
            'on_condition' => $this->evaluateCondition(),
            default => false,
        };
    }

    /**
     * Evaluate conditional trigger (simplified - checks stat thresholds).
     */
    public function evaluateCondition(): bool
    {
        if (!$this->trigger_condition) {
            return false;
        }

        $condition = json_decode($this->trigger_condition, true);
        if (!$condition) {
            return false;
        }

        $player = $this->player;
        $stat = $condition['stat'] ?? null;
        $operator = $condition['operator'] ?? '>=';
        $value = $condition['value'] ?? 0;

        if (!$stat || !isset($player->$stat)) {
            return false;
        }

        return match ($operator) {
            '>=' => $player->$stat >= $value,
            '<=' => $player->$stat <= $value,
            '==' => $player->$stat == $value,
            '>'  => $player->$stat > $value,
            '<'  => $player->$stat < $value,
            default => false,
        };
    }
}
