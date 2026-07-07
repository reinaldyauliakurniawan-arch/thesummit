<?php
namespace App\Livewire;use Livewire\Component;use App\Models\GameRoom;
class RoomLobby extends Component{public GameRoom $room;public $copied=false;
public function mount(GameRoom $room){$this->room=$room->load(['players.user','host']);}
public function getIsHostProperty(){return $this->room->host_user_id===auth()->id();}
public function getCanStartProperty(){return $this->isHost&&$this->room->status==='waiting'&&$this->room->playerCount()>=config('summit.min_players');}
public function copyCode(){$this->copied=true;}
public function refreshLobby(){$this->room->load(['players.user','host']);}
public function render(){return view('livewire.room-lobby')->layout('layouts.app');}
}
