<div>
<div class="max-w-2xl mx-auto px-4 pt-6 pb-12 relative">
<div class="absolute right-[-50px] top-[-10px] w-[200px] h-[200px] opacity-[.05] pointer-events-none"><x-compass-watermark /></div>

<!-- Header -->
<div class="text-center mb-8 relative">
    <h1 class="text-3xl font-bold font-expedition text-[#e8dfc8] tracking-wide">Ekspedisi Selesai!</h1>
    <p class="text-[#a89c7d] mt-1 font-instrument text-sm">Room {{ $room->code }}</p>
</div>

<!-- Winner display -->
@if($results->first() && $results->first()->badge !== 'none')
<div class="text-center mb-8 animate-slide-up relative">
    <div class="animate-badge-glow inline-block">
    <x-player-badge :badge="$results->first()->badge" :rank="1" size="lg" />
    </div>
    <div class="mt-3">
        <h2 class="text-2xl font-bold font-expedition text-[#e8dfc8] tracking-wide">{{ $results->first()->player->display_name }}</h2>
        <p class="text-[#a89c7d] text-sm font-instrument">Skor: {{ $results->first()->final_score }}</p>
    </div>
    @if($results->first()->badge === 'the_carrier')
        <p class="text-[#d6a94e] text-sm mt-2 italic font-field">"The real winner is the one who makes everybody win."</p>
    @elseif($results->first()->badge === 'the_catalyst')
        <p class="text-[#7fae6c] text-sm mt-2 italic font-field">"You didn't reach the top, but you lifted everyone closer."</p>
    @elseif($results->first()->badge === 'the_strategist')
        <p class="text-[#8a97ab] text-sm mt-2 italic font-field">"True leadership is not about one strength — it's about many."</p>
    @elseif($results->first()->badge === 'solo_peak')
        <p class="text-[#a89c7d] text-sm mt-2 italic font-field">"You reached the peak. But who climbed with you?"</p>
    @endif
</div>
@endif

<!-- Rankings -->
<div class="space-y-3 mb-8 relative">
    @foreach($results as $r)
    <div class="flex items-center gap-4 p-4 notch-sm bg-[#1c1810] border {{ $r->rank===1?'border-[#d6a94e]':'border-[#332b1c]' }}">
        <div class="text-lg font-bold font-instrument w-8 text-center {{ $r->rank===1?'text-[#d6a94e]':($r->rank===2?'text-[#cdc2a0]':'text-[#8a6a30]') }}">#{{ $r->rank }}</div>
        <div class="flex-1">
            <div class="flex items-center gap-2">
                <span class="font-semibold text-[#e8dfc8] font-field">{{ $r->player->display_name }}</span>
                <x-player-badge :badge="$r->badge" size="sm" />
            </div>
            <div class="flex gap-3 mt-1 text-xs flex-wrap font-instrument">
                <span class="text-[#d6a94e]">MP {{ $r->final_mp }}</span>
                <span class="text-[#d6a94e]">SP {{ $r->final_sp }}</span>
                <span class="text-[#d6a94e]">TT {{ $r->final_tt }}</span>
                @if($r->final_reputation != 0)
                    <span class="{{ $r->final_reputation >= 0 ? 'text-[#7fae6c]' : 'text-[#e6603a]' }}">Rep {{ $r->final_reputation }}</span>
                @endif
            </div>
            <div class="text-xs text-[#8a6a30] mt-0.5 font-instrument uppercase tracking-wider">{{ ucfirst($r->final_level) }} — Skor: {{ $r->final_score }}</div>
        </div>
        <x-rope-meter :tt="$r->final_tt" :compact="true" />
    </div>
    @endforeach
</div>

<!-- Reflection Reports: every player's profile is shown in turn order, since
     everyone shares the same device (hotseat mode). -->
@foreach($results as $result)
@if($result->leadershipProfile)
    @php $profile = $result->leadershipProfile; @endphp

    <div class="card-frame mb-8">
    <div class="card-frame-inner p-6 md:p-8">
    <div class="grain-overlay" style="opacity:.2;"></div>
    <div class="absolute right-2 top-2 w-12 h-12 z-10"><x-expedition-stamp level="summit" /></div>
    <div class="relative z-10">

        <div class="text-center mb-6">
            <span class="font-instrument text-[10px] uppercase tracking-[.25em] text-[#8a6a30]">Laporan Resmi Ekspedisi</span>
            <h2 class="text-2xl font-bold text-[#e8dfc8] font-expedition tracking-wide mt-1">Profil Kepemimpinan {{ $result->player->display_name }}</h2>
        </div>

        <!-- Leadership Style — the verdict, emphasized -->
        <div class="text-center mb-8 pb-6 border-b border-[#4a3a1b]">
            <p class="text-lg text-[#e8dfc8] leading-relaxed font-field italic">"{{ $profile->leadership_style }}"</p>
        </div>

        <!-- Behavior Scores — evidence backing the verdict -->
        @if($profile->behavior_scores && count($profile->behavior_scores) > 0)
        <div class="mb-6">
            <h3 class="text-[10px] uppercase tracking-[.18em] text-[#8a6a30] mb-3 font-instrument text-center">Bukti Perilaku</h3>
            <div class="space-y-2">
                @foreach($profile->behavior_scores as $type => $score)
                @php
                    $pct = min(100, (abs($score) / 5) * 100);
                    $barColor = $score >= 0 ? '#7fae6c' : '#e6603a';
                @endphp
                <div class="flex items-center gap-3 text-xs font-instrument">
                    <div class="w-24 text-[#a89c7d]">{{ \App\Models\PlayerBehavior::behaviorTypes()[$type] ?? $type }}</div>
                    <div class="flex-1 h-2 bg-[#15130f] border border-[#332b1c] overflow-hidden">
                        <div class="h-full transition-all duration-700" style="width:{{ $pct }}%; margin-left:{{ $score < 0 ? (100 - $pct) : 0 }}%; background:{{ $barColor }};"></div>
                    </div>
                    <div class="w-8 text-right {{ $score >= 0 ? 'text-[#7fae6c]' : 'text-[#e6603a]' }}">{{ $score >= 0 ? '+' : '' }}{{ $score }}</div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Strengths & Blind Spots -->
        <div class="grid grid-cols-2 gap-3 mb-6">
            <div class="notch-sm p-4" style="background:rgba(107,156,90,.08);border:1px solid rgba(107,156,90,.3);">
                <h3 class="text-[10px] uppercase tracking-wider text-[#7fae6c] mb-2 font-semibold font-instrument">Top 3 Kekuatan</h3>
                <div class="space-y-1.5">
                    @foreach($profile->strengths as $i => $strength)
                    <div class="text-sm text-[#cdc2a0] flex items-start gap-2 font-field">
                        <span class="text-[#7fae6c] font-bold flex-shrink-0 font-instrument">{{ $i+1 }}.</span>
                        <span>{{ $strength }}</span>
                    </div>
                    @endforeach
                    @if(empty($profile->strengths))
                        <p class="text-xs text-[#8a6a30] font-instrument">Belum terdeteksi</p>
                    @endif
                </div>
            </div>
            <div class="notch-sm p-4" style="background:rgba(193,80,46,.08);border:1px solid rgba(193,80,46,.3);">
                <h3 class="text-[10px] uppercase tracking-wider text-[#e6603a] mb-2 font-semibold font-instrument">Top 3 Blind Spots</h3>
                <div class="space-y-1.5">
                    @foreach($profile->blind_spots as $i => $spot)
                    <div class="text-sm text-[#cdc2a0] flex items-start gap-2 font-field">
                        <span class="text-[#e6603a] font-bold flex-shrink-0 font-instrument">{{ $i+1 }}.</span>
                        <span>{{ $spot }}</span>
                    </div>
                    @endforeach
                    @if(empty($profile->blind_spots))
                        <p class="text-xs text-[#8a6a30] font-instrument">Belum terdeteksi</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Key Turning Point -->
        @if($profile->key_turning_point)
        <div class="notch-sm bg-[#1c1810] border border-[#332b1c] p-4 mb-4">
            <h3 class="text-[10px] uppercase tracking-wider text-[#d6a94e] mb-2 font-semibold font-instrument">Momen Kunci</h3>
            <p class="text-sm text-[#cdc2a0] leading-relaxed font-field">{{ $profile->key_turning_point }}</p>
        </div>
        @endif

        <!-- Missed Opportunities -->
        @if($profile->missed_opportunities)
        <div class="notch-sm bg-[#1c1810] border border-[#332b1c] p-4 mb-4">
            <h3 class="text-[10px] uppercase tracking-wider text-[#a89c7d] mb-2 font-semibold font-instrument">Peluang yang Terlewat</h3>
            <p class="text-sm text-[#cdc2a0] leading-relaxed font-field">{{ $profile->missed_opportunities }}</p>
        </div>
        @endif

        <!-- Coaching Recommendations — final actionable takeaway, emphasized -->
        @if($profile->coaching_recommendations)
        <div class="notch-sm p-5 mt-6" style="background:rgba(214,169,78,.08);border:1px solid #6b5325;">
            <h3 class="text-[10px] uppercase tracking-wider text-[#d6a94e] mb-2 font-semibold font-instrument">Rekomendasi Coaching</h3>
            <p class="text-sm text-[#e8dfc8] leading-relaxed font-field">{{ $profile->coaching_recommendations }}</p>
        </div>
        @endif

    </div>
    </div>
    </div>

    {{-- Real-World Challenge — separate mission card, distinct CTA --}}
    @if($result->realWorldChallenge)
        @php $challenge = $result->realWorldChallenge; @endphp
        <div class="mb-8">
            <div class="notch-md p-6" style="background:linear-gradient(160deg,#241f17,#1c1810);border:1px solid #d6a94e;">
                <div class="flex items-center gap-2 mb-4">
                    <span class="text-2xl">🎯</span>
                    <h2 class="text-lg font-bold text-[#d6a94e] font-expedition tracking-wide">Tantangan Dunia Nyata</h2>
                </div>
                <p class="text-sm text-[#e8dfc8] leading-relaxed mb-4 font-field">{{ $challenge->challenge }}</p>

                <div class="notch-sm bg-[#15130f] p-3 mb-4">
                    <h4 class="text-[10px] uppercase tracking-wider text-[#8a6a30] mb-1 font-instrument">Mengapa tantangan ini?</h4>
                    <p class="text-xs text-[#a89c7d] leading-relaxed font-field">{{ $challenge->why_this_challenge }}</p>
                </div>

                <div class="flex items-center justify-between text-xs font-instrument">
                    <span class="text-[#a89c7d]">
                        Deadline: {{ $challenge->deadline ? $challenge->deadline->format('d M Y') : '1 minggu' }}
                    </span>
                    @if(!$challenge->is_completed)
                        <button wire:click="markChallengeCompleted({{ $result->id }})"
                                class="px-4 py-1.5 notch-sm bg-[#7fae6c] text-[#15130f] font-semibold hover:bg-[#a8c79a] uppercase tracking-wider">
                            Tandai Selesai
                        </button>
                    @else
                        <span class="px-3 py-1 notch-sm text-[#7fae6c] uppercase tracking-wider" style="background:rgba(107,156,90,.15);">✓ Selesai</span>
                    @endif
                </div>
            </div>
        </div>
    @endif
@endif
@endforeach

{{-- Turn History --}}
<div class="mt-4 relative">
    <h3 class="font-expedition text-sm font-semibold text-[#cdc2a0] mb-3 tracking-wide">Riwayat Ekspedisi</h3>
    <div class="space-y-2 max-h-96 overflow-y-auto">
        @foreach($turns as $t)
        <div class="p-3 notch-sm bg-[#1c1810] border border-[#332b1c] text-xs font-instrument">
            <div class="flex items-center gap-2 mb-1">
                <span class="font-semibold text-[#e8dfc8] font-field">{{ $t->player->display_name }}</span>
                pilih <span class="font-bold text-[#d6a94e]">{{ $t->chosen_option }}</span>
                @if($t->card)
                    <span class="px-1.5 py-0.5 notch-sm text-[#8a6a30] bg-[#241f17]">{{ ucfirst($t->card->kategori) }}</span>
                @endif
                @if($t->was_hidden)
                    <span class="pill-notch pill-brass">Tersembunyi</span>
                @endif
            </div>
            @if($t->card)
                <p class="text-[#8a6a30] mb-1 line-clamp-1 font-field">{{ Str::limit($t->card->teks_situasi, 80) }}</p>
            @endif
            <div class="flex gap-2 text-[#a89c7d] flex-wrap">
                <span>MP{{ $t->mp_effect>=0?'+':'' }}{{ $t->mp_effect }}</span>
                <span>SP{{ $t->sp_effect>=0?'+':'' }}{{ $t->sp_effect }}</span>
                <span>TT{{ $t->tt_effect>=0?'+':'' }}{{ $t->tt_effect }}</span>
                @if($t->reputation_effect)
                    <span class="{{ $t->reputation_effect >= 0 ? 'text-[#7fae6c]' : 'text-[#e6603a]' }}">R{{ $t->reputation_effect >= 0 ? '+' : '' }}{{ $t->reputation_effect }}</span>
                @endif
                @if($t->risk_die_result)<span class="text-[#8a6a30]">| Die:{{ $t->risk_die_result }}</span>@endif
                @if($t->dysfunction_triggered)<span class="text-[#e6603a]">| {{ str_replace('_',' ',$t->dysfunction_triggered) }}</span>@endif
                @if($t->rope_bridge_success)<span class="text-[#7fae6c]">| Rope Bridge OK</span>@endif
                @if($t->cross_player_effects && count($t->cross_player_effects) > 0)
                    <span class="text-[#7fae6c]">| Tim Effect</span>
                @endif
            </div>
        </div>
        @endforeach
    </div>
</div>

<div class="text-center mt-8 relative">
    <a href="{{ route('dashboard') }}" class="px-6 py-2.5 notch-sm border border-[#4a3a1b] text-[#a89c7d] text-sm font-instrument uppercase tracking-wider">Kembali ke Basecamp</a>
</div>
</div></div>
