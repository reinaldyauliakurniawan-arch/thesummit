<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Promise extends Model
{
    use HasFactory;

    const UPDATED_AT = null;

    protected $fillable = [
        'game_room_id',
        'promiser_player_id',
        'recipient_player_id',
        'promise_type',
        'description',
        'is_fulfilled',
        'is_broken',
        'resolved_at',
    ];

    protected function casts(): array
    {
        return [
            'is_fulfilled' => 'boolean',
            'is_broken'    => 'boolean',
            'created_at'   => 'datetime',
            'resolved_at'  => 'datetime',
        ];
    }

    // ─── Relationships ──────────────────────────────────────────

    public function room()
    {
        return $this->belongsTo(GameRoom::class, 'game_room_id');
    }

    public function promiser()
    {
        return $this->belongsTo(GamePlayer::class, 'promiser_player_id');
    }

    public function recipient()
    {
        return $this->belongsTo(GamePlayer::class, 'recipient_player_id');
    }

    // ─── Promise Types ───────────────────────────────────────────

    public static function promiseTypes(): array
    {
        return [
            'vote_for'      => 'Janji Dukungan Suara',
            'help_rescue'   => 'Janji Menolong',
            'share_resource' => 'Janji Berbagi Sumber Daya',
            'support_bridge' => 'Janji Dukungan Rope Bridge',
            'protect_trust'  => 'Janji Melindungi Kepercayaan',
        ];
    }

    // ─── Scopes ─────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('is_fulfilled', false)->where('is_broken', false);
    }

    public function scopeByPromiser($query, int $playerId)
    {
        return $query->where('promiser_player_id', $playerId);
    }

    public function scopeByRecipient($query, int $playerId)
    {
        return $query->where('recipient_player_id', $playerId);
    }
}
