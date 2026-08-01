@props(['player' => null, 'thresholdKey' => null])

@if($player)
<div class="fixed inset-0 bg-black/70 z-50 flex items-center justify-center p-4" wire:click="skipRopeBridge" x-data="{ burst: false, particles: Array.from({length: 24}, (_,i) => ({ id:i, x:(Math.random()*200-100), rot:(Math.random()*360), delay:(Math.random()*0.15), color: ['bg-[#d6a94e]','bg-[#7fae6c]','bg-[#cdc2a0]'][i%3] })) }">
    <div class="fixed inset-0 pointer-events-none z-[60] overflow-hidden" x-show="burst" x-cloak>
        <template x-for="p in particles" :key="p.id">
            <div class="absolute left-1/2 top-1/3 w-2 h-2"
                 :class="p.color"
                 x-show="burst"
                 x-transition:enter="transition ease-out duration-700"
                 :style="`transition-delay:${p.delay}s; transform: translate(${p.x}px, ${burst?200:0}px) rotate(${p.rot}deg); opacity:${burst?0:1};`"></div>
        </template>
    </div>
    <div class="card-frame max-w-md w-full animate-slide-up" wire:click.stop>
        <div class="card-frame-inner p-6">
        <div class="grain-overlay" style="opacity:.3;"></div>
        <div class="relative z-10">
        <div class="text-center mb-4">
            <h3 class="text-xl font-bold text-[#e8dfc8] font-expedition tracking-wide">Rope Bridge Check</h3>
            <p class="text-[#a89c7d] text-sm mt-1 font-field">Mencoba naik ke level berikutnya</p>
        </div>

        @if(config("summit.thresholds.$thresholdKey"))
            @php $threshold = config("summit.thresholds.$thresholdKey"); @endphp
            <div class="grid {{ ($threshold['tt_required'] ?? true) ? 'grid-cols-3' : 'grid-cols-2' }} gap-3 mb-6 font-instrument">
                <div class="text-center p-3 notch-sm bg-[#1c1810] border border-[#332b1c]">
                    <div class="text-[10px] text-[#a89c7d] uppercase tracking-wider mb-1">MP</div>
                    <div class="text-lg font-bold {{ $player->mp >= $threshold['mp'] ? 'text-[#7fae6c]' : 'text-[#e6603a]' }}">
                        {{ $player->mp }}/{{ $threshold['mp'] }}
                    </div>
                </div>
                <div class="text-center p-3 notch-sm bg-[#1c1810] border border-[#332b1c]">
                    <div class="text-[10px] text-[#a89c7d] uppercase tracking-wider mb-1">SP</div>
                    <div class="text-lg font-bold {{ $player->sp >= $threshold['sp'] ? 'text-[#7fae6c]' : 'text-[#e6603a]' }}">
                        {{ $player->sp }}/{{ $threshold['sp'] }}
                    </div>
                </div>
                @if($threshold['tt_required'] ?? true)
                    <div class="text-center p-3 notch-sm bg-[#1c1810] border border-[#332b1c]">
                        <div class="text-[10px] text-[#a89c7d] uppercase tracking-wider mb-1">TT</div>
                        <div class="text-lg font-bold {{ $player->tt >= $threshold['tt'] ? 'text-[#7fae6c]' : 'text-[#e6603a]' }}">
                            {{ $player->tt }}/{{ $threshold['tt'] }}
                        </div>
                    </div>
                @endif
            </div>
        @endif

        <div class="flex gap-3">
            <button wire:click="skipRopeBridge" class="flex-1 px-4 py-2.5 notch-sm border border-[#4a3a1b] text-[#a89c7d] hover:text-[#e8dfc8] text-sm font-instrument uppercase tracking-wider">
                Lewati
            </button>
            <button x-on:click="burst = true" wire:click="attemptRopeBridge" class="flex-1 px-4 py-2.5 notch-sm bg-[#d6a94e] text-[#15130f] font-bold hover:bg-[#e3c483] text-sm font-instrument uppercase tracking-wider">
                Lintasi Rope Bridge
            </button>
        </div>
        </div>
        </div>
    </div>
</div>
@endif
