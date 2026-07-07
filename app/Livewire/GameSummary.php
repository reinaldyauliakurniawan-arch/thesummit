<?php
namespace App\Livewire;use Livewire\Component;use App\Models\GameRoom;
class GameSummary extends Component{public GameRoom $room;
public function mount(GameRoom $room){$this->room=$room->load(['results.player.user','players.user']);}
public function render(){$results=$this->room->results()->with('player.user')->orderBy('rank')->get();$turns=$this->room->turns()->with(['card','player.user'])->orderBy('created_at')->get();
return view('livewire.game-summary',compact('results','turns'))->layout('layouts.app');}
}
