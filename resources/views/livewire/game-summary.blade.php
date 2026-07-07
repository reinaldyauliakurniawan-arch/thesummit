<div>
<div class="max-w-2xl mx-auto px-4 pt-6 pb-12">
<div class="text-center mb-8"><h1 class="text-3xl font-bold font-expedition text-mountain-100">Ekspedisi Selesai!</h1><p class="text-mountain-400 mt-1">Room {{ $room->code }}</p></div>

@if($results->first() && $results->first()->badge!=='none')
<div class="text-center mb-8 animate-slide-up"><x-player-badge :badge="$results->first()->badge" :rank="1" size="lg" />
<div class="mt-3"><h2 class="text-2xl font-bold text-mountain-100">{{ $results->first()->player->user->name }}</h2><p class="text-mountain-400 text-sm">Skor: {{ $results->first()->final_score }}</p></div>
@if($results->first()->badge==='the_carrier')<p class="text-trust-400 text-sm mt-2 italic">"The real winner is the one who makes everybody win."</p>@endif</div>
@endif

<div class="space-y-3 mb-8">@foreach($results as $r)
<div class="flex items-center gap-4 p-4 rounded-xl bg-mountain-900/50 border {{ $r->rank===1?'border-trust-500/50':'border-mountain-800' }}">
<div class="text-lg font-bold font-mono w-8 text-center {{ $r->rank===1?'text-trust-400':($r->rank===2?'text-mountain-300':'text-mountain-500') }} ">#{{ $r->rank }}</div>
<div class="flex-1"><div class="flex items-center gap-2"><span class="font-semibold text-mountain-200">{{ $r->player->user->name }}</span><x-player-badge :badge="$r->badge" size="sm" /></div>
<div class="flex gap-3 mt-1 text-xs"><span class="text-basecamp-300">MP {{ $r->final_mp }}</span><span class="text-camp-300">SP {{ $r->final_sp }}</span><span class="text-trust-300">TT {{ $r->final_tt }}</span></div>
<div class="text-xs text-mountain-500 mt-0.5">{{ ucfirst($r->final_level) }} — Skor: {{ $r->final_score }}</div></div>
<x-rope-meter :tt="$r->final_tt" :compact="true" /></div>
@endforeach</div>

<div><h3 class="text-sm font-semibold text-mountain-300 mb-3">Riwayat Ekspedisi</h3><div class="space-y-2 max-h-96 overflow-y-auto">
@foreach($turns as $t)<div class="p-3 rounded-lg bg-mountain-900/50 border border-mountain-800 text-xs">
<div class="flex items-center gap-2 mb-1"><span class="font-semibold text-mountain-200">{{ $t->player->user->name }}</span>pilih <span class="font-bold text-trust-300">{{ $t->chosen_option }}</span>
@if($t->card)<span class="px-1.5 py-0.5 rounded text-mountain-400 bg-mountain-800">{{ ucfirst($t->card->kategori) }}</span>@endif</div>
@if($t->card)<p class="text-mountain-500 mb-1 line-clamp-1">{{ Str::limit($t->card->teks_situasi,80) }}</p>@endif
<div class="flex gap-2 text-mountain-400"><span>MP{{ $t->mp_effect>=0?'+':'' }}{{ $t->mp_effect }}</span><span>SP{{ $t->sp_effect>=0?'+':'' }}{{ $t->sp_effect }}</span><span>TT{{ $t->tt_effect>=0?'+':'' }}{{ $t->tt_effect }}</span>
@if($t->risk_die_result)<span class="text-mountain-500">| Die:{{ $t->risk_die_result }}</span>@endif
@if($t->dysfunction_triggered)<span class="text-crisis-400">| {{ str_replace('_',' ',$t->dysfunction_triggered) }}</span>@endif
@if($t->rope_bridge_success)<span class="text-camp-300">| Rope Bridge OK</span>@endif</div>
</div>@endforeach</div></div>
<div class="text-center mt-8"><a href="{{ route('dashboard') }}" class="px-6 py-2.5 rounded-xl border border-mountain-600 text-mountain-300 text-sm">Kembali ke Basecamp</a></div>
</div></div>
