@props(['kategori'=>'mindset','tipe'=>'netral'])
@php
$isKrisis = $tipe === 'krisis';
$color = $isKrisis ? '#e6603a' : ($kategori === 'mindset' ? '#d6a94e' : '#7fae6c');
@endphp
<div class="w-full flex items-center justify-center mb-4" style="color: {{ $color }}; filter: drop-shadow(0 2px 2px rgba(0,0,0,.4));">
@if($kategori === 'mindset' && !$isKrisis)
<svg class="h-14" viewBox="0 0 200 120" fill="none" stroke="currentColor" stroke-width="7" stroke-linecap="round" stroke-linejoin="round">
<circle cx="150" cy="28" r="13" stroke-width="6" opacity="0.7"/>
<path d="M20 100L75 30L105 60L145 15L185 100Z" stroke-width="8"/>
<path d="M75 30L88 46L62 46Z" fill="currentColor" opacity="0.4" stroke="none"/>
</svg>
@elseif($kategori === 'mindset' && $isKrisis)
<svg class="h-14" viewBox="0 0 200 120" fill="none" stroke="currentColor" stroke-width="7" stroke-linecap="round" stroke-linejoin="round">
<path d="M20 100L75 30L105 60L145 15L185 100Z" stroke-width="8"/>
<path d="M118 33L104 60L120 60L98 96" stroke-width="7"/>
<path d="M35 22Q52 8 70 22" opacity="0.5"/>
<path d="M128 12Q145 -2 163 12" opacity="0.5"/>
</svg>
@elseif($kategori === 'skillset' && !$isKrisis)
<svg class="h-14" viewBox="0 0 200 120" fill="none" stroke="currentColor" stroke-width="7" stroke-linecap="round" stroke-linejoin="round">
<path d="M20 100L75 30L105 60L145 15L185 100Z" stroke-width="6" opacity="0.45"/>
<circle cx="78" cy="72" r="10"/>
<path d="M78 82L78 100M68 90L88 90"/>
<circle cx="112" cy="72" r="10"/>
<path d="M112 82L112 100M102 90L122 90"/>
<path d="M88 78Q95 70 102 78"/>
</svg>
@else
<svg class="h-14" viewBox="0 0 200 120" fill="none" stroke="currentColor" stroke-width="7" stroke-linecap="round" stroke-linejoin="round">
<path d="M12 82Q100 60 188 82" stroke-width="8"/>
<path d="M32 82L32 100M65 82L65 104M100 82L100 108M135 82L135 104M168 82L168 100" stroke-width="5" opacity="0.7"/>
<path d="M12 45L12 82M188 45L188 82" opacity="0.6"/>
<path d="M-6 45L32 82M32 45L-6 82" opacity="0.4"/>
<path d="M168 45L206 82M206 45L168 82" opacity="0.4"/>
</svg>
@endif
</div>