<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;use Illuminate\Database\Eloquent\Model;
class GamePlayer extends Model{use HasFactory;
protected $fillable=['game_room_id','user_id','current_level','mp','sp','tt','turn_order','is_active'];
protected function casts():array{return ['mp'=>'integer','sp'=>'integer','tt'=>'integer','turn_order'=>'integer','is_active'=>'boolean','joined_at'=>'datetime'];}
public function room(){return $this->belongsTo(GameRoom::class);}
public function user(){return $this->belongsTo(User::class);}
public function turns(){return $this->hasMany(GameTurn::class)->orderBy('created_at');}
public function result(){return $this->hasOne(GameResult::class);}
public function getPlayedCardIds():array{return $this->turns()->pluck('expedition_card_id')->toArray();}
public function getLastTwoCardIds():array{return $this->turns()->latest()->limit(2)->pluck('expedition_card_id')->toArray();}
public function meetsThreshold(string $key):bool{$t=config("summit.thresholds.$key");return $t&&$this->mp>=$t['mp']&&$this->sp>=$t['sp']&&$this->tt>=$t['tt'];}
public function calculateScore():int{$v=config("summit.scoring.level_values.$this->current_level",1);return($v*10)+$this->tt;}
}
