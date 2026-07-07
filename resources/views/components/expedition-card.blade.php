@props(['card'=>null,'showEffects'=>false,'effects'=>[],'riskDieResult'=>null,'dysfunction'=>null,'choosing'=>false])
@if($card)
<div class="max-w-lg mx-auto">
<div class="flex items-center justify-center gap-2 mb-3">
<span class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider {{ $card->level==='basecamp'?'bg-basecamp-500 text-white':($card->level==='camp'?'bg-camp-600 text-white':'bg-summit-600 text-summit-50') }}">{{ ucfirst($card->level) }}</span>
<span class="px-2 py-0.5 rounded-full text-xs bg-mountain-700 text-mountain-200">{{ ucfirst($card->kategori) }}</span>
@if($card->tipe==='krisis')<span class="px-2 py-0.5 rounded-full text-xs bg-crisis-600 text-white animate-pulse">Krisis</span>@endif
</div>
<div class="bg-mountain-800 rounded-2xl border-2 {{ $card->tipe==='krisis'?'border-crisis-500':'border-mountain-600' }} p-6 shadow-xl {{ $choosing?'animate-card-flip':'animate-fade-in' }}">
<div class="mb-6"><h4 class="text-xs uppercase tracking-wider text-mountain-400 mb-2 font-semibold">Situasi Ekspedisi</h4><p class="text-mountain-100 leading-relaxed text-sm">{{ $card->teks_situasi }}</p></div>
@if($choosing)
<div class="space-y-3">
<button wire:click="chooseOption('A')" class="w-full text-left p-4 rounded-xl border-2 border-mountain-600 hover:border-trust-400 bg-mountain-900/50 hover:bg-mountain-900 transition-all group">
<div class="flex items-start gap-3"><span class="w-8 h-8 rounded-lg bg-mountain-700 group-hover:bg-trust-500 flex items-center justify-center text-sm font-bold text-mountain-200 group-hover:text-white transition-colors flex-shrink-0">A</span><div>
<p class="text-mountain-100 text-sm leading-relaxed">{{ $card->opsi_a_teks }}</p>
<div class="flex gap-2 mt-1.5 text-xs"><span class="px-1.5 py-0.5 rounded bg-basecamp-900/50 text-basecamp-300">MP {{ $card->opsi_a_mp>=0?'+':'' }}{{ $card->opsi_a_mp }}</span><span class="px-1.5 py-0.5 rounded bg-camp-900/50 text-camp-300">SP {{ $card->opsi_a_sp>=0?'+':'' }}{{ $card->opsi_a_sp }}</span><span class="px-1.5 py-0.5 rounded bg-trust-900/50 text-trust-300">TT {{ $card->opsi_a_tt>=0?'+':'' }}{{ $card->opsi_a_tt }}</span></div>
</div></div></button>
<button wire:click="chooseOption('B')" class="w-full text-left p-4 rounded-xl border-2 border-mountain-600 hover:border-trust-400 bg-mountain-900/50 hover:bg-mountain-900 transition-all group">
<div class="flex items-start gap-3"><span class="w-8 h-8 rounded-lg bg-mountain-700 group-hover:bg-trust-500 flex items-center justify-center text-sm font-bold text-mountain-200 group-hover:text-white transition-colors flex-shrink-0">B</span><div>
<p class="text-mountain-100 text-sm leading-relaxed">{{ $card->opsi_b_teks }}</p>
<div class="flex gap-2 mt-1.5 text-xs"><span class="px-1.5 py-0.5 rounded bg-basecamp-900/50 text-basecamp-300">MP {{ $card->opsi_b_mp>=0?'+':'' }}{{ $card->opsi_b_mp }}</span><span class="px-1.5 py-0.5 rounded bg-camp-900/50 text-camp-300">SP {{ $card->opsi_b_sp>=0?'+':'' }}{{ $card->opsi_b_sp }}</span><span class="px-1.5 py-0.5 rounded bg-trust-900/50 text-trust-300">TT {{ $card->opsi_b_tt>=0?'+':'' }}{{ $card->opsi_b_tt }}</span></div>
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
@if($riskDieResult !== null)
<div class="border-t border-mountain-700 pt-3 mt-3">
<div class="text-sm text-mountain-300 mb-1">Risk Die: <span class="font-bold text-lg">{{ $riskDieResult }}</span></div>
@if($riskDieResult<=2)<div class="text-crisis-400 text-xs font-semibold animate-pulse">Dysfunction: {{ config("summit.dysfunctions.$dysfunction",$dysfunction) }} (TT -2)</div>
@elseif($riskDieResult>=5)<div class="text-trust-400 text-xs font-semibold">Bonus! TT +1</div>
@else<div class="text-mountain-400 text-xs">Netral</div>@endif
</div>@endif
</div></div>@endif
