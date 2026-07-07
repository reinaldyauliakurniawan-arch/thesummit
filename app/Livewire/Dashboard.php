<?php
namespace App\Livewire;use Livewire\Component;use Illuminate\Support\Facades\Auth;use App\Models\GameRoom;
class Dashboard extends Component{
public function render(){$u=Auth::user();
$wr=GameRoom::where('status','waiting')->whereHas('players',fn($q)=>$q->where('user_id',$u->id))->with(['host','players.user'])->latest()->get();
$ag=\App\Models\GamePlayer::where('user_id',$u->id)->whereHas('room',fn($q)=>$q->whereIn('status',['in_progress','final_round','finished']))->with('room')->get();
$un=$u->unreadNotifications()->take(10)->get();
return view('livewire.dashboard',compact('wr','ag','un'))->layout('layouts.app');}
public function markRead($id){Auth::user()->unreadNotifications()->where('id',$id)->update(['read_at'=>now()]);}
}
