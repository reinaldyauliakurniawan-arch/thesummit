<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar',
        'is_admin',
        'has_seen_onboarding',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at'    => 'datetime',
            'password'             => 'hashed',
            'is_admin'             => 'boolean',
            'has_seen_onboarding'  => 'boolean',
        ];
    }

    // ─── Relationships ──────────────────────────────────────────────

    public function hostedRooms()
    {
        return $this->hasMany(GameRoom::class, 'host_user_id');
    }

    public function gamePlayers()
    {
        return $this->hasMany(GamePlayer::class);
    }
}