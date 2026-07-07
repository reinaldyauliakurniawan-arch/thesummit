<?php
namespace App\Services;
use App\Enums\GameStatus;use App\Enums\Level;use App\Enums\Badge;
use App\Models\GameRoom;use App\Models\GamePlayer;use App\Models\ExpeditionCard;use App\Models\GameTurn;use App\Models\GameResult;
use Illuminate\Support\Facades\DB;

class GameService{
  public function drawCard(GamePlayer $p,int $turnNum):ExpeditionCard{
    $level=$p->current_level;$cat=($turnNum%2===1)?'mindset':'skillset';
    $played=$p->getPlayedCardIds();$last2=$p->getLastTwoCardIds();
    $pool=ExpeditionCard::where('level',$level)->where('kategori',$cat)->when(!empty($played),fn($q)=>$q->whereNotIn('id',$played));
    if($pool->count()===0) $pool=ExpeditionCard::where('level',$level)->where('kategori',$cat)->when(!empty($last2),fn($q)=>$q->whereNotIn('id',$last2));
    return $pool->inRandomOrder()->firstOrFail();
  }

  public function applyCardEffects(GamePlayer $p,ExpeditionCard $c,string $opt):array{
    $e=$c->getEffects($opt);$p->mp=max(0,$p->mp+$e['mp']);$p->sp=max(0,$p->sp+$e['sp']);$p->tt=max(0,$p->tt+$e['tt']);$p->save();return $e;
  }

  public function rollRiskDie():int{return rand(1,6);}

  public function resolveRiskDie(int $roll):array{
    $cfg=config('summit.risk_die');$tt=0;$dys=null;
    if(in_array($roll,$cfg['dysfunction_range'])){$tt=$cfg['dysfunction_tt_penalty'];$dys=array_rand(config('summit.dysfunctions'));}
    elseif(in_array($roll,$cfg['bonus_range'])){$tt=$cfg['bonus_tt_reward'];}
    return ['roll'=>$roll,'tt_delta'=>$tt,'dysfunction'=>$dys];
  }

  public function checkRopeBridge(GamePlayer $p):?string{
    $lvl=Level::from($p->current_level);$next=$lvl->next();if(!$next)return null;
    $key='to_'.$next->value;
    if($p->meetsThreshold($key)){$p->current_level=$next->value;$p->save();return 'success';}
    return 'fail';
  }

  public function checkFinalWin(GamePlayer $p):bool{return $p->current_level==='summit'&&$p->meetsThreshold('final_win');}

  public function processTurn(GamePlayer $p,string $chosen,?ExpeditionCard $card=null):array{
    return DB::transaction(function()use($p,$chosen,$card){
      $room=$p->room;$tn=$p->turns()->count()+1;
      if(!$card)$card=$this->drawCard($p,$tn);
      $fx=$this->applyCardEffects($p,$card,$chosen);
      $mpF=$fx['mp'];$spF=$fx['sp'];$ttF=$fx['tt'];$extra=$fx['extra'];$riskR=null;$dys=null;
      if($card->isKrisis()){$riskR=$this->rollRiskDie();$ro=$this->resolveRiskDie($riskR);if($ro['tt_delta']!==0){$p->tt=max(0,$p->tt+$ro['tt_delta']);$p->save();$ttF+=$ro['tt_delta'];}$dys=$ro['dysfunction'];}
      GameTurn::create(['game_room_id'=>$room->id,'game_player_id'=>$p->id,'expedition_card_id'=>$card->id,'chosen_option'=>$chosen,'risk_die_result'=>$riskR,'mp_effect'=>$mpF,'sp_effect'=>$spF,'tt_effect'=>$ttF,'extra_effect_applied'=>$extra,'dysfunction_triggered'=>$dys]);
      $triggeredFinal=false;
      if($this->checkFinalWin($p)&&$room->status===GameStatus::InProgress){$room->status=GameStatus::FinalRound;$room->save();$triggeredFinal=true;}
      $this->advanceTurn($room);
      return ['card'=>$card,'effects'=>['mp'=>$mpF,'sp'=>$spF,'tt'=>$ttF],'risk_die'=>$riskR,'dysfunction'=>$dys,'extra'=>$extra,'triggered_final_round'=>$triggeredFinal,'player'=>$p->fresh()];
    });
  }

  public function attemptRopeBridge(GamePlayer $p):array{
    return DB::transaction(function()use($p){
      $res=$this->checkRopeBridge($p);$lt=$p->turns()->latest()->first();
      if($lt){$lt->rope_bridge_attempted=true;$lt->rope_bridge_success=($res==='success');$lt->save();}
      $tf=false;if($this->checkFinalWin($p)){$room=$p->room;if($room->status===GameStatus::InProgress){$room->status=GameStatus::FinalRound;$room->save();$tf=true;}}
      return ['result'=>$res,'player'=>$p->fresh(),'triggered_final_round'=>$tf];
    });
  }

  public function advanceTurn(GameRoom $room):void{
    if($room->status===GameStatus::Finished)return;
    $aps=$room->players()->where('is_active',true)->orderBy('turn_order')->get();
    if($aps->isEmpty())return;
    if(!$room->current_turn_player_id){$next=$aps->first();}
    else{$cur=$aps->firstWhere('id',$room->current_turn_player_id);$ci=$cur?$aps->search($cur):-1;$next=$aps[($ci+1)%$aps->count()];}
    $room->current_turn_player_id=$next->id;$room->current_turn_started_at=now();$room->save();
    if($room->status===GameStatus::FinalRound){if($room->turns()->where('game_player_id',$next->id)->exists())$this->finishGame($room);}
    $next->user->notify(new \App\Notifications\TurnNotification($room,$next));
  }

  public function processTimeout(GameRoom $room):void{
    if(!in_array($room->status->value,['in_progress','final_round']))return;
    $cp=$room->currentPlayer;if(!$cp)return;
    $th=config('summit.turn_timeout_hours',24);
    if($room->current_turn_started_at&&$room->current_turn_started_at->addHours($th)->isFuture())return;
    $tn=$cp->turns()->count()+1;$card=$this->drawCard($cp,$tn);
    $opt=$card->opsi_b_tt>=$card->opsi_a_tt?'B':'A';
    $this->processTurn($cp,$opt,$card);
  }

  public function finishGame(GameRoom $room):void{
    DB::transaction(function()use($room){
      $room->status=GameStatus::Finished;$room->current_turn_player_id=null;$room->current_turn_started_at=null;$room->save();
      $ps=$room->players()->where('is_active',true)->get()->map(fn($p)=>tap($p,function($p){$p->score=$p->calculateScore();}));
      $sorted=$ps->sortByDesc(fn($p)=>($p->current_level==='summit'&&$p->tt>=8?'1':'0').'.'.$p->score.'.'.$p->tt.'.'.str_pad(99-$p->turn_order,2,'0',STR_PAD_LEFT));
      $rank=1;
      foreach($sorted as $p){
        $b='none';if($p->current_level==='summit'&&$p->tt>=8)$b='the_carrier';elseif($p->current_level==='summit'&&$p->tt<8)$b='solo_peak';
        GameResult::create(['game_room_id'=>$room->id,'game_player_id'=>$p->id,'final_level'=>$p->current_level,'final_mp'=>$p->mp,'final_sp'=>$p->sp,'final_tt'=>$p->tt,'final_score'=>$p->score,'badge'=>$b,'rank'=>$rank]);
        $rank++;
      }
      foreach($room->players as $gp)$gp->user->notify(new \App\Notifications\GameFinishedNotification($room,$gp));
    });
  }

  public function startGame(GameRoom $room):void{
    DB::transaction(function()use($room){
      $ps=$room->players()->where('is_active',true)->inRandomOrder()->get();
      foreach($ps as $i=>$p){$p->turn_order=$i+1;$p->save();}
      $room->status=GameStatus::InProgress;$room->current_turn_player_id=$ps->first()->id;$room->current_turn_started_at=now();$room->save();
      $ps->first()->user->notify(new \App\Notifications\TurnNotification($room,$ps->first()));
    });
  }
}
