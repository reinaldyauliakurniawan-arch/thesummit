@props(['level'=>'basecamp','mp'=>0,'sp'=>0,'tt'=>0,'showThresholds'=>false,'playerName'=>'','compact'=>false])
@php
$levels=['basecamp','camp','summit'];$ci=array_search($level,$levels);
$mpPct=$level==='summit'?min(100,($mp/15)*100):min(100,($mp/12)*100);
$spPct=$level==='summit'?min(100,($sp/15)*100):min(100,($sp/12)*100);
$nt=null;$nl=null;
if($level==='basecamp'){$nt=config('summit.thresholds.to_camp');$nl='Camp';}elseif($level==='camp'){$nt=config('summit.thresholds.to_summit');$nl='Summit';}
@endphp
<div class="{{ $compact?'p-2':'p-4' }} rounded-lg bg-mountain-900/50 border border-mountain-700">
@if($playerName)<div class="text-sm font-semibold text-mountain-200 mb-2">{{ $playerName }}</div>@endif
<div class="flex items-center gap-1 mb-3">
@foreach($levels as $idx=>$lvl)<div class="flex items-center"><div class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold {{ $idx<$ci?'bg-camp-500 text-white':($idx===$ci?'bg-basecamp-500 text-white ring-2 ring-trust-400':'bg-mountain-700 text-mountain-400') }}">{{ $idx===0?'B':($idx===1?'C':'S') }}</div>@if($idx<2)<div class="w-6 h-0.5 {{ $idx<$ci?'bg-camp-400':'bg-mountain-700' }}"></div>@endif</div>@endforeach
</div>
<div class="mb-2"><div class="flex justify-between text-xs mb-0.5"><span class="text-mountain-300">MP</span><span class="text-mountain-200 font-mono">{{ $mp }}</span></div><div class="w-full h-2 bg-mountain-800 rounded-full overflow-hidden"><div class="h-full bg-basecamp-400 rounded-full transition-all duration-500" style="width:{{ $mpPct }}%"></div></div></div>
<div class="mb-2"><div class="flex justify-between text-xs mb-0.5"><span class="text-mountain-300">SP</span><span class="text-mountain-200 font-mono">{{ $sp }}</span></div><div class="w-full h-2 bg-mountain-800 rounded-full overflow-hidden"><div class="h-full bg-camp-400 rounded-full transition-all duration-500" style="width:{{ $spPct }}%"></div></div></div>
<x-rope-meter :tt="$tt" :compact="$compact" />
@if($showThresholds && $nt)<div class="mt-2 text-xs text-mountain-400 border-t border-mountain-700 pt-2">Naik ke {{ $nl }}: MP {{$nt['mp']}} | SP {{$nt['sp']}} @if($nt['tt']>0)| TT {{$nt['tt']}} @endif</div>@endif
</div>
