@props(['level' => 'basecamp'])
@php
$labels = ['basecamp' => 'BASECAMP', 'camp' => 'CAMP', 'summit' => 'SUMMIT'];
$label = $labels[$level] ?? strtoupper($level);
@endphp
<svg viewBox="0 0 100 100" class="w-full h-full" style="filter:drop-shadow(0 2px 3px rgba(0,0,0,.5));">
    <circle cx="50" cy="50" r="46" fill="#241f17" stroke="#d6a94e" stroke-width="2"/>
    <circle cx="50" cy="50" r="38" fill="none" stroke="#d6a94e" stroke-width="1" stroke-dasharray="2 3"/>
    <g stroke="#d6a94e" stroke-width="1.5" fill="none" stroke-linecap="round">
        <path d="M50 16 L50 24 M50 76 L50 84 M16 50 L24 50 M76 50 L84 50"/>
        <path d="M27 27 L32 32 M68 68 L73 73 M73 27 L68 32 M32 68 L27 73" opacity="0.6"/>
    </g>
    <path d="M50 24 L58 50 L50 76 L42 50 Z" fill="#d6a94e"/>
    <path d="M24 50 L50 42 L76 50 L50 58 Z" fill="#d6a94e" opacity="0.55"/>
    <text x="50" y="54" text-anchor="middle" font-family="JetBrains Mono, monospace" font-size="9" font-weight="700" fill="#15130f">{{ $label }}</text>
</svg>
