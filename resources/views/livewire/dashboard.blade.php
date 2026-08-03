<div wire:poll.10s>
    <!-- Hero banner -->
    <div class="relative w-full h-60 md:h-80 overflow-hidden bg-[#1c1810]">
        <div class="absolute inset-0" style="background-image:repeating-radial-gradient(circle at 20% 25%, transparent 0, transparent 34px, rgba(214,169,78,0.06) 35px, transparent 36px), repeating-radial-gradient(circle at 80% 75%, transparent 0, transparent 48px, rgba(214,169,78,0.05) 49px, transparent 50px);"></div>
        <div class="absolute right-[-60px] top-[-60px] w-[260px] h-[260px] opacity-[.08] pointer-events-none">
            <x-compass-watermark />
        </div>
        <div class="absolute inset-0 bg-gradient-to-t from-[#15130f] via-[#15130f]/75 to-[#15130f]/10"></div>
        <div class="absolute bottom-0 left-0 right-0 p-6 md:p-8">
            <div class="max-w-4xl mx-auto">
                <h1 class="text-3xl md:text-4xl font-bold font-expedition text-[#e8dfc8] tracking-wide drop-shadow-lg">Basecamp Dashboard</h1>
                <p class="text-[#a89c7d] text-sm mt-1 font-field">Selamat datang, {{ auth()->user()->name }}!</p>
            </div>
        </div>
    </div>

    <div class="max-w-4xl mx-auto px-4 pb-8 space-y-6 -mt-2">
        <!-- Create room CTA: host enters all local players at once (hotseat) -->
        <div class="card-frame animate-pulse-gold">
        <div class="card-frame-inner p-6 md:p-8" x-data="{ names: ['','','' ] }">
            <div class="grain-overlay" style="opacity:.2;"></div>
            <div class="relative z-10">
                <h2 class="font-expedition font-semibold text-[#e8dfc8] text-lg tracking-wide mb-1">Mulai Ekspedisi Baru</h2>
                <p class="text-sm text-[#a89c7d] font-field mb-4">Satu perangkat, mainkan bergiliran. Isi nama 3–6 pendaki.</p>
                <form method="POST" action="{{ route('rooms.store') }}" class="space-y-2">
                    @csrf
                    <template x-for="(n, i) in names" :key="i">
                        <div class="flex gap-2">
                            <input :name="'names[' + i + ']'" x-model="names[i]" type="text" required maxlength="60"
                                   :placeholder="'Nama Pendaki ' + (i+1)"
                                   class="flex-1 notch-sm bg-[#1c1810] border border-[#4a3a1b] text-[#e8dfc8] text-sm p-2.5 font-field">
                            <button type="button" x-show="names.length > 3" x-on:click="names.splice(i,1)"
                                    class="px-3 notch-sm border border-[#4a3a1b] text-[#e6603a] text-xs">&times;</button>
                        </div>
                    </template>
                    <div class="flex items-center justify-between pt-2">
                        <button type="button" x-show="names.length < 6" x-on:click="names.push('')"
                                class="text-xs text-[#d6a94e] font-instrument uppercase tracking-wider hover:underline">+ Tambah Pendaki</button>
                        <button type="submit"
                                class="px-8 py-3 notch-md bg-[#d6a94e] text-[#15130f] font-bold hover:bg-[#e3c483] text-sm font-instrument uppercase tracking-wider">
                            &#9650; Mulai Ekspedisi
                        </button>
                    </div>
                </form>
            </div>
        </div>
        </div>

        <!-- 3-step expedition explainer -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-6">
            <div class="notch-sm border border-[#332b1c] bg-[#1c1810] p-5">
                <div class="w-8 h-8 tag-notch mb-3">B</div>
                <div class="text-[#8a6a30] text-xs font-bold uppercase tracking-wider mb-1 font-instrument">Tahap 1</div>
                <h3 class="font-expedition text-lg text-[#e8dfc8] mb-1 tracking-wide">Basecamp</h3>
                <p class="text-sm text-[#a89c7d] font-field">Bangun fondasi kepemimpinan lewat keputusan sehari-hari.</p>
            </div>
            <div class="notch-sm border border-[#332b1c] bg-[#1c1810] p-5">
                <div class="w-8 h-8 tag-notch mb-3" style="border-color:#7fae6c;color:#7fae6c;">C</div>
                <div class="text-[#7fae6c] text-xs font-bold uppercase tracking-wider mb-1 font-instrument">Tahap 2</div>
                <h3 class="font-expedition text-lg text-[#e8dfc8] mb-1 tracking-wide">Camp</h3>
                <p class="text-sm text-[#a89c7d] font-field">Hadapi dilema tim yang lebih kompleks dan penuh tekanan.</p>
            </div>
            <div class="notch-sm border border-[#332b1c] bg-[#1c1810] p-5">
                <div class="w-8 h-8 tag-notch mb-3" style="border-color:#8a97ab;color:#8a97ab;">S</div>
                <div class="text-[#8a97ab] text-xs font-bold uppercase tracking-wider mb-1 font-instrument">Tahap 3</div>
                <h3 class="font-expedition text-lg text-[#e8dfc8] mb-1 tracking-wide">Summit</h3>
                <p class="text-sm text-[#a89c7d] font-field">Pimpin di level organisasi dengan taruhan tertinggi.</p>
            </div>
        </div>

        <!-- Notifications -->
        @if($un->count() > 0)
        <div class="notch-sm bg-[#241f17] border border-[#6b5325] p-4">
            <h2 class="font-expedition font-semibold text-[#e8dfc8] text-sm mb-3 flex items-center gap-2 tracking-wide">
                <span class="w-2 h-2 bg-[#d6a94e] rounded-full animate-pulse"></span>
                Notifikasi
            </h2>
            <div class="space-y-2">
                @foreach($un as $notification)
                <a href="{{ $notification->data['url'] ?? '#' }}"
                   wire:click="markRead('{{ $notification->id }}')"
                   class="block p-3 notch-sm bg-[#1c1810] hover:bg-[#2c2519]">
                    <p class="text-sm text-[#cdc2a0] font-field">{{ $notification->data['message'] ?? '' }}</p>
                    <p class="text-xs text-[#8a6a30] mt-1 font-instrument">{{ $notification->created_at->diffForHumans() }}</p>
                </a>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Waiting rooms -->
        @if($wr->count() > 0)
        <div>
            <h2 class="font-expedition font-semibold text-[#cdc2a0] text-sm mb-3 tracking-wide">Menunggu Pemain</h2>
            <div class="space-y-2">
                @foreach($wr as $room)
                <a href="{{ route('rooms.lobby', $room) }}"
                   class="block p-4 notch-sm bg-[#1c1810] border border-[#332b1c] hover:border-[#d6a94e]">
                    <div class="flex items-center justify-between font-instrument">
                        <div>
                            <span class="font-bold text-[#d6a94e]">{{ $room->code }}</span>
                            <span class="text-sm text-[#cdc2a0] ml-2">{{ $room->players->count() }}/{{ config('summit.max_players') }}</span>
                        </div>
                    </div>
                </a>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Active/finished games -->
        @if($ag->count() > 0)
        <div>
            <h2 class="font-expedition font-semibold text-[#cdc2a0] text-sm mb-3 tracking-wide">Game Aktif</h2>
            <div class="space-y-2">
                @foreach($ag as $gameRoom)
                <a href="{{ $gameRoom->status->value === 'finished' ? route('game.summary', $gameRoom) : route('game.board', $gameRoom) }}"
                   class="block p-4 notch-sm bg-[#1c1810] border border-[#332b1c] hover:border-[#d6a94e]">
                    <div class="flex items-center justify-between font-instrument">
                        <div>
                            <span class="font-bold text-[#d6a94e]">{{ $gameRoom->code }}</span>
                            <span class="text-xs ml-2 pill-notch" style="color:#7fae6c;border-color:#3d5a33;background:rgba(107,156,90,.1);">
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
