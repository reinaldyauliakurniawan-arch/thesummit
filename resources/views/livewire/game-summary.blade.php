<div>
<div class="max-w-2xl mx-auto px-4 pt-6 pb-12">

<!-- Header -->
<div class="text-center mb-8">
    <h1 class="text-3xl font-bold font-expedition text-mountain-100">Ekspedisi Selesai!</h1>
    <p class="text-mountain-400 mt-1">Room {{ $room->code }}</p>
</div>

<!-- Winner display -->
@if($results->first() && $results->first()->badge !== 'none')
<div class="text-center mb-8 animate-slide-up">
    <div class="animate-badge-glow inline-block">
    <x-player-badge :badge="$results->first()->badge" :rank="1" size="lg" />
    </div>
    <div class="mt-3">
        <h2 class="text-2xl font-bold text-mountain-100">{{ $results->first()->player->user->name }}</h2>
        <p class="text-mountain-400 text-sm">Skor: {{ $results->first()->final_score }}</p>
    </div>
    @if($results->first()->badge === 'the_carrier')
        <p class="text-trust-400 text-sm mt-2 italic">"The real winner is the one who makes everybody win."</p>
    @elseif($results->first()->badge === 'the_catalyst')
        <p class="text-camp-400 text-sm mt-2 italic">"You didn't reach the top, but you lifted everyone closer."</p>
    @elseif($results->first()->badge === 'the_strategist')
        <p class="text-summit-400 text-sm mt-2 italic">"True leadership is not about one strength — it's about many."</p>
    @elseif($results->first()->badge === 'solo_peak')
        <p class="text-summit-300 text-sm mt-2 italic">"You reached the peak. But who climbed with you?"</p>
    @endif
</div>
@endif

<!-- Rankings with v2 stats -->
<div class="space-y-3 mb-8">
    @foreach($results as $r)
    <div class="flex items-center gap-4 p-4 rounded-xl bg-mountain-900/50 border {{ $r->rank===1?'border-trust-500/50':'border-mountain-800' }}">
        <div class="text-lg font-bold font-mono w-8 text-center {{ $r->rank===1?'text-trust-400':($r->rank===2?'text-mountain-300':'text-mountain-500') }}">#{{ $r->rank }}</div>
        <div class="flex-1">
            <div class="flex items-center gap-2">
                <span class="font-semibold text-mountain-200">{{ $r->player->user->name }}</span>
                <x-player-badge :badge="$r->badge" size="sm" />
            </div>
            <div class="flex gap-3 mt-1 text-xs flex-wrap">
                <span class="text-basecamp-300">MP {{ $r->final_mp }}</span>
                <span class="text-camp-300">SP {{ $r->final_sp }}</span>
                <span class="text-trust-300">TT {{ $r->final_tt }}</span>
                @if($r->final_reputation != 0)
                    <span class="{{ $r->final_reputation >= 0 ? 'text-summit-300' : 'text-crisis-400' }}">Rep {{ $r->final_reputation }}</span>
                @endif
            </div>
            <div class="text-xs text-mountain-500 mt-0.5">{{ ucfirst($r->final_level) }} — Skor: {{ $r->final_score }}</div>
        </div>
        <x-rope-meter :tt="$r->final_tt" :compact="true" />
    </div>
    @endforeach
</div>

{{-- ═══════════════════════════════════════════════════════════════ --}}
{{-- V2: Leadership Reflection Report (for current user)            --}}
{{-- ═══════════════════════════════════════════════════════════════ --}}
@if($myResult && $myResult->leadershipProfile)
    @php $profile = $myResult->leadershipProfile; @endphp

    <div class="border-t border-mountain-700 pt-8 mb-8">
        <h2 class="text-xl font-bold text-trust-400 mb-1 font-expedition">Profil Kepemimpinanmu</h2>
        <p class="text-xs text-mountain-400 mb-6">Refleksi berdasarkan keputusanmu selama ekspedisi</p>

        <!-- Leadership Style -->
        <div class="p-4 rounded-xl bg-trust-900/20 border border-trust-700/30 mb-4">
            <h3 class="text-xs uppercase tracking-wider text-trust-300 mb-2 font-semibold">Gaya Kepemimpinan</h3>
            <p class="text-sm text-mountain-100 leading-relaxed">{{ $profile->leadership_style }}</p>
        </div>

        <!-- Strengths & Blind Spots -->
        <div class="grid grid-cols-2 gap-3 mb-4">
            <div class="p-4 rounded-xl bg-camp-900/20 border border-camp-700/30">
                <h3 class="text-xs uppercase tracking-wider text-camp-300 mb-2 font-semibold">Top 3 Kekuatan</h3>
                <div class="space-y-1.5">
                    @foreach($profile->strengths as $i => $strength)
                    <div class="text-sm text-mountain-200 flex items-start gap-2">
                        <span class="text-camp-400 font-bold flex-shrink-0">{{ $i+1 }}.</span>
                        <span>{{ $strength }}</span>
                    </div>
                    @endforeach
                    @if(empty($profile->strengths))
                        <p class="text-xs text-mountain-400">Belum terdeteksi</p>
                    @endif
                </div>
            </div>
            <div class="p-4 rounded-xl bg-crisis-900/20 border border-crisis-700/30">
                <h3 class="text-xs uppercase tracking-wider text-crisis-300 mb-2 font-semibold">Top 3 Blind Spots</h3>
                <div class="space-y-1.5">
                    @foreach($profile->blind_spots as $i => $spot)
                    <div class="text-sm text-mountain-200 flex items-start gap-2">
                        <span class="text-crisis-400 font-bold flex-shrink-0">{{ $i+1 }}.</span>
                        <span>{{ $spot }}</span>
                    </div>
                    @endforeach
                    @if(empty($profile->blind_spots))
                        <p class="text-xs text-mountain-400">Belum terdeteksi</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Key Turning Point -->
        @if($profile->key_turning_point)
        <div class="p-4 rounded-xl bg-basecamp-900/20 border border-basecamp-700/30 mb-4">
            <h3 class="text-xs uppercase tracking-wider text-basecamp-300 mb-2 font-semibold">Momen Kunci</h3>
            <p class="text-sm text-mountain-200 leading-relaxed">{{ $profile->key_turning_point }}</p>
        </div>
        @endif

        <!-- Missed Opportunities -->
        @if($profile->missed_opportunities)
        <div class="p-4 rounded-xl bg-summit-900/20 border border-summit-700/30 mb-4">
            <h3 class="text-xs uppercase tracking-wider text-summit-300 mb-2 font-semibold">Peluang yang Terlewat</h3>
            <p class="text-sm text-mountain-200 leading-relaxed">{{ $profile->missed_opportunities }}</p>
        </div>
        @endif

        <!-- Coaching Recommendations -->
        @if($profile->coaching_recommendations)
        <div class="p-4 rounded-xl bg-mountain-900/50 border border-mountain-700 mb-4">
            <h3 class="text-xs uppercase tracking-wider text-trust-300 mb-2 font-semibold">Rekomendasi Coaching</h3>
            <p class="text-sm text-mountain-100 leading-relaxed">{{ $profile->coaching_recommendations }}</p>
        </div>
        @endif

        <!-- Behavior Scores Visualization -->
        @if($profile->behavior_scores && count($profile->behavior_scores) > 0)
        <div class="p-4 rounded-xl bg-mountain-900/50 border border-mountain-700 mb-4">
            <h3 class="text-xs uppercase tracking-wider text-mountain-300 mb-3 font-semibold">Skor Perilaku</h3>
            <div class="space-y-2">
                @foreach($profile->behavior_scores as $type => $score)
                @php
                    $maxAbs = max(1, abs($score));
                    $pct = min(100, (abs($score) / 5) * 100);
                    $barColor = $score >= 0 ? 'bg-camp-400' : 'bg-crisis-500';
                @endphp
                <div class="flex items-center gap-3 text-xs">
                    <div class="w-24 text-mountain-300">{{ \App\Models\PlayerBehavior::behaviorTypes()[$type] ?? $type }}</div>
                    <div class="flex-1 h-2 bg-mountain-800 rounded-full overflow-hidden">
                        <div class="h-full {{ $barColor }} rounded-full transition-all duration-700" style="width:{{ $pct }}%; margin-left:{{ $score < 0 ? (100 - $pct) : 0 }}%"></div>
                    </div>
                    <div class="w-8 text-right font-mono {{ $score >= 0 ? 'text-camp-300' : 'text-crisis-400' }}">{{ $score >= 0 ? '+' : '' }}{{ $score }}</div>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>

    {{-- ═══════════════════════════════════════════════════════════════ --}}
    {{-- V2: Real-World Challenge                                        --}}
    {{-- ═══════════════════════════════════════════════════════════════ --}}
    @if($myResult && $myResult->realWorldChallenge)
        @php $challenge = $myResult->realWorldChallenge; @endphp
        <div class="border-t border-trust-700 pt-8 mb-8">
            <div class="p-6 rounded-2xl bg-trust-900/20 border border-trust-500/30">
                <div class="flex items-center gap-2 mb-4">
                    <span class="text-2xl">🎯</span>
                    <h2 class="text-lg font-bold text-trust-400 font-expedition">Tantangan Dunia Nyata</h2>
                </div>
                <p class="text-sm text-mountain-100 leading-relaxed mb-4">{{ $challenge->challenge }}</p>

                <div class="p-3 rounded-lg bg-mountain-900/50 mb-4">
                    <h4 class="text-xs uppercase tracking-wider text-mountain-400 mb-1">Mengapa tantangan ini?</h4>
                    <p class="text-xs text-mountain-300 leading-relaxed">{{ $challenge->why_this_challenge }}</p>
                </div>

                <div class="flex items-center justify-between text-xs">
                    <span class="text-mountain-400">
                        Deadline: {{ $challenge->deadline ? $challenge->deadline->format('d M Y') : '1 minggu' }}
                    </span>
                    @if(!$challenge->is_completed)
                        <button wire:click="markChallengeCompleted({{ $myResult->id }})"
                                class="px-4 py-1.5 rounded-lg bg-camp-600 text-white font-semibold hover:bg-camp-500 transition-colors">
                            Tandai Selesai
                        </button>
                    @else
                        <span class="px-3 py-1 rounded-lg bg-camp-800 text-camp-300 font-semibold">✓ Selesai</span>
                    @endif
                </div>
            </div>
        </div>
    @endif
@endif

{{-- Turn History --}}
<div class="mt-4">
    <h3 class="text-sm font-semibold text-mountain-300 mb-3">Riwayat Ekspedisi</h3>
    <div class="space-y-2 max-h-96 overflow-y-auto">
        @foreach($turns as $t)
        <div class="p-3 rounded-lg bg-mountain-900/50 border border-mountain-800 text-xs">
            <div class="flex items-center gap-2 mb-1">
                <span class="font-semibold text-mountain-200">{{ $t->player->user->name }}</span>
                pilih <span class="font-bold text-trust-300">{{ $t->chosen_option }}</span>
                @if($t->card)
                    <span class="px-1.5 py-0.5 rounded text-mountain-400 bg-mountain-800">{{ ucfirst($t->card->kategori) }}</span>
                @endif
                @if($t->was_hidden)
                    <span class="px-1.5 py-0.5 rounded text-summit-300 bg-summit-800">Tersembunyi</span>
                @endif
            </div>
            @if($t->card)
                <p class="text-mountain-500 mb-1 line-clamp-1">{{ Str::limit($t->card->teks_situasi, 80) }}</p>
            @endif
            <div class="flex gap-2 text-mountain-400 flex-wrap">
                <span>MP{{ $t->mp_effect>=0?'+':'' }}{{ $t->mp_effect }}</span>
                <span>SP{{ $t->sp_effect>=0?'+':'' }}{{ $t->sp_effect }}</span>
                <span>TT{{ $t->tt_effect>=0?'+':'' }}{{ $t->tt_effect }}</span>
                @if($t->reputation_effect)
                    <span class="{{ $t->reputation_effect >= 0 ? 'text-summit-300' : 'text-crisis-400' }}">R{{ $t->reputation_effect >= 0 ? '+' : '' }}{{ $t->reputation_effect }}</span>
                @endif
                @if($t->risk_die_result)<span class="text-mountain-500">| Die:{{ $t->risk_die_result }}</span>@endif
                @if($t->dysfunction_triggered)<span class="text-crisis-400">| {{ str_replace('_',' ',$t->dysfunction_triggered) }}</span>@endif
                @if($t->rope_bridge_success)<span class="text-camp-300">| Rope Bridge OK</span>@endif
                @if($t->cross_player_effects && count($t->cross_player_effects) > 0)
                    <span class="text-camp-300">| Tim Effect</span>
                @endif
            </div>
        </div>
        @endforeach
    </div>
</div>

<div class="text-center mt-8">
    <a href="{{ route('dashboard') }}" class="px-6 py-2.5 rounded-xl border border-mountain-600 text-mountain-300 text-sm">Kembali ke Basecamp</a>
</div>
</div></div>
