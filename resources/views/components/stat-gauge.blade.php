@props(['value' => 0, 'max' => 4, 'label' => '', 'size' => 54])
@php
    $radius = ($size/2) - 5;
    $circumference = 2 * M_PI * $radius;
    $pct = $max > 0 ? max(0, min(1, abs($value) / $max)) : 0;
    $offset = $circumference * (1 - $pct);
    $positive = $value >= 0;
    $stroke = $positive ? '#6b9c5a' : '#c1502e';
    $center = $size / 2;
@endphp
<div class="text-center">
    <svg width="{{ $size }}" height="{{ $size }}" viewBox="0 0 {{ $size }} {{ $size }}">
        <circle cx="{{ $center }}" cy="{{ $center }}" r="{{ $radius }}" fill="none" stroke="#3a3221" stroke-width="4"/>
        <circle cx="{{ $center }}" cy="{{ $center }}" r="{{ $radius }}" fill="none" stroke="{{ $stroke }}" stroke-width="4" stroke-linecap="round"
                stroke-dasharray="{{ $circumference }}" stroke-dashoffset="{{ $offset }}"
                transform="rotate(-90 {{ $center }} {{ $center }})" style="transition:stroke-dashoffset .6s ease;"/>
    </svg>
    <div class="font-instrument font-bold text-[19px] mt-1 {{ $positive ? 'text-[#7fae6c]' : 'text-[#e6603a]' }}">{{ $positive ? '+' : '' }}{{ $value }}</div>
    <div class="font-instrument text-[9px] tracking-widest text-[#a89c7d] uppercase mt-0.5">{{ $label }}</div>
</div>
