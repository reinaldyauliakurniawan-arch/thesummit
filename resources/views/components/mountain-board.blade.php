@props(['players', 'currentPlayerId' => null])
@php
    $levels = ['basecamp' => 0, 'camp' => 1, 'summit' => 2];
    $avatarColors = ['#d6a94e','#7fae6c','#e6603a','#8a97ab','#c98fb0','#6fa8c9'];

    // Camp anchor points along the winding trail (viewBox 0 0 720 260).
    $camps = [
        ['x' => 70,  'y' => 210, 'label' => 'Basecamp'],
        ['x' => 380, 'y' => 120, 'label' => 'Camp'],
        ['x' => 650, 'y' => 40,  'label' => 'Summit'],
    ];

    // Compute each player's fractional progress within their current
    // level, based on MP+SP against the threshold for the next level,
    // so tokens visibly creep along the trail between camps.
    $tokens = [];
    foreach ($players as $p) {
        $levelIdx = $levels[$p->current_level] ?? 0;
        $frac = 0.0;

        if ($levelIdx < 2) {
            $nextKey = $levelIdx === 0 ? 'to_camp' : 'to_summit';
            $threshold = config("summit.thresholds.$nextKey");
            if ($threshold) {
                $mpFrac = $threshold['mp'] > 0 ? min(1, $p->mp / $threshold['mp']) : 1;
                $spFrac = $threshold['sp'] > 0 ? min(1, $p->sp / $threshold['sp']) : 1;
                $frac = ($mpFrac + $spFrac) / 2;
            }
        }

        $from = $camps[$levelIdx];
        $to = $camps[min($levelIdx + 1, 2)];
        $tokens[] = [
            'player' => $p,
            'x' => $from['x'] + ($to['x'] - $from['x']) * $frac,
            'y' => $from['y'] + ($to['y'] - $from['y']) * $frac,
            'color' => $avatarColors[$p->turn_order % count($avatarColors)],
        ];
    }
@endphp
<div class="notch-md bg-[#1c1810] border border-[#4a3a1b] p-3 md:p-5">
    <svg viewBox="0 0 720 260" class="w-full h-auto" xmlns="http://www.w3.org/2000/svg">
        <defs>
            <linearGradient id="skyFade" x1="0" y1="0" x2="0" y2="1">
                <stop offset="0%" stop-color="#241f17"/>
                <stop offset="100%" stop-color="#1c1810"/>
            </linearGradient>
        </defs>
        <rect x="0" y="0" width="720" height="260" fill="url(#skyFade)"/>

        <!-- Mountain silhouette -->
        <polygon points="0,260 40,230 130,250 220,150 300,190 380,120 470,170 560,90 650,40 720,110 720,260"
                 fill="#241f17" stroke="#4a3a1b" stroke-width="2"/>

        <!-- Trail line connecting the three camps -->
        <polyline points="{{ $camps[0]['x'] }},{{ $camps[0]['y'] }} {{ $camps[1]['x'] }},{{ $camps[1]['y'] }} {{ $camps[2]['x'] }},{{ $camps[2]['y'] }}"
                  fill="none" stroke="#8a6a30" stroke-width="3" stroke-dasharray="2 8" stroke-linecap="round"/>

        <!-- Camp markers -->
        @foreach($camps as $i => $camp)
            <circle cx="{{ $camp['x'] }}" cy="{{ $camp['y'] }}" r="14" fill="#15130f" stroke="#d6a94e" stroke-width="2.5"/>
            <text x="{{ $camp['x'] }}" y="{{ $camp['y'] + 5 }}" text-anchor="middle" font-family="JetBrains Mono, monospace" font-size="11" font-weight="700" fill="#d6a94e">{{ ['B','C','S'][$i] }}</text>
            <text x="{{ $camp['x'] }}" y="{{ $camp['y'] - 22 }}" text-anchor="middle" font-family="JetBrains Mono, monospace" font-size="10" letter-spacing="1" fill="#8a6a30" text-transform="uppercase">{{ strtoupper($camp['label']) }}</text>
        @endforeach

        <!-- Player tokens -->
        @foreach($tokens as $t)
            <g transform="translate({{ $t['x'] }},{{ $t['y'] - 28 }})">
                <circle r="11" fill="{{ $t['color'] }}" stroke="{{ $currentPlayerId === $t['player']->id ? '#e8dfc8' : '#15130f' }}" stroke-width="{{ $currentPlayerId === $t['player']->id ? 2.5 : 1.5 }}">
                    @if($currentPlayerId === $t['player']->id)
                        <animate attributeName="r" values="11;13;11" dur="1.6s" repeatCount="indefinite"/>
                    @endif
                </circle>
                <text text-anchor="middle" dy="4" font-family="JetBrains Mono, monospace" font-size="10" font-weight="700" fill="#15130f">{{ strtoupper(substr($t['player']->display_name, 0, 1)) }}</text>
            </g>
        @endforeach
    </svg>

    <!-- Legend -->
    <div class="flex flex-wrap gap-x-4 gap-y-1 mt-3 pt-3 border-t border-[#332b1c]">
        @foreach($players as $p)
        @php $c = $avatarColors[$p->turn_order % count($avatarColors)]; @endphp
        <div class="flex items-center gap-1.5 text-[10px] font-instrument">
            <span class="w-2.5 h-2.5 rounded-full inline-block" style="background:{{ $c }};"></span>
            <span class="{{ $currentPlayerId === $p->id ? 'text-[#d6a94e] font-bold' : 'text-[#a89c7d]' }}">{{ $p->display_name }}</span>
        </div>
        @endforeach
    </div>
</div>
