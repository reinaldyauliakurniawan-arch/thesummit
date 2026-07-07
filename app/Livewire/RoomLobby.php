<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\GameRoom;

class RoomLobby extends Component
{
    public GameRoom $room;
    public bool $copied = false;

    public function mount(GameRoom $room): void
    {
        $this->room = $room->load(['players.user', 'host']);
    }

    public function getIsHostProperty(): bool
    {
        return $this->room->host_user_id === auth()->id();
    }

    public function getCanStartProperty(): bool
    {
        return $this->isHost
            && $this->room->status === 'waiting'
            && $this->room->playerCount() >= config('summit.min_players');
    }

    public function copyCode(): void
    {
        $this->copied = true;
    }

    public function refreshLobby(): void
    {
        $this->room->load(['players.user', 'host']);
    }

    public function render()
    {
        return view('livewire.room-lobby')->layout('layouts.app');
    }
}