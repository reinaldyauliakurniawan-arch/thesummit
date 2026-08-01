@props(['badge'=>'none','rank'=>null,'size'=>'md'])
@php
$sizes=['sm'=>'text-xs px-2 py-0.5','md'=>'text-sm px-3 py-1','lg'=>'text-base px-4 py-1.5'];
$bg=[
    'the_carrier'=>'style="background:#d6a94e;color:#15130f;box-shadow:0 0 0 2px #e3c483;"',
    'the_catalyst'=>'style="background:#6b9c5a;color:#15130f;box-shadow:0 0 0 2px #a8c79a;"',
    'the_strategist'=>'style="background:#8a97ab;color:#15130f;box-shadow:0 0 0 2px #c3ccd8;"',
    'solo_peak'=>'style="background:#8a97ab;color:#15130f;box-shadow:0 0 0 2px #c3ccd8;"',
    'none'=>'style="background:#2c2519;color:#cdc2a0;"',
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
@php
$badgeIcons=[
    'the_carrier'=>'<path d="M12 3l2.5 5.5L20 9l-4 4 1 6-5-3-5 3 1-6-4-4 5.5-.5z"/>',
    'the_catalyst'=>'<path d="M12 2l2 7h7l-5.5 4.5L17 21l-5-4-5 4 1.5-7.5L3 9h7z"/>',
    'the_strategist'=>'<path d="M12 2l2.5 6.5L21 10l-5 4 1.5 7-5.5-3.5L6.5 21 8 14l-5-4 6.5-1.5z"/>',
    'solo_peak'=>'<path d="M4 20L12 4l8 16H4zm8-11l-4 8h8l-4-8z"/>',
    'none'=>'<path d="M12 2a5 5 0 015 5c0 3-5 11-5 11S7 10 7 7a5 5 0 015-5z"/>',
];
$badgeSizePx=['sm'=>'w-8 h-8','md'=>'w-11 h-11','lg'=>'w-16 h-16'];
@endphp
<div class="inline-flex flex-col items-center gap-1" title="{{ $descriptions[$badge]??'' }}">
    <div class="{{ $badgeSizePx[$size]??$badgeSizePx['md'] }} rounded-full flex items-center justify-center" {!! $bg[$badge]??$bg['none'] !!}>
        <svg class="w-1/2 h-1/2" fill="currentColor" viewBox="0 0 24 24">{!! $badgeIcons[$badge]??$badgeIcons['none'] !!}</svg>
    </div>
    <div class="flex items-center gap-1 font-instrument">
        @if($rank!==null)<span class="opacity-70 {{ $sizes[$size]??$sizes['md'] }}">#{{ $rank }}</span>@endif
        <span class="{{ $sizes[$size]??$sizes['md'] }} font-bold text-[#e8dfc8]">{{ $labels[$badge]??'Climber' }}</span>
    </div>
</div>
