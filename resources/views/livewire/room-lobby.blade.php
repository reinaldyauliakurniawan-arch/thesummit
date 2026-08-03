<div>
<div class="max-w-lg mx-auto px-4 pt-6">
<div class="text-center mb-6"><span class="pill-notch pill-brass">{{ $room->playerCount() }}/{{ config('summit.max_players') }} pemain siap</span></div>
<p class="text-center text-xs text-[#a89c7d] mb-6 font-field">Main bergiliran di perangkat ini. Setiap pemain akan membaca kartunya sendiri saat gilirannya, lalu serahkan perangkat ke pemain berikutnya.</p>
<div class="space-y-2 mb-6">
@foreach($room->players as $p)
@php $avatarColors = ['#d6a94e','#7fae6c','#e6603a','#8a97ab','#c98fb0','#6fa8c9']; $c = $avatarColors[$p->turn_order % count($avatarColors)]; @endphp
<div class="flex items-center gap-3 p-3 notch-sm bg-[#1c1810] border border-[#332b1c]">
<div class="w-8 h-8 tag-notch text-sm" style="border-color:{{ $c }};color:{{ $c }};">{{ strtoupper(substr($p->display_name,0,1)) }}</div>
<div class="flex-1 text-sm font-field font-medium text-[#e8dfc8]">{{ $p->display_name }}</div>
</div>
@endforeach
</div>
@if($room->playerCount()<config('summit.min_players'))<div class="text-center text-sm text-[#a89c7d] mb-4 font-field">Butuh {{ config('summit.min_players')-$room->playerCount() }} pemain lagi.</div>@endif
<div class="flex gap-3">
<a href="{{ route('dashboard') }}" class="flex-1 text-center px-4 py-2.5 notch-sm border border-[#4a3a1b] text-[#a89c7d] text-sm font-instrument uppercase tracking-wider">Kembali</a>
@if($canStart)<form method="POST" action="{{ route('rooms.start',$room) }}" class="flex-1">@csrf<button class="w-full px-4 py-2.5 notch-sm bg-[#d6a94e] text-[#15130f] font-bold text-sm font-instrument uppercase tracking-wider">Mulai Pendakian!</button></form>@endif
</div></div></div>
