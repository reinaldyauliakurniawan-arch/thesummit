@props(['player'=>null,'thresholdKey'=>null])
@if($player)
<div class="fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4" wire:click="skipRopeBridge">
<div class="bg-mountain-800 rounded-2xl border-2 border-trust-500 p-6 max-w-md w-full shadow-2xl animate-slide-up" wire:click.stop>
<div class="text-center mb-4">
<h3 class="text-xl font-bold text-mountain-100 font-expedition">Rope Bridge Check</h3>
<p class="text-mountain-300 text-sm mt-1">Mencoba naik ke level berikutnya</p></div>
@if(config("summit.thresholds.$thresholdKey"))
@php $t=config("summit.thresholds.$thresholdKey"); @endphp
<div class="grid grid-cols-3 gap-3 mb-6">
<div class="text-center p-3 rounded-lg bg-mountain-900/50 border border-mountain-700"><div class="text-xs text-mountain-400 mb-1">MP</div><div class="text-lg font-bold font-mono {{ $player->mp>=$t['mp']?'text-basecamp-300':'text-crisis-400' }}">{{ $player->mp }}/{{ $t['mp'] }}</div></div>
<div class="text-center p-3 rounded-lg bg-mountain-900/50 border border-mountain-700"><div class="text-xs text-mountain-400 mb-1">SP</div><div class="text-lg font-bold font-mono {{ $player->sp>=$t['sp']?'text-camp-300':'text-crisis-400' }}">{{ $player->sp }}/{{ $t['sp'] }}</div></div>
@if($t['tt']>0)<div class="text-center p-3 rounded-lg bg-mountain-900/50 border border-mountain-700"><div class="text-xs text-mountain-400 mb-1">TT</div><div class="text-lg font-bold font-mono {{ $player->tt>=$t['tt']?'text-trust-300':'text-crisis-400' }}">{{ $player->tt }}/{{ $t['tt'] }}</div></div>@endif
</div>@endif
<div class="flex gap-3">
<button wire:click="skipRopeBridge" class="flex-1 px-4 py-2.5 rounded-xl border border-mountain-600 text-mountain-300 hover:bg-mountain-700 text-sm">Lewati</button>
<button wire:click="attemptRopeBridge" class="flex-1 px-4 py-2.5 rounded-xl bg-trust-500 text-mountain-950 font-bold hover:bg-trust-400 text-sm">Lintasi Rope Bridge</button>
</div></div></div>
@endif
