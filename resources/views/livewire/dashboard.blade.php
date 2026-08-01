<div wire:poll.10s>
    <!-- Hero banner with background image -->
    <div class="relative w-full h-60 md:h-80 overflow-hidden">
        <div class="absolute inset-0 bg-[url('/images/expedition/hero-bg.jpg')] bg-cover bg-center"></div>
        <div class="absolute inset-0 bg-gradient-to-t from-mountain-950 via-mountain-950/70 to-mountain-950/20"></div>
        <div class="absolute bottom-0 left-0 right-0 p-6 md:p-8">
            <div class="max-w-4xl mx-auto">
                <h1 class="text-3xl md:text-4xl font-bold font-expedition text-white drop-shadow-lg">Basecamp Dashboard</h1>
                <p class="text-mountain-200 text-sm mt-1">Selamat datang, {{ auth()->user()->name }}!</p>
            </div>
        </div>
    </div>

    <div class="max-w-4xl mx-auto px-4 pb-8 space-y-6 -mt-2">
        <!-- Create room CTA -->
        <div class="bg-mountain-900/50 rounded-2xl border border-trust-500/30 animate-pulse-gold p-6 md:p-8 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div>
                <h2 class="font-semibold text-mountain-100 text-lg">Mulai Ekspedisi Baru</h2>
                <p class="text-sm text-mountain-400">Buat room dan undang 2-5 pendaki lainnya.</p>
            </div>
            <form method="POST" action="{{ route('rooms.store') }}">
                @csrf
                <button class="px-8 py-3.5 rounded-xl bg-trust-500 text-mountain-950 font-bold hover:bg-trust-400 text-base whitespace-nowrap">
                    &#9650; Buat Room
                </button>
            </form>
        </div>

        <!-- 3-step expedition explainer -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-6">
            <div class="rounded-2xl border border-basecamp-700/40 bg-basecamp-950/30 p-5">
                <div class="text-basecamp-400 text-xs font-bold uppercase tracking-wider mb-2">Tahap 1</div>
                <h3 class="font-expedition text-lg text-basecamp-200 mb-1">Basecamp</h3>
                <p class="text-sm text-mountain-400">Bangun fondasi kepemimpinan lewat keputusan sehari-hari.</p>
            </div>
            <div class="rounded-2xl border border-camp-700/40 bg-camp-950/30 p-5">
                <div class="text-camp-400 text-xs font-bold uppercase tracking-wider mb-2">Tahap 2</div>
                <h3 class="font-expedition text-lg text-camp-200 mb-1">Camp</h3>
                <p class="text-sm text-mountain-400">Hadapi dilema tim yang lebih kompleks dan penuh tekanan.</p>
            </div>
            <div class="rounded-2xl border border-summit-700/40 bg-summit-950/30 p-5">
                <div class="text-summit-400 text-xs font-bold uppercase tracking-wider mb-2">Tahap 3</div>
                <h3 class="font-expedition text-lg text-summit-200 mb-1">Summit</h3>
                <p class="text-sm text-mountain-400">Pimpin di level organisasi dengan taruhan tertinggi.</p>
            </div>
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
                            <span class="text-sm text-mountain-300 ml-2">{{ $room->players->count() }}/{{ config('summit.max_players') }}</span>
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
                                {{ $gameRoom->status->label() }}
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
