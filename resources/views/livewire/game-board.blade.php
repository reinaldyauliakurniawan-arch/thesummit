<div wire:poll.5s>
    <div class="relative w-full h-32 -mt-4 mb-4 overflow-hidden">
    <div class="absolute inset-0 bg-[url('/images/expedition/{{ $myPlayer->current_level }}-bg.jpg')] bg-cover bg-center"></div>
    <div class="absolute inset-0 bg-gradient-to-t from-mountain-950 via-mountain-950/60 to-mountain-950/10"></div>
</div>
<div class="max-w-2xl mx-auto px-4 pt-4 pb-8">

        <!-- Header -->
        <div class="flex items-center justify-between mb-4">
            <div>
                <span class="font-mono text-trust-400 font-bold">{{ $room->code }}</span>
                @if($room->status->value === 'final_round')
                    <span class="ml-2 px-2 py-0.5 rounded-full text-xs bg-trust-800 text-trust-200 font-bold animate-pulse">FINAL ROUND</span>
                @else
                    <span class="ml-2 px-2 py-0.5 rounded-full text-xs bg-camp-800 text-camp-200">Bermain</span>
                @endif
            </div>
            <div class="flex items-center gap-2">
                <button wire:click="showPromiseForm" class="text-xs text-mountain-400 px-2 py-1 rounded-lg border border-mountain-700 hover:border-trust-500 hover:text-trust-400" title="Buat Janji">
                    Janji
                </button>
                <button wire:click="refreshBoard" class="text-xs text-mountain-400 px-3 py-1 rounded-lg border border-mountain-700">
                    Refresh
                </button>
            </div>
        </div>

        <!-- Player grid with v2 stats -->
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 mb-6">
            @foreach($players as $player)
            @php
                $avatarColors = ['bg-trust-500','bg-camp-500','bg-crisis-500','bg-mountain-500'];
                $avatarColor = $avatarColors[$player->id % count($avatarColors)];
                $mpPct = min(100, ($player->mp / 12) * 100);
                $spPct = min(100, ($player->sp / 12) * 100);
                $ttPct = min(100, ($player->tt / 10) * 100);
            @endphp
            <div class="relative p-3 rounded-xl border {{ $room->current_turn_player_id === $player->id ? 'border-trust-500 bg-mountain-800' : 'border-mountain-800 bg-mountain-900/50' }}">
                @if($room->current_turn_player_id === $player->id)
                    <div class="absolute -top-1.5 -right-1.5 w-3 h-3 bg-trust-400 rounded-full animate-pulse"></div>
                @endif
                <div class="flex items-center gap-2 mb-2">
                    <div class="w-6 h-6 rounded-full {{ $avatarColor }} flex items-center justify-center text-[10px] font-bold text-white flex-shrink-0">{{ strtoupper(substr($player->user->name,0,1)) }}</div>
                    <div class="text-xs font-semibold {{ $player->user_id === auth()->id() ? 'text-trust-300' : 'text-mountain-300' }} truncate">
                        {{ $player->user->name }}{{ $player->user_id === auth()->id() ? ' (kamu)' : '' }}
                    </div>
                </div>
                <div class="space-y-1">
                    <div class="flex items-center gap-1.5"><span class="text-[10px] text-mountain-500 w-4">MP</span><div class="flex-1 h-1.5 bg-mountain-800 rounded-full overflow-hidden"><div class="h-full bg-trust-400 rounded-full" style="width:{{ $mpPct }}%"></div></div><span class="text-[10px] text-mountain-400 font-mono w-4 text-right">{{ $player->mp }}</span></div>
                    <div class="flex items-center gap-1.5"><span class="text-[10px] text-mountain-500 w-4">SP</span><div class="flex-1 h-1.5 bg-mountain-800 rounded-full overflow-hidden"><div class="h-full bg-trust-400 rounded-full" style="width:{{ $spPct }}%"></div></div><span class="text-[10px] text-mountain-400 font-mono w-4 text-right">{{ $player->sp }}</span></div>
                    <div class="flex items-center gap-1.5"><span class="text-[10px] text-mountain-500 w-4">TT</span><div class="flex-1 h-1.5 bg-mountain-800 rounded-full overflow-hidden"><div class="h-full bg-trust-400 rounded-full" style="width:{{ $ttPct }}%"></div></div><span class="text-[10px] text-mountain-400 font-mono w-4 text-right">{{ $player->tt }}</span></div>
                </div>
                <div class="flex items-center justify-between mt-2 pt-1.5 border-t border-mountain-800">
                    <span class="text-[10px] {{ ($player->reputation ?? 0) >= 0 ? 'text-camp-400' : 'text-crisis-400' }}">Rep {{ $player->reputation ?? 0 }} · Res {{ $player->resources ?? 0 }}</span>
                    @if(($player->promises_kept ?? 0) > 0 || ($player->promises_broken ?? 0) > 0)
                        <span class="text-[10px]">
                            @if(($player->promises_kept ?? 0) > 0)<span class="text-camp-400">✓{{$player->promises_kept}}</span>@endif
                            @if(($player->promises_broken ?? 0) > 0)<span class="text-crisis-400 ml-1">✗{{$player->promises_broken}}</span>@endif
                        </span>
                    @endif
                </div>
                <div class="text-[10px] text-mountain-500 mt-1">{{ ucfirst($player->current_level) }}</div>
            </div>
            @endforeach
        </div>

        <!-- My progress bar -->
        <div class="mb-6">
            <x-progress-bar
                :level="$myPlayer->current_level"
                :mp="$myPlayer->mp"
                :sp="$myPlayer->sp"
                :tt="$myPlayer->tt"
                :showThresholds="true"
                :playerName="$myPlayer->user->name . ' (kamu)'" />
        </div>

        <!-- V2: Active Consequences Panel -->
        @if(!empty($activeConsequences))
            <div class="mb-4 p-3 rounded-xl bg-trust-900/30 border border-trust-700/50">
                <h3 class="text-xs font-semibold text-trust-300 mb-2 flex items-center gap-1">
                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                    Konsekuensi Aktif
                </h3>
                <div class="space-y-1.5 max-h-32 overflow-y-auto">
                    @foreach($activeConsequences as $cons)
                    <div class="text-xs text-trust-200 flex items-start gap-1">
                        <span class="text-crisis-400 flex-shrink-0">⏳</span>
                        <span>{{ $cons['description'] }} <span class="text-trust-400">({{ $cons['stat'] }}{{ $cons['delta'] >= 0 ? '+' : '' }}{{ $cons['delta'] }})</span></span>
                    </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- V2: Active Promises -->
        @if(!empty($activePromises))
            <div class="mb-4 p-3 rounded-xl bg-trust-900/20 border border-trust-700/30">
                <h3 class="text-xs font-semibold text-trust-300 mb-2">Janji Aktif</h3>
                <div class="space-y-1.5">
                    @foreach($activePromises as $promise)
                    <div class="text-xs text-trust-200">
                        <span class="font-semibold">{{ $promise['promiser']['user']['name'] }}</span>
                        → <span class="font-semibold">{{ $promise['recipient']['user']['name'] }}</span>:
                        {{ $promise['description'] }}
                    </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- V2: Active Votes -->
        @if(!empty($activeVotes))
            <div class="mb-4 p-3 rounded-xl bg-trust-900/20 border border-trust-700/30">
                @foreach($activeVotes as $vote)
                <h3 class="text-xs font-semibold text-trust-300 mb-2">🗳️ {{ $vote['vote_topic'] }}</h3>
                <p class="text-xs text-mountain-300 mb-2">{{ $vote['vote_description'] }}</p>
                <div class="flex gap-2 flex-wrap">
                    @foreach(($vote['options'] ?? []) as $option)
                    <button wire:click="castVoteOnActive({{ $vote['id'] }}, '{{ $option }}')"
                            class="px-3 py-1 rounded-lg text-xs border {{ $voteChoice === $option ? 'border-trust-400 bg-trust-500 text-mountain-950' : 'border-mountain-600 text-mountain-300 hover:border-trust-400' }}">
                        {{ $option }}
                    </button>
                    @endforeach
                </div>
                @endforeach
            </div>
        @endif

        <!-- Status message -->
        @if($message)
            <div class="mb-4 p-3 rounded-xl bg-trust-900/30 border border-trust-500/30 text-sm text-center">
                {{ $message }}
            </div>
        @endif

        <!-- Waiting for turn -->
        @if(!$isMyTurn && $room->status->value !== 'finished')
            <div class="text-center py-8 text-mountain-500">
                <div class="text-sm mb-1">Bukan giliranmu.</div>
                <div class="text-xs text-mountain-600">
                    @if($room->currentPlayer)
                        Giliran <span class="text-mountain-400 font-semibold">{{ $room->currentPlayer->user->name }}</span>
                    @endif
                </div>
                <button wire:click="refreshBoard" class="mt-3 text-xs text-trust-400 hover:underline">Refresh</button>
            </div>
        @endif

        <!-- Draw card button -->
        @if($isMyTurn && !$showCard && !$showEffects)
            <div class="text-center py-6">
                <p class="text-mountain-200 mb-4 font-semibold">Giliranmu!</p>
                <button wire:click="drawCard"
                        class="px-8 py-3 rounded-xl bg-trust-500 text-mountain-950 font-bold text-lg hover:bg-trust-400 animate-pulse-gold">
                    Ambil Expedition Card
                </button>
                <p class="text-xs text-mountain-500 mt-2">
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
            <div class="text-center mt-4">
                <button wire:click="refreshBoard"
                        class="px-6 py-2 rounded-xl border border-mountain-600 text-mountain-300 text-sm">
                    Lanjut
                </button>
            </div>
        @endif

        <!-- Rope Bridge check modal -->
        @if($showRopeBridge)
            <x-rope-bridge-check
                :player="$myPlayer"
                :thresholdKey="$myPlayer->current_level === 'basecamp' ? 'to_camp' : 'to_summit'" />
        @endif

        <!-- V2: Promise Modal -->
        @if($showPromiseModal)
            <div class="fixed inset-0 bg-black/60 flex items-center justify-center z-50 p-4">
                <div class="bg-mountain-900 border border-mountain-600 rounded-2xl p-6 max-w-md w-full shadow-2xl">
                    <h3 class="font-expedition text-lg font-bold text-mountain-100 mb-4">Buat Janji</h3>
                    <p class="text-xs text-mountain-400 mb-4">Janji tidak diwajibkan sistem. Kamu bebas menepati atau melanggarnya — tapi ada konsekuensi reputasi.</p>

                    <div class="space-y-3">
                        <div>
                            <label class="text-xs text-mountain-300 block mb-1">Tipe Janji</label>
                            <select wire:model="promiseType" class="w-full rounded-lg bg-mountain-800 border border-mountain-600 text-mountain-200 text-sm p-2">
                                <option value="">Pilih...</option>
                                <option value="vote_for">Dukungan Suara</option>
                                <option value="help_rescue">Menolong</option>
                                <option value="share_resource">Berbagi Sumber Daya</option>
                                <option value="support_bridge">Dukungan Rope Bridge</option>
                                <option value="protect_trust">Melindungi Trust</option>
                            </select>
                        </div>

                        <div>
                            <label class="text-xs text-mountain-300 block mb-1">Untuk Pemain</label>
                            <select wire:model="promiseRecipientId" class="w-full rounded-lg bg-mountain-800 border border-mountain-600 text-mountain-200 text-sm p-2">
                                <option value="">Pilih...</option>
                                @foreach($otherPlayers as $op)
                                <option value="{{ $op->id }}">{{ $op->user->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="text-xs text-mountain-300 block mb-1">Deskripsi</label>
                            <input wire:model="promiseDescription" type="text"
                                   class="w-full rounded-lg bg-mountain-800 border border-mountain-600 text-mountain-200 text-sm p-2"
                                   placeholder="Contoh: Akan mendukungmu di voting berikutnya">
                        </div>
                    </div>

                    <div class="flex gap-3 mt-6">
                        <button wire:click="submitPromise" class="flex-1 px-4 py-2 rounded-xl bg-trust-500 text-mountain-950 font-semibold text-sm hover:bg-trust-400">
                            Buat Janji
                        </button>
                        <button wire:click="hidePromiseForm" class="px-4 py-2 rounded-xl border border-mountain-600 text-mountain-300 text-sm">
                            Batal
                        </button>
                    </div>
                </div>
            </div>
        @endif

        <!-- Turn log -->
        <div class="mt-8">
            <h3 class="font-expedition text-sm font-semibold text-mountain-300 mb-3">Log Ekspedisi</h3>
            <div class="space-y-2 max-h-64 overflow-y-auto">
                @foreach($allTurns as $turn)
                <div class="p-3 rounded-lg bg-mountain-900/50 border border-mountain-800 text-xs">
                    <div class="flex items-center gap-2 mb-1">
                        <span class="font-semibold text-mountain-200">{{ $turn->player->user->name }}</span>
                        <span class="text-mountain-500">pilih</span>
                        <span class="font-bold text-trust-300">{{ $turn->chosen_option }}</span>
                        @if($turn->was_hidden)
                            <span class="px-1.5 py-0.5 rounded text-trust-300 bg-trust-900 text-[10px]">HIDDEN</span>
                        @endif
                    </div>
                    <div class="flex gap-2 text-mountain-400 flex-wrap">
                        <span>MP{{ $turn->mp_effect >= 0 ? '+' : '' }}{{ $turn->mp_effect }}</span>
                        <span>SP{{ $turn->sp_effect >= 0 ? '+' : '' }}{{ $turn->sp_effect }}</span>
                        <span>TT{{ $turn->tt_effect >= 0 ? '+' : '' }}{{ $turn->tt_effect }}</span>
                        @if($turn->reputation_effect)
                            <span class="{{ $turn->reputation_effect >= 0 ? 'text-camp-300' : 'text-crisis-400' }}">R{{ $turn->reputation_effect >= 0 ? '+' : '' }}{{ $turn->reputation_effect }}</span>
                        @endif
                        @if($turn->risk_die_result)
                            <span class="text-mountain-500">| Die:{{ $turn->risk_die_result }}</span>
                        @endif
                        @if($turn->dysfunction_triggered)
                            <span class="text-crisis-400">| Dysfunction!</span>
                        @endif
                        @if($turn->cross_player_effects && count($turn->cross_player_effects) > 0)
                            <span class="text-camp-300">| Tim Effect</span>
                        @endif
                    </div>
                    @if($turn->hidden_info_shown)
                        <div class="mt-1 text-trust-400 italic text-[11px]">{{ $turn->hidden_info_shown }}</div>
                    @endif
                </div>
                @endforeach
                @if($allTurns->isEmpty())
                    <p class="text-mountain-600 text-xs text-center py-4">Belum ada giliran.</p>
                @endif
            </div>
        </div>
    </div>
</div>
