<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Enums\GameStatus;
use Illuminate\Support\Str;

class GameRoom extends Model
{
    use HasFactory;

    protected $fillable = [
        'host_user_id',
        'code',
        'status',
        'current_turn_player_id',
        'current_turn_started_at',
        'final_round_started_at',
    ];

    protected function casts(): array
    {
        return [
            'status'                => GameStatus::class,
            'current_turn_started_at' => 'datetime',
            'final_round_started_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (GameRoom $room) {
            if (empty($room->code)) {
                $room->code = strtoupper(Str::random(6));
            }
        });
    }

    // ─── Relationships ──────────────────────────────────────────────

    public function host()
    {
        return $this->belongsTo(User::class, 'host_user_id');
    }

    public function players()
    {
        return $this->hasMany(GamePlayer::class);
    }

    public function currentPlayer()
    {
        return $this->belongsTo(GamePlayer::class, 'current_turn_player_id');
    }

    public function turns()
    {
        return $this->hasMany(GameTurn::class)->orderBy('created_at');
    }

    public function results()
    {
        return $this->hasMany(GameResult::class)->orderBy('rank');
    }

    // ─── Scopes ─────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->whereIn('status', ['waiting', 'in_progress', 'final_round']);
    }

    // ─── Helpers ────────────────────────────────────────────────────

    public function playerCount(): int
    {
        return $this->players()->where('is_active', true)->count();
    }
}