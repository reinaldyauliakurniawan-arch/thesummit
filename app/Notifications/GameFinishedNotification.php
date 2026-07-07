<?php
namespace App\Notifications;
use App\Models\GameRoom;use App\Models\GamePlayer;use Illuminate\Bus\Queueable;use Illuminate\Notifications\Notification;use Illuminate\Notifications\Messages\DatabaseMessage;
class GameFinishedNotification extends Notification{use Queueable;
public function __construct(public GameRoom $room,public GamePlayer $player){}
public function via($n):array{return ['database'];}
public function toDatabase($n):array{return ['room_id'=>$this->room->id,'room_code'=>$this->room->code,'message'=>"Game {$this->room->code} selesai!",'url'=>route('game.summary',$this->room)];}
}
