<div wire:poll.5s="refreshBoard">
    <div class="relative w-full h-32 -mt-4 mb-4 overflow-hidden bg-[#1c1810]">
    <div class="absolute inset-0" style="background-image:repeating-radial-gradient(circle at 15% 30%, transparent 0, transparent 30px, rgba(214,169,78,0.06) 31px, transparent 32px), repeating-radial-gradient(circle at 85% 70%, transparent 0, transparent 44px, rgba(214,169,78,0.05) 45px, transparent 46px);"></div>
    <div class="absolute inset-0 flex items-center justify-center">
        <span class="font-instrument text-[11px] tracking-[.3em] uppercase text-[#8a6a30]">{{ ucfirst($myPlayer->current_level) }} Level</span>
    </div>
    <div class="absolute inset-0 bg-gradient-to-t from-[#15130f] via-[#15130f]/60 to-transparent"></div>
</div>
<div class="max-w-6xl mx-auto px-4 pt-4 pb-8">

        <!-- Header -->
        <div class="flex items-center justify-between mb-4 font-instrument">
            <div>
                <span class="text-[#d6a94e] font-bold tracking-widest">{{ $room->code }}</span>
                @if($room->status->value === 'final_round')
                    <span class="ml-2 pill-notch pill-ember">Final Round</span>
                @else
                    <span class="ml-2 pill-notch" style="color:#7fae6c;border-color:#3d5a33;background:rgba(107,156,90,.1);">Bermain</span>
                @endif
            </div>
            <div class="flex items-center gap-2 text-[10px] uppercase tracking-wider">
                <button wire:click="refreshBoard" wire:loading.attr="disabled" class="px-3 py-1 notch-sm border border-[#4a3a1b] text-[#a89c7d] disabled:opacity-50">
                    Refresh
                </button>
            </div>
        </div>

        <div class="lg:grid lg:grid-cols-2 lg:gap-6 lg:items-start">

        <!-- Left column: stats + visual representation. Kept together and
             always visible so the acting player sees team impact at a glance
             while making a decision on the right. -->
        <div class="lg:min-w-0">

        <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 mb-6">
            @foreach($players as $player)
            @php
                $avatarColors = ['#d6a94e','#7fae6c','#e6603a','#8a97ab'];
                $avatarColor = $avatarColors[$player->id % count($avatarColors)];
                $mpPct = min(100, ($player->mp / 12) * 100);
                $spPct = min(100, ($player->sp / 12) * 100);
                $ttPct = min(100, ($player->tt / 10) * 100);
            @endphp
            <div class="relative p-3 notch-sm {{ $room->current_turn_player_id === $player->id ? 'bg-[#241f17] border border-[#d6a94e]' : 'bg-[#1c1810] border border-[#332b1c]' }}">
                @if($room->current_turn_player_id === $player->id)
                    <div class="absolute -top-1.5 -right-1.5 w-3 h-3 bg-[#d6a94e] rounded-full animate-pulse"></div>
                @endif
                <div class="flex items-center gap-2 mb-2">
                    <div class="w-6 h-6 rounded-full flex items-center justify-center text-[10px] font-bold text-[#15130f] flex-shrink-0" style="background:{{ $avatarColor }};">{{ strtoupper(substr($player->display_name,0,1)) }}</div>
                    <div class="text-xs font-field font-semibold {{ $room->current_turn_player_id === $player->id ? 'text-[#d6a94e]' : 'text-[#cdc2a0]' }} truncate">
                        {{ $player->display_name }}{{ $room->current_turn_player_id === $player->id ? ' (giliran)' : '' }}
                    </div>
                </div>
                <div class="space-y-1 font-instrument">
                    <div class="flex items-center gap-1.5"><span class="text-[10px] text-[#8a6a30] w-4">MP</span><div class="flex-1 h-1.5 bg-[#15130f] border border-[#332b1c] overflow-hidden"><div class="h-full bg-[#d6a94e]" style="width:{{ $mpPct }}%"></div></div><span class="text-[10px] text-[#a89c7d] w-4 text-right">{{ $player->mp }}</span></div>
                    <div class="flex items-center gap-1.5"><span class="text-[10px] text-[#8a6a30] w-4">SP</span><div class="flex-1 h-1.5 bg-[#15130f] border border-[#332b1c] overflow-hidden"><div class="h-full bg-[#d6a94e]" style="width:{{ $spPct }}%"></div></div><span class="text-[10px] text-[#a89c7d] w-4 text-right">{{ $player->sp }}</span></div>
                    <div class="flex items-center gap-1.5"><span class="text-[10px] text-[#8a6a30] w-4">TT</span><div class="flex-1 h-1.5 bg-[#15130f] border border-[#332b1c] overflow-hidden"><div class="h-full bg-[#d6a94e]" style="width:{{ $ttPct }}%"></div></div><span class="text-[10px] text-[#a89c7d] w-4 text-right">{{ $player->tt }}</span></div>
                </div>
                <div class="flex items-center justify-between mt-2 pt-1.5 border-t border-[#332b1c] font-instrument">
                    <span class="text-[10px] {{ ($player->reputation ?? 0) >= 0 ? 'text-[#7fae6c]' : 'text-[#e6603a]' }}">Rep {{ $player->reputation ?? 0 }} · Res {{ $player->resources ?? 0 }}</span>
                </div>
                <div class="text-[10px] text-[#8a6a30] mt-1 font-instrument uppercase tracking-wider">{{ ucfirst($player->current_level) }}</div>
            </div>
            @endforeach
        </div>

        <!-- My progress bar -->
        <div class="mb-6">
            <x-mountain-board :players="$players" :currentPlayerId="$room->current_turn_player_id" />
        </div>

        </div>

        <!-- Right column: turn / actions + log. -->
        <div class="lg:min-w-0 mt-6 lg:mt-0">

        <!-- V2: Active Consequences Panel -->
        @if(!empty($activeConsequences))
            <div class="mb-4 p-3 notch-sm bg-[#241f17] border border-[#6b5325]">
                <h3 class="text-xs font-semibold text-[#d6a94e] mb-2 flex items-center gap-1 font-instrument uppercase tracking-wider">
                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                    Konsekuensi Aktif
                </h3>
                <div class="space-y-1.5 max-h-32 overflow-y-auto font-field">
                    @foreach($activeConsequences as $cons)
                    <div class="text-xs text-[#cdc2a0] flex items-start gap-1">
                        <span class="text-[#e6603a] flex-shrink-0">⏳</span>
                        <span>{{ $cons['description'] }} <span class="text-[#d6a94e] font-instrument">({{ $cons['stat'] }}{{ $cons['delta'] >= 0 ? '+' : '' }}{{ $cons['delta'] }})</span></span>
                    </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- V2: Active Votes -->
        @if(!empty($activeVotes))
            <div class="mb-4 p-3 notch-sm bg-[#241f17] border border-[#6b5325]">
                @foreach($activeVotes as $vote)
                <h3 class="text-xs font-semibold text-[#d6a94e] mb-2 font-instrument">🗳️ {{ $vote['vote_topic'] }}</h3>
                <p class="text-xs text-[#cdc2a0] mb-2 font-field">{{ $vote['vote_description'] }}</p>
                <div class="flex gap-2 flex-wrap font-instrument text-xs uppercase tracking-wider">
                    @foreach(($vote['options'] ?? []) as $option)
                    <button wire:click="castVoteOnActive({{ $vote['id'] }}, '{{ $option }}')"
                            class="px-3 py-1 notch-sm border {{ $voteChoice === $option ? 'border-[#d6a94e] bg-[#d6a94e] text-[#15130f]' : 'border-[#4a3a1b] text-[#a89c7d] hover:border-[#d6a94e]' }}">
                        {{ $option }}
                    </button>
                    @endforeach
                </div>
                @endforeach
            </div>
        @endif

        <!-- Status message -->
        @if($message)
            <div class="mb-4 p-3 notch-sm bg-[#241f17] border border-[#6b5325] text-sm text-center font-field text-[#cdc2a0]">
                {{ $message }}
            </div>
        @endif

        <!-- Waiting for turn -->
        <!-- Draw card button: hotseat, current player draws on the shared device -->
        @if($isMyTurn && !$showCard && !$showEffects)
            <div class="text-center py-6">
                <p class="text-[#e8dfc8] mb-1 font-semibold font-field text-lg">Giliran {{ $myPlayer->display_name }}!</p>
                <p class="text-xs text-[#a89c7d] mb-4 font-field">Serahkan perangkat ke {{ $myPlayer->display_name }} untuk mengambil kartu.</p>
                <button wire:click="drawCard"
                        class="px-8 py-3 notch-md bg-[#d6a94e] text-[#15130f] font-bold text-lg hover:bg-[#e3c483] animate-pulse-gold font-instrument uppercase tracking-wider">
                    Ambil Expedition Card
                </button>
                <p class="text-xs text-[#8a6a30] mt-2 font-instrument">
                    Turn #{{ $myPlayer->turns()->count() + 1 }}
                    — {{ $myPlayer->turns()->count() % 2 === 0 ? 'Mindset' : 'Skillset' }}
                </p>
            </div>
        @endif

        <!-- Card display (choosing) -->
        @if($showCard && $currentCard)
            <x-expedition-card :card="$currentCard" :choosing="true" />
        @endif

        <!-- Effects display (v2 enhanced) -->
        @if($showEffects && !empty($lastEffects))
            <x-expedition-card
                :showEffects="true"
                :effects="$lastEffects"
                :riskDieResult="$riskDieResult"
                :dysfunction="$dysfunctionTriggered"
                :wasHidden="$wasHidden"
                :hiddenInfo="$hiddenInfo"
                :createdConsequences="$createdConsequences"
                :crossPlayerEffects="$crossPlayerEffects" />

            <!-- Forum: optional discussion before handing off the turn -->
            <div class="max-w-lg mx-auto mt-4">
                <button wire:click="toggleDiscussion"
                        class="w-full px-4 py-2 notch-sm border border-[#4a3a1b] text-[#a89c7d] text-xs font-instrument uppercase tracking-wider hover:border-[#d6a94e] hover:text-[#d6a94e]">
                    {{ $showDiscussion ? 'Tutup Forum Diskusi' : 'Buka Forum Diskusi' }}
                </button>
                @if($showDiscussion)
                <div class="mt-2 p-4 notch-sm bg-[#1c1810] border border-[#332b1c] text-center">
                    <p class="text-sm text-[#cdc2a0] font-field">Diskusikan keputusan ini sebentar dengan pemain lain di meja. Tidak ada yang dicatat sistem — ini murni ruang obrolan.</p>
                </div>
                @endif
            </div>

            <div class="text-center mt-4">
                <button wire:click="nextTurn"
                        class="px-6 py-2 notch-sm bg-[#d6a94e] text-[#15130f] font-bold text-sm font-instrument uppercase tracking-wider hover:bg-[#e3c483]">
                    Lanjut ke Giliran Berikutnya
                </button>
            </div>
        @endif

        <!-- Rope Bridge check modal -->
        @if($showRopeBridge)
            <x-rope-bridge-check
                :player="$myPlayer"
                :thresholdKey="$myPlayer->current_level === 'basecamp' ? 'to_camp' : 'to_summit'" />
        @endif

        <!-- Turn log: fixed short height, always scrollable — not meant to
             grow long, just a quick scan-back reference. -->
        <div class="mt-8">
            <h3 class="font-expedition text-sm font-semibold text-[#cdc2a0] mb-3 tracking-wide">Log Ekspedisi</h3>
            <div class="space-y-2 max-h-64 overflow-y-auto">
                @foreach($allTurns as $turn)
                <div class="p-3 notch-sm bg-[#1c1810] border border-[#332b1c] text-xs font-instrument">
                    <div class="flex items-center gap-2 mb-1">
                        <span class="font-semibold text-[#e8dfc8] font-field">{{ $turn->player->display_name }}</span>
                        <span class="text-[#8a6a30]">pilih</span>
                        <span class="font-bold text-[#d6a94e]">{{ $turn->chosen_option }}</span>
                        @if($turn->was_hidden)
                            <span class="pill-notch pill-brass">HIDDEN</span>
                        @endif
                    </div>
                    <div class="flex gap-2 text-[#a89c7d] flex-wrap">
                        <span>MP{{ $turn->mp_effect >= 0 ? '+' : '' }}{{ $turn->mp_effect }}</span>
                        <span>SP{{ $turn->sp_effect >= 0 ? '+' : '' }}{{ $turn->sp_effect }}</span>
                        <span>TT{{ $turn->tt_effect >= 0 ? '+' : '' }}{{ $turn->tt_effect }}</span>
                        @if($turn->reputation_effect)
                            <span class="{{ $turn->reputation_effect >= 0 ? 'text-[#7fae6c]' : 'text-[#e6603a]' }}">R{{ $turn->reputation_effect >= 0 ? '+' : '' }}{{ $turn->reputation_effect }}</span>
                        @endif
                        @if($turn->risk_die_result)
                            <span class="text-[#8a6a30]">| Die:{{ $turn->risk_die_result }}</span>
                        @endif
                        @if($turn->dysfunction_triggered)
                            <span class="text-[#e6603a]">| Dysfunction!</span>
                        @endif
                        @if($turn->cross_player_effects && count($turn->cross_player_effects) > 0)
                            <span class="text-[#7fae6c]">| Tim Effect</span>
                        @endif
                    </div>
                    @if($turn->hidden_info_shown)
                        <div class="mt-1 text-[#d6a94e] italic text-[11px] font-field">{{ $turn->hidden_info_shown }}</div>
                    @endif
                </div>
                @endforeach
                @if($allTurns->isEmpty())
                    <p class="text-[#4a3a1b] text-xs text-center py-4 font-field">Belum ada giliran.</p>
                @endif
            </div>
        </div>

        </div>
        </div>
    </div>
</div>