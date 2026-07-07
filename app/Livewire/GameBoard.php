<?php
namespace App\Livewire;use Livewire\Component;use App\Models\GameRoom;use App\Models\GamePlayer;use App\Models\ExpeditionCard;use App\Services\GameService;use Illuminate\Support\Facades\Auth;
class GameBoard extends Component{
public GameRoom $room;public GamePlayer $myPlayer;public $currentCard=null;public bool $showCard=false;public bool $showRopeBridge=false;public bool $showEffects=false;public array $lastEffects=[];public $riskDieResult=null;public $dysfunctionTriggered=null;public string $message='';public bool $isMyTurn=false;

public function mount(GameRoom $room){$this->room=$room->load(['players.user','currentPlayer.user','turns.card','turns.player.user']);$this->myPlayer=$room->players()->where('user_id',Auth::id())->firstOrFail();$this->checkTurn();}
public function checkTurn(){$this->room->refresh();$this->myPlayer->refresh();$this->isMyTurn=$this->room->current_turn_player_id===$this->myPlayer->id&&in_array($this->room->status->value,['in_progress','final_round']);}
public function refreshBoard(){$this->room->load(['players.user','currentPlayer.user','turns.card','turns.player.user']);$this->myPlayer->refresh();$this->checkTurn();if($this->room->status->value==='finished')$this->redirect(route('game.summary',$this->room));}

public function drawCard(GameService $gs){if(!$this->isMyTurn)return;$tn=$this->myPlayer->turns()->count()+1;$this->currentCard=$gs->drawCard($this->myPlayer,$tn);$this->showCard=true;$this->showEffects=false;}

public function chooseOption(string $opt,GameService $gs){if(!$this->isMyTurn||!$this->currentCard)return;
$res=$gs->processTurn($this->myPlayer,$opt,$this->currentCard);
$this->lastEffects=$res['effects'];$this->riskDieResult=$res['risk_die'];$this->dysfunctionTriggered=$res['dysfunction'];$this->showCard=false;$this->showEffects=true;
$p=$this->myPlayer->fresh();$lvl=\App\Enums\Level::from($p->current_level);$next=$lvl->next();
if($next){$key='to_'.$next->value;if($p->meetsThreshold($key))$this->showRopeBridge=true;}
if($res['triggered_final_round'])$this->message='Final Round! Semua pemain dapat 1 giliran terakhir.';
$this->currentCard=null;$this->checkTurn();}

public function attemptRopeBridge(GameService $gs){$res=$gs->attemptRopeBridge($this->myPlayer);
$this->message=$res['result']==='success'?'Rope Bridge berhasil! Naik ke '.ucfirst($this->myPlayer->fresh()->current_level).'!':'Rope Bridge gagal. Ambil 1 kartu tambahan.';
$this->showRopeBridge=false;if($res['triggered_final_round'])$this->message.=' Final Round triggered!';
$this->room->refresh();$this->myPlayer->refresh();$this->checkTurn();}

public function skipRopeBridge(){$this->showRopeBridge=false;}

public function render(){$allTurns=$this->room->turns()->with(['card','player.user'])->latest()->take(20)->get()->reverse();$players=$this->room->players()->with('user')->orderBy('turn_order')->get();
return view('livewire.game-board',compact('allTurns','players'))->layout('layouts.app');}
}
