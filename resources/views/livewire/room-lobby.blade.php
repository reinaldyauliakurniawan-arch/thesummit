<div>
<div class="max-w-lg mx-auto px-4 pt-6">
<div class="text-center mb-6"><div class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-mountain-800 border border-trust-500/30">
<span class="text-xs text-mountain-400">Kode Room:</span><span class="font-mono text-2xl font-bold text-trust-400 tracking-widest">{{ $room->code }}</span>
</div></div>
<div class="text-center mb-6"><span class="px-3 py-1 rounded-full text-xs font-semibold bg-trust-800 text-trust-200">Menunggu — {{ $room->playerCount() }}/6 pemain</span></div>
<div class="space-y-2 mb-6">@foreach($room->players as $p)
<div class="flex items-center gap-3 p-3 rounded-xl bg-mountain-900/50 border border-mountain-800">
<div class="w-8 h-8 rounded-full bg-mountain-700 flex items-center justify-center text-sm font-bold text-mountain-300">{{ strtoupper(substr($p->user->name,0,1)) }}</div>
<div class="flex-1 text-sm font-medium text-mountain-200">{{ $p->user->name }} @if($p->user_id===$room->host_user_id)<span class="text-xs text-trust-400 ml-1">(Host)</span>@endif</div>
</div>@endforeach</div>
@if($room->playerCount()<config('summit.min_players'))<div class="text-center text-sm text-mountain-400 mb-4">Butuh {{ config('summit.min_players')-$room->playerCount() }} pemain lagi.</div>@endif
<div class="flex gap-3">
<a href="{{ route('rooms.index') }}" class="flex-1 text-center px-4 py-2.5 rounded-xl border border-mountain-600 text-mountain-300 text-sm">Kembali</a>
@if($canStart)<form method="POST" action="{{ route('rooms.start',$room) }}">@csrf<button class="flex-1 px-4 py-2.5 rounded-xl bg-trust-500 text-mountain-950 font-bold text-sm">Mulai Pendakian!</button></form>@endif
</div></div></div>
