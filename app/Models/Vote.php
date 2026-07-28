<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vote extends Model
{
    use HasFactory;

    protected $fillable = [
        'game_room_id',
        'triggering_player_id',
        'vote_topic',
        'vote_description',
        'vote_type',
        'options',
        'votes_cast',
        'is_resolved',
        'result',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'options'    => 'array',
            'votes_cast' => 'array',
            'is_resolved' => 'boolean',
            'expires_at'  => 'datetime',
        ];
    }

    // ─── Relationships ──────────────────────────────────────────

    public function room()
    {
        return $this->belongsTo(GameRoom::class, 'game_room_id');
    }

    public function triggeringPlayer()
    {
        return $this->belongsTo(GamePlayer::class, 'triggering_player_id');
    }

    // ─── Helpers ────────────────────────────────────────────────

    /**
     * Cast a vote from a player.
     */
    public function castVote(int $playerId, string $choice): void
    {
        $votes = $this->votes_cast ?? [];
        $votes[$playerId] = $choice;
        $this->votes_cast = $votes;
        $this->save();
    }

    /**
     * Check if a player has already voted.
     */
    public function hasVoted(int $playerId): bool
    {
        return isset(($this->votes_cast ?? [])[$playerId]);
    }

    /**
     * Count total votes cast.
     */
    public function voteCount(): int
    {
        return count($this->votes_cast ?? []);
    }

    /**
     * Get the winning option.
     */
    public function getWinner(): ?string
    {
        if (empty($this->votes_cast)) {
            return null;
        }

        $tallies = array_count_values(array_values($this->votes_cast));
        arsort($tallies);

        return array_key_first($tallies);
    }

    // ─── Scopes ─────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('is_resolved', false);
    }
}
