@props(['kategori'=>'mindset','tipe'=>'netral'])
@php
$isKrisis = $tipe === 'krisis';
$tint = $isKrisis ? 'text-crisis-400' : ($kategori === 'mindset' ? 'text-trust-400' : 'text-camp-400');
@endphp
<div class="w-full h-20 flex items-center justify-center mb-4 {{ $tint }}">
@if($kategori === 'mindset' && !$isKrisis)
<svg class="h-full" viewBox="0 0 200 80" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
<circle cx="150" cy="20" r="10" stroke-width="1.5" opacity="0.6"/>
<path d="M20 65L70 20L100 45L140 10L180 65Z" opacity="0.9"/>
<path d="M70 20L80 32L60 32Z" fill="currentColor" opacity="0.3" stroke="none"/>
</svg>
@elseif($kategori === 'mindset' && $isKrisis)
<svg class="h-full" viewBox="0 0 200 80" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
<path d="M20 65L70 20L100 45L140 10L180 65Z" opacity="0.9"/>
<path d="M110 25L100 45L112 45L95 68" stroke-width="2.5"/>
<path d="M30 15Q45 5 60 15" opacity="0.5"/>
<path d="M120 8Q135 -2 150 8" opacity="0.5"/>
</svg>
@elseif($kategori === 'skillset' && !$isKrisis)
<svg class="h-full" viewBox="0 0 200 80" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
<path d="M20 65L70 20L100 45L140 10L180 65Z" opacity="0.5"/>
<circle cx="80" cy="50" r="6"/>
<path d="M80 56L80 68M74 60L86 60" />
<circle cx="105" cy="50" r="6"/>
<path d="M105 56L105 68M99 60L111 60"/>
<path d="M86 55Q92.5 50 99 55"/>
</svg>
@else
<svg class="h-full" viewBox="0 0 200 80" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
<path d="M15 55Q100 40 185 55" stroke-width="2.5"/>
<path d="M30 55L30 68M60 55L60 70M100 55L100 72M140 55L140 70M170 55L170 68" stroke-width="1.5" opacity="0.7"/>
<path d="M15 30L15 55M185 30L185 55" opacity="0.6"/>
<path d="M0 30L30 55M30 30L0 55" opacity="0.4"/>
<path d="M170 30L200 55M200 30L170 55" opacity="0.4"/>
</svg>
@endif
</div>