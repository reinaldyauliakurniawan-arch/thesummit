<div>
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
            <button wire:click="refreshBoard" class="text-xs text-mountain-400 px-3 py-1 rounded-lg border border-mountain-700">
                Refresh
            </button>
        </div>

        <!-- Player grid -->
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 mb-6">
            @foreach($players as $player)
            <div class="relative p-2 rounded-xl border {{ $room->current_turn_player_id === $player->id ? 'border-trust-500 bg-mountain-800' : 'border-mountain-800 bg-mountain-900/50' }}">
                @if($room->current_turn_player_id === $player->id)
                    <div class="absolute -top-1.5 -right-1.5 w-3 h-3 bg-trust-400 rounded-full animate-pulse"></div>
                @endif
                <div class="text-xs font-semibold {{ $player->user_id === auth()->id() ? 'text-trust-300' : 'text-mountain-300' }} truncate">
                    {{ $player->user->name }}{{ $player->user_id === auth()->id() ? ' (kamu)' : '' }}
                </div>
                <div class="flex gap-1 mt-1">
                    <span class="text-xs text-basecamp-300">M{{ $player->mp }}</span>
                    <span class="text-xs text-camp-300">S{{ $player->sp }}</span>
                    <span class="text-xs text-trust-300">T{{ $player->tt }}</span>
                </div>
                <div class="text-xs text-mountain-500 mt-0.5">{{ ucfirst($player->current_level) }}</div>
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

        <!-- Effects display -->
        @if($showEffects && !empty($lastEffects))
            <x-expedition-card
                :showEffects="true"
                :effects="$lastEffects"
                :riskDieResult="$riskDieResult"
                :dysfunction="$dysfunctionTriggered" />
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

        <!-- Turn log -->
        <div class="mt-8">
            <h3 class="text-sm font-semibold text-mountain-300 mb-3">Log Ekspedisi</h3>
            <div class="space-y-2 max-h-64 overflow-y-auto">
                @foreach($allTurns as $turn)
                <div class="p-3 rounded-lg bg-mountain-900/50 border border-mountain-800 text-xs">
                    <div class="flex items-center gap-2 mb-1">
                        <span class="font-semibold text-mountain-200">{{ $turn->player->user->name }}</span>
                        <span class="text-mountain-500">pilih</span>
                        <span class="font-bold text-trust-300">{{ $turn->chosen_option }}</span>
                    </div>
                    <div class="flex gap-2 text-mountain-400">
                        <span>MP{{ $turn->mp_effect >= 0 ? '+' : '' }}{{ $turn->mp_effect }}</span>
                        <span>SP{{ $turn->sp_effect >= 0 ? '+' : '' }}{{ $turn->sp_effect }}</span>
                        <span>TT{{ $turn->tt_effect >= 0 ? '+' : '' }}{{ $turn->tt_effect }}</span>
                        @if($turn->risk_die_result)
                            <span class="text-mountain-500">| Die:{{ $turn->risk_die_result }}</span>
                        @endif
                        @if($turn->dysfunction_triggered)
                            <span class="text-crisis-400">| Dysfunction!</span>
                        @endif
                    </div>
                </div>
                @endforeach
                @if($allTurns->isEmpty())
                    <p class="text-mountain-600 text-xs text-center py-4">Belum ada giliran.</p>
                @endif
            </div>
        </div>
    </div>
</div>