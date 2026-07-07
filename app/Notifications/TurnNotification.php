<?php

namespace App\Notifications;

use App\Models\GameRoom;
use App\Models\GamePlayer;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class TurnNotification extends Notification
{
    use Queueable;

    public function __construct(
        public GameRoom $room,
        public GamePlayer $player
    ) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        return [
            'room_id'   => $this->room->id,
            'room_code' => $this->room->code,
            'message'   => "Giliranmu di ruang {$this->room->code}!",
            'url'       => route('game.board', $this->room),
        ];
    }
}