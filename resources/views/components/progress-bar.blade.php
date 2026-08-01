@props(['level'=>'basecamp','mp'=>0,'sp'=>0,'tt'=>0,'showThresholds'=>false,'playerName'=>'','compact'=>false,'reputation'=>0,'resources'=>0])
@php
$levels=['basecamp','camp','summit'];$ci=array_search($level,$levels);
$mpPct=$level==='summit'?min(100,($mp/15)*100):min(100,($mp/12)*100);
$spPct=$level==='summit'?min(100,($sp/15)*100):min(100,($sp/12)*100);
$nt=null;$nl=null;
if($level==='basecamp'){$nt=config('summit.thresholds.to_camp');$nl='Camp';}elseif($level==='camp'){$nt=config('summit.thresholds.to_summit');$nl='Summit';}
@endphp
<div class="{{ $compact?'p-2':'p-4' }} notch-md bg-[#1c1810] border border-[#4a3a1b]">
@if($playerName)<div class="font-field text-sm font-semibold text-[#e8dfc8] mb-2">{{ $playerName }}</div>@endif
<div class="flex items-center gap-1 mb-3">
@foreach($levels as $idx=>$lvl)<div class="flex items-center"><div class="w-7 h-7 tag-notch text-[11px] {{ $idx<$ci?'border-[#7fae6c] text-[#7fae6c]':($idx===$ci?'border-[#d6a94e] text-[#d6a94e]':'border-[#4a3a1b] text-[#8a6a30]') }}">{{ $idx===0?'B':($idx===1?'C':'S') }}</div>@if($idx<2)<div class="w-6 h-0.5 {{ $idx<$ci?'bg-[#7fae6c]':'bg-[#4a3a1b]' }}"></div>@endif</div>@endforeach
</div>
<div class="mb-2"><div class="flex justify-between text-[10px] font-instrument mb-0.5"><span class="text-[#a89c7d] uppercase tracking-wider">MP</span><span class="text-[#e8dfc8]">{{ $mp }}</span></div><div class="w-full h-1.5 bg-[#1c1810] border border-[#332b1c] overflow-hidden"><div class="h-full bg-[#d6a94e] transition-all duration-500" style="width:{{ $mpPct }}%"></div></div></div>
<div class="mb-2"><div class="flex justify-between text-[10px] font-instrument mb-0.5"><span class="text-[#a89c7d] uppercase tracking-wider">SP</span><span class="text-[#e8dfc8]">{{ $sp }}</span></div><div class="w-full h-1.5 bg-[#1c1810] border border-[#332b1c] overflow-hidden"><div class="h-full bg-[#d6a94e] transition-all duration-500" style="width:{{ $spPct }}%"></div></div></div>
<x-rope-meter :tt="$tt" :compact="$compact" />
@if($reputation != 0)
<div class="mt-1 font-instrument text-[10px]"><span class="{{ $reputation >= 0 ? 'text-[#7fae6c]' : 'text-[#e6603a]' }}">Reputasi: {{ $reputation >= 0 ? '+' : '' }}{{ $reputation }}</span></div>
@endif
@if($showThresholds && $nt)<div class="mt-2 text-[10px] font-instrument text-[#a89c7d] border-t border-[#332b1c] pt-2">Naik ke {{ $nl }}: MP {{$nt['mp']}} | SP {{$nt['sp']}} @if($nt['tt']>0)| TT {{$nt['tt']}} @endif</div>@endif
</div>
