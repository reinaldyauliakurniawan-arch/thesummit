<div wire:poll.10s>
    <div class="max-w-4xl mx-auto px-4 pt-6 pb-4">
        <h1 class="text-2xl font-bold font-expedition text-mountain-100">Basecamp Dashboard</h1>
        <p class="text-mountain-400 text-sm mt-1">Selamat datang, {{ auth()->user()->name }}!</p>
    </div>

    <div class="max-w-4xl mx-auto px-4 pb-8 space-y-6">
        <!-- Create room CTA -->
        <div class="bg-mountain-900/50 rounded-2xl border border-mountain-800 p-5 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
            <div>
                <h2 class="font-semibold text-mountain-100">Mulai Ekspedisi Baru</h2>
                <p class="text-sm text-mountain-400">Buat room dan undang 2-5 pendaki lainnya.</p>
            </div>
            <form method="POST" action="{{ route('rooms.store') }}">
                @csrf
                <button class="px-6 py-2.5 rounded-xl bg-trust-500 text-mountain-950 font-bold hover:bg-trust-400 text-sm whitespace-nowrap">
                    + Buat Room
                </button>
            </form>
        </div>

        <!-- Notifications -->
        @if($un->count() > 0)
        <div class="bg-mountain-900/50 rounded-2xl border border-trust-500/30 p-4">
            <h2 class="font-semibold text-mountain-200 text-sm mb-3 flex items-center gap-2">
                <span class="w-2 h-2 bg-trust-400 rounded-full animate-pulse"></span>
                Notifikasi
            </h2>
            <div class="space-y-2">
                @foreach($un as $notification)
                <a href="{{ $notification->data['url'] ?? '#' }}"
                   wire:click="markRead('{{ $notification->id }}')"
                   class="block p-3 rounded-lg bg-mountain-800/50 hover:bg-mountain-800">
                    <p class="text-sm text-mountain-200">{{ $notification->data['message'] ?? '' }}</p>
                    <p class="text-xs text-mountain-500 mt-1">{{ $notification->created_at->diffForHumans() }}</p>
                </a>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Waiting rooms -->
        @if($wr->count() > 0)
        <div>
            <h2 class="font-semibold text-mountain-200 text-sm mb-3">Menunggu Pemain</h2>
            <div class="space-y-2">
                @foreach($wr as $room)
                <a href="{{ route('rooms.lobby', $room) }}"
                   class="block p-4 rounded-xl bg-mountain-900/50 border border-mountain-800 hover:border-trust-500/50">
                    <div class="flex items-center justify-between">
                        <div>
                            <span class="font-mono font-bold text-trust-400">{{ $room->code }}</span>
                            <span class="text-sm text-mountain-300 ml-2">{{ $room->players->count() }}/6</span>
                        </div>
                        <span class="text-xs text-mountain-500">{{ $room->host->name }}</span>
                    </div>
                </a>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Active/finished games -->
        @if($ag->count() > 0)
        <div>
            <h2 class="font-semibold text-mountain-200 text-sm mb-3">Game Aktif</h2>
            <div class="space-y-2">
                @foreach($ag as $gamePlayer)
                @php $gameRoom = $gamePlayer->room; @endphp
                <a href="{{ $gameRoom->status === 'finished' ? route('game.summary', $gameRoom) : route('game.board', $gameRoom) }}"
                   class="block p-4 rounded-xl bg-mountain-900/50 border border-mountain-800 hover:border-trust-500/50">
                    <div class="flex items-center justify-between">
                        <div>
                            <span class="font-mono font-bold text-trust-400">{{ $gameRoom->code }}</span>
                            <span class="text-xs ml-2 px-2 py-0.5 rounded-full bg-camp-800 text-camp-200">
                                {{ ucfirst($gameRoom->status) }}
                            </span>
                        </div>
                    </div>
                </a>
                @endforeach
            </div>
        </div>
        @endif
    </div>
</div>