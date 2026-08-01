<div wire:poll.5s>
<div class="max-w-lg mx-auto px-4 pt-6">
<div class="text-center mb-6"><div class="inline-flex items-center gap-2 px-4 py-2 notch-sm bg-[#1c1810] border border-[#4a3a1b]">
<span class="text-xs text-[#a89c7d] font-instrument uppercase tracking-wider">Kode Room:</span><span class="font-instrument text-2xl font-bold text-[#d6a94e] tracking-widest">{{ $room->code }}</span>
</div></div>
<div class="text-center mb-6"><span class="pill-notch pill-brass">Menunggu — {{ $room->playerCount() }}/{{ config('summit.max_players') }} pemain</span></div>
<div class="space-y-2 mb-6">@foreach($room->players as $p)
<div class="flex items-center gap-3 p-3 notch-sm bg-[#1c1810] border border-[#332b1c]">
<div class="w-8 h-8 tag-notch text-sm">{{ strtoupper(substr($p->user->name,0,1)) }}</div>
<div class="flex-1 text-sm font-field font-medium text-[#e8dfc8]">{{ $p->user->name }} @if($p->user_id===$room->host_user_id)<span class="text-xs text-[#d6a94e] ml-1 font-instrument">(Host)</span>@endif</div>
</div>@endforeach</div>
@if($room->playerCount()<config('summit.min_players'))<div class="text-center text-sm text-[#a89c7d] mb-4 font-field">Butuh {{ config('summit.min_players')-$room->playerCount() }} pemain lagi.</div>@endif
<div class="flex gap-3">
<a href="{{ route('rooms.index') }}" class="flex-1 text-center px-4 py-2.5 notch-sm border border-[#4a3a1b] text-[#a89c7d] text-sm font-instrument uppercase tracking-wider">Kembali</a>
@if($canStart)<form method="POST" action="{{ route('rooms.start',$room) }}">@csrf<button class="flex-1 w-full px-4 py-2.5 notch-sm bg-[#d6a94e] text-[#15130f] font-bold text-sm font-instrument uppercase tracking-wider">Mulai Pendakian!</button></form>@endif
</div></div></div>