@props(['card'=>null,'showEffects'=>false,'effects'=>[],'riskDieResult'=>null,'dysfunction'=>null,'choosing'=>false,'wasHidden'=>false,'hiddenInfo'=>null,'createdConsequences'=>[],'crossPlayerEffects'=>[]])
@if($card)
<div class="max-w-lg mx-auto">
<div class="flex items-center justify-center gap-2 mb-3">
<span class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider {{ $card->level==='basecamp'?'bg-basecamp-500 text-white':($card->level==='camp'?'bg-camp-600 text-white':'bg-summit-600 text-summit-50') }}">{{ ucfirst($card->level) }}</span>
<span class="px-2 py-0.5 rounded-full text-xs bg-mountain-700 text-mountain-200">{{ ucfirst($card->kategori) }}</span>
@if($card->tipe==='krisis')<span class="px-2 py-0.5 rounded-full text-xs bg-crisis-600 text-white animate-pulse">Krisis</span>@endif
@if($card->has_hidden_info)<span class="px-2 py-0.5 rounded-full text-xs bg-summit-800 text-summit-300">? Tersembunyi</span>@endif
</div>
<div class="bg-mountain-800 rounded-2xl border-2 {{ $card->tipe==='krisis'?'border-crisis-500':'border-mountain-600' }} p-6 shadow-xl {{ $choosing?'animate-card-flip':'animate-fade-in' }}">
<div class="mb-6"><h4 class="text-xs uppercase tracking-wider text-mountain-400 mb-2 font-semibold">Situasi Ekspedisi</h4><p class="text-mountain-100 leading-relaxed text-sm">{{ $card->teks_situasi }}</p></div>
@if($choosing)
<div class="space-y-3">
<button wire:click="chooseOption('A')" class="w-full text-left p-4 rounded-xl border-2 border-mountain-600 hover:border-trust-400 bg-mountain-900/50 hover:bg-mountain-900 transition-all group">
<div class="flex items-start gap-3"><span class="w-8 h-8 rounded-lg bg-mountain-700 group-hover:bg-trust-500 flex items-center justify-center text-sm font-bold text-mountain-200 group-hover:text-white transition-colors flex-shrink-0">A</span><div>
<p class="text-mountain-100 text-sm leading-relaxed">{{ $card->opsi_a_teks }}</p>
<div class="flex gap-2 mt-1.5 text-xs flex-wrap">
<span class="px-1.5 py-0.5 rounded bg-basecamp-900/50 text-basecamp-300">MP {{ $card->opsi_a_mp>=0?'+':'' }}{{ $card->opsi_a_mp }}</span>
<span class="px-1.5 py-0.5 rounded bg-camp-900/50 text-camp-300">SP {{ $card->opsi_a_sp>=0?'+':'' }}{{ $card->opsi_a_sp }}</span>
<span class="px-1.5 py-0.5 rounded bg-trust-900/50 text-trust-300">TT {{ $card->opsi_a_tt>=0?'+':'' }}{{ $card->opsi_a_tt }}</span>
@if($card->opsi_a_reputation != 0)<span class="px-1.5 py-0.5 rounded bg-summit-900/50 text-summit-300">R {{ $card->opsi_a_reputation>=0?'+':'' }}{{ $card->opsi_a_reputation }}</span>@endif
@if($card->opsi_a_resources != 0)<span class="px-1.5 py-0.5 rounded bg-mountain-700/50 text-mountain-300">Res {{ $card->opsi_a_resources>=0?'+':'' }}{{ $card->opsi_a_resources }}</span>@endif
</div>
@if($card->opsi_a_cross_player && count($card->opsi_a_cross_player) > 0)
<div class="mt-1 text-xs text-camp-300 flex items-center gap-1">
<svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"/></svg>
<span>Ada efek ke pemain lain</span>
</div>
@endif
@if($card->opsi_a_delayed_effects && count($card->opsi_a_delayed_effects) > 0)
<div class="mt-1 text-xs text-summit-300 flex items-center gap-1">
<span>⏳ Konsekuensi tertunda</span>
</div>
@endif
</div></div></button>
<button wire:click="chooseOption('B')" class="w-full text-left p-4 rounded-xl border-2 border-mountain-600 hover:border-trust-400 bg-mountain-900/50 hover:bg-mountain-900 transition-all group">
<div class="flex items-start gap-3"><span class="w-8 h-8 rounded-lg bg-mountain-700 group-hover:bg-trust-500 flex items-center justify-center text-sm font-bold text-mountain-200 group-hover:text-white transition-colors flex-shrink-0">B</span><div>
<p class="text-mountain-100 text-sm leading-relaxed">{{ $card->opsi_b_teks }}</p>
<div class="flex gap-2 mt-1.5 text-xs flex-wrap">
<span class="px-1.5 py-0.5 rounded bg-basecamp-900/50 text-basecamp-300">MP {{ $card->opsi_b_mp>=0?'+':'' }}{{ $card->opsi_b_mp }}</span>
<span class="px-1.5 py-0.5 rounded bg-camp-900/50 text-camp-300">SP {{ $card->opsi_b_sp>=0?'+':'' }}{{ $card->opsi_b_sp }}</span>
<span class="px-1.5 py-0.5 rounded bg-trust-900/50 text-trust-300">TT {{ $card->opsi_b_tt>=0?'+':'' }}{{ $card->opsi_b_tt }}</span>
@if($card->opsi_b_reputation != 0)<span class="px-1.5 py-0.5 rounded bg-summit-900/50 text-summit-300">R {{ $card->opsi_b_reputation>=0?'+':'' }}{{ $card->opsi_b_reputation }}</span>@endif
@if($card->opsi_b_resources != 0)<span class="px-1.5 py-0.5 rounded bg-mountain-700/50 text-mountain-300">Res {{ $card->opsi_b_resources>=0?'+':'' }}{{ $card->opsi_b_resources }}</span>@endif
</div>
@if($card->opsi_b_cross_player && count($card->opsi_b_cross_player) > 0)
<div class="mt-1 text-xs text-camp-300 flex items-center gap-1">
<svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"/></svg>
<span>Ada efek ke pemain lain</span>
</div>
@endif
@if($card->opsi_b_delayed_effects && count($card->opsi_b_delayed_effects) > 0)
<div class="mt-1 text-xs text-summit-300 flex items-center gap-1">
<span>⏳ Konsekuensi tertunda</span>
</div>
@endif
</div></div></button>
</div>@endif
</div>
@if($card->tipe==='krisis' && $choosing)<p class="text-center text-crisis-400 text-xs mt-3 animate-pulse">Kartu Krisis! Risk Die otomatis setelah pilihan.</p>@endif
</div>@endif

@if($showEffects && !empty($effects))
<div class="max-w-lg mx-auto animate-slide-up">
<div class="bg-mountain-800 rounded-2xl border border-mountain-600 p-6 shadow-xl text-center">
<h4 class="text-xs uppercase tracking-wider text-mountain-400 mb-4 font-semibold">Efek Diterapkan</h4>
<div class="flex justify-center gap-4 mb-4">
<div class="text-center"><div class="text-2xl font-bold font-mono {{ $effects['mp']>=0?'text-basecamp-300':'text-crisis-400' }}">{{ $effects['mp']>=0?'+':'' }}{{ $effects['mp'] }}</div><div class="text-xs text-mountain-400">MP</div></div>
<div class="text-center"><div class="text-2xl font-bold font-mono {{ $effects['sp']>=0?'text-camp-300':'text-crisis-400' }}">{{ $effects['sp']>=0?'+':'' }}{{ $effects['sp'] }}</div><div class="text-xs text-mountain-400">SP</div></div>
<div class="text-center"><div class="text-2xl font-bold font-mono {{ $effects['tt']>=0?'text-trust-300':'text-crisis-400' }}">{{ $effects['tt']>=0?'+':'' }}{{ $effects['tt'] }}</div><div class="text-xs text-mountain-400">TT</div></div>
</div>
@isset($effects['reputation'])
<div class="flex justify-center gap-4 mb-2">
<div class="text-center"><div class="text-lg font-bold font-mono {{ ($effects['reputation']??0)>=0?'text-summit-300':'text-crisis-400' }}">{{ ($effects['reputation']??0)>=0?'+':'' }}{{ $effects['reputation']??0 }}</div><div class="text-xs text-mountain-400">Rep</div></div>
<div class="text-center"><div class="text-lg font-bold font-mono {{ ($effects['resources']??0)>=0?'text-mountain-300':'text-crisis-400' }}">{{ ($effects['resources']??0)>=0?'+':'' }}{{ $effects['resources']??0 }}</div><div class="text-xs text-mountain-400">Res</div></div>
</div>
@endisset
@if($riskDieResult !== null)
<div class="border-t border-mountain-700 pt-3 mt-3">
<div class="text-sm text-mountain-300 mb-1">Risk Die: <span class="font-bold text-lg">{{ $riskDieResult }}</span></div>
@if($riskDieResult<=2)<div class="text-crisis-400 text-xs font-semibold animate-pulse">Dysfunction: {{ config("summit.dysfunctions.$dysfunction",$dysfunction) }} (TT -2) | Semua pemain terdampak!</div>
@elseif($riskDieResult>=5)<div class="text-trust-400 text-xs font-semibold">Bonus! TT +1</div>
@else<div class="text-mountain-400 text-xs">Netral</div>@endif
</div>@endif

{{-- V2: Hidden Info Reveal --}}
@if($wasHidden && $hiddenInfo)
<div class="border-t border-summit-700 pt-3 mt-3 animate-fade-in">
<div class="text-summit-300 text-xs font-semibold mb-1">Informasi Tersembunyi Terungkap:</div>
<div class="text-summit-200 text-xs italic">{{ $hiddenInfo }}</div>
</div>
@endif

{{-- V2: Created Consequences --}}
@if(!empty($createdConsequences))
<div class="border-t border-summit-700 pt-3 mt-3">
<div class="text-summit-300 text-xs font-semibold mb-1">Konsekuensi Baru Dibuat:</div>
@foreach($createdConsequences as $cons)
<div class="text-xs text-summit-200 mt-1">
    @if($cons['is_hidden'])
    <span class="text-summit-400">???</span> — <span class="text-mountain-400">efek tersembunyi</span>
    @else
    ⏳ {{ $cons['description'] }} ({{ $cons['stat'] }}{{ $cons['delta']>=0?'+':'' }}{{ $cons['delta'] }})
    @endif
</div>
@endforeach
</div>
@endif

{{-- V2: Cross-Player Effects --}}
@if(!empty($crossPlayerEffects))
<div class="border-t border-camp-700 pt-3 mt-3">
<div class="text-camp-300 text-xs font-semibold mb-1">Efek ke Tim:</div>
@foreach($crossPlayerEffects as $cpe)
<div class="text-xs text-camp-200 mt-1">
    {{ $cpe['target'] }}: {{ $cpe['stat'] }}{{ $cpe['delta']>=0?'+':'' }}{{ $cpe['delta'] }} — {{ $cpe['description'] }}
</div>
@endforeach
</div>
@endif

</div></div>@endif
