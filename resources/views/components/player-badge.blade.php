@props(['badge'=>'none','rank'=>null,'size'=>'md'])
@php
$sizes=['sm'=>'text-xs px-2 py-0.5','md'=>'text-sm px-3 py-1','lg'=>'text-base px-4 py-1.5'];
$bg=[
    'the_carrier'=>'bg-trust-500 text-white ring-2 ring-trust-300 animate-pulse-gold',
    'the_catalyst'=>'bg-camp-500 text-white ring-2 ring-camp-300',
    'the_strategist'=>'bg-summit-500 text-summit-950 ring-2 ring-summit-300',
    'solo_peak'=>'bg-summit-500 text-summit-950 ring-2 ring-summit-300',
    'none'=>'bg-mountain-700 text-mountain-200',
];
$labels=[
    'the_carrier'=>'The Carrier',
    'the_catalyst'=>'The Catalyst',
    'the_strategist'=>'The Strategist',
    'solo_peak'=>'Solo Peak',
    'none'=>'Climber',
];
$descriptions=[
    'the_carrier'=>'Mencapai Summit dengan kepercayaan tim. Leader sejati.',
    'the_catalyst'=>'Tidak summit, tapi jadi tulang punggung tim.',
    'the_strategist'=>'Leader paling versatile — terbukti multi-dimensional.',
    'solo_peak'=>'Mencapai Summit sendiri. Mampu, tapi dengan apa cost?',
    'none'=>'Masih mendaki. Setiap summit dimulai dari langkah pertama.',
];
@endphp
<div class="inline-flex items-center gap-1.5 rounded-full {{ $bg[$badge]??$bg['none'] }} {{ $sizes[$size]??$sizes['md'] }} font-bold" title="{{ $descriptions[$badge]??'' }}">
@if($rank!==null)<span class="font-mono opacity-70">#{{ $rank }}</span>@endif
<span>{{ $labels[$badge]??'Climber' }}</span></div>
