<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;use App\Models\GameRoom;use App\Models\GamePlayer;use App\Services\GameService;use Illuminate\Support\Facades\Auth;
class RoomController extends Controller{
public function index(){$wr=GameRoom::where('status','waiting')->whereHas('players',fn($q)=>$q->where('user_id',Auth::id()))->with(['host','players.user'])->latest()->get();
$ag=GamePlayer::where('user_id',Auth::id())->whereHas('room',fn($q)=>$q->whereIn('status',['in_progress','final_round']))->with('room')->get()->pluck('room')->unique('id');
return view('room.index',compact('wr','ag'));}
public function store(Request $r){$room=GameRoom::create(['host_user_id'=>Auth::id()]);GamePlayer::create(['game_room_id'=>$room->id,'user_id'=>Auth::id(),'turn_order'=>0]);return redirect()->route('rooms.lobby',$room);}
public function join(Request $r,string $code){$room=GameRoom::where('code',$code)->firstOrFail();
if($room->status!=='waiting')return back()->withErrors(['msg'=>'Room sudah dimulai.']);
if($room->players()->where('user_id',Auth::id())->exists())return redirect()->route('rooms.lobby',$room);
if($room->playerCount()>=config('summit.max_players'))return back()->withErrors(['msg'=>'Room penuh.']);
GamePlayer::create(['game_room_id'=>$room->id,'user_id'=>Auth::id(),'turn_order'=>0]);return redirect()->route('rooms.lobby',$room);}
public function leave(Request $r,GameRoom $room){if($room->status!=='waiting')return back()->withErrors(['msg'=>'Tidak bisa keluar.']);
$p=$room->players()->where('user_id',Auth::id())->first();if($p)$p->delete();return redirect()->route('rooms.index');}
public function start(Request $r,GameRoom $room,GameService $gs){if($room->host_user_id!==Auth::id())abort(403);
if($room->playerCount()<config('summit.min_players'))return back()->withErrors(['msg'=>'Minimal 3 pemain.']);
$gs->startGame($room);return redirect()->route('game.board',$room);}
}
