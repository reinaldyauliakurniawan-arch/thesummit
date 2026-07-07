@props(['badge'=>'none','rank'=>null,'size'=>'md'])
@php
$sizes=['sm'=>'text-xs px-2 py-0.5','md'=>'text-sm px-3 py-1','lg'=>'text-base px-4 py-1.5'];
$bg=['the_carrier'=>'bg-trust-500 text-white ring-2 ring-trust-300 animate-pulse-gold','solo_peak'=>'bg-summit-500 text-summit-950 ring-2 ring-summit-300','none'=>'bg-mountain-700 text-mountain-200'];
$labels=['the_carrier'=>'The Carrier','solo_peak'=>'Solo Peak','none'=>'Climber'];
@endphp
<div class="inline-flex items-center gap-1.5 rounded-full {{ $bg[$badge]??$bg['none'] }} {{ $sizes[$size]??$sizes['md'] }} font-bold">
@if($rank!==null)<span class="font-mono opacity-70">#{{ $rank }}</span>@endif
<span>{{ $labels[$badge]??'Climber' }}</span></div>
