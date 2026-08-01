@props(['tt'=>0,'max'=>8,'compact'=>false,'showLabel'=>true])
@php $pct=min(100,($tt/$max)*100);$filled=min($max,$tt); @endphp
<div>
@if($showLabel)<div class="flex justify-between text-[10px] font-instrument mb-0.5"><span class="text-[#d6a94e] font-semibold uppercase tracking-wider">Trust Token</span><span class="text-[#cdc2a0]">{{ $tt }}/{{ $max }}</span></div>@endif
<div class="flex items-center gap-0.5">@for($i=1;$i<=$max;$i++)<div class="flex-1 h-{{ $compact?'1.5':'2.5' }} transition-all duration-500 {{ $i<=$filled?'bg-[#d6a94e] shadow-[0_0_4px_rgba(214,169,78,.5)]':'bg-[#1c1810] border border-[#332b1c]' }}"></div>@endfor</div>
</div>
