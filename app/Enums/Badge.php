<?php
namespace App\Enums;
enum Badge:string{case TheCarrier='the_carrier';case SoloPeak='solo_peak';case None='none';
public function label():string{return match($this){self::TheCarrier=>'The Carrier',self::SoloPeak=>'Solo Peak',self::None=>'Climber'};}
}
