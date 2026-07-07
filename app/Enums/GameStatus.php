<?php
namespace App\Enums;
enum GameStatus:string{case Waiting='waiting';case InProgress='in_progress';case FinalRound='final_round';case Finished='finished';
public function label():string{return match($this){self::Waiting=>'Menunggu Pemain',self::InProgress=>'Sedang Bermain',self::FinalRound=>'Ronde Terakhir',self::Finished=>'Selesai'};}
}
