@props(['tt'=>0,'max'=>8,'compact'=>false,'showLabel'=>true])
@php $pct=min(100,($tt/$max)*100);$filled=min($max,$tt); @endphp
<div>
@if($showLabel)<div class="flex justify-between text-xs mb-0.5"><span class="text-trust-300 font-semibold">Trust Token</span><span class="text-trust-200 font-mono">{{ $tt }}/{{ $max }}</span></div>@endif
<div class="flex items-center gap-0.5">@for($i=1;$i<=$max;$i++)<div class="flex-1 h-{{ $compact?'1.5':'2.5' }} rounded-full transition-all duration-500 {{ $i<=$filled?'bg-trust-400 shadow-sm shadow-trust-400/50':'bg-mountain-800' }}"></div>@endfor</div>
</div>
