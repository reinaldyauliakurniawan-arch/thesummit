@props(['card'=>null,'showEffects'=>false,'effects'=>[],'riskDieResult'=>null,'dysfunction'=>null,'choosing'=>false,'wasHidden'=>false,'hiddenInfo'=>null,'createdConsequences'=>[],'crossPlayerEffects'=>[]])
@if($card)
<div class="max-w-lg mx-auto relative">
    <div class="absolute -top-3 -right-2 w-14 h-14 z-20">
        <x-expedition-stamp :level="$card->level" />
    </div>
    <div class="flex items-center gap-2 mb-3">
        <span class="pill-notch pill-brass">{{ ucfirst($card->level) }}</span>
        <span class="pill-notch" style="color:#a89c7d;border-color:#4a3a1b;background:rgba(255,255,255,.02);">{{ ucfirst($card->kategori) }}</span>
        @if($card->tipe==='krisis')<span class="pill-notch pill-ember" title="Kartu Krisis: setelah kamu memilih, sistem melempar Risk Die — bisa memicu dysfunction (TT -2, berdampak ke semua pemain) atau bonus (TT +1).">Krisis</span>@endif
        @if($card->has_hidden_info)<span class="pill-notch" style="color:#a89c7d;border-color:#4a3a1b;border-style:dashed;">? Tersembunyi</span>@endif
    </div>
    <div class="card-frame {{ $choosing?'animate-card-flip':'animate-fade-in' }}">
        <div class="card-frame-inner p-6">
            <div class="grain-overlay"></div>
            <x-compass-watermark />
            <div class="relative z-10">
                <x-card-illustration :kategori="$card->kategori" :tipe="$card->tipe" />
                <div class="mb-6">
                    <h4 class="font-instrument text-[10px] uppercase tracking-[.18em] text-[#8a6a30] mb-2">Situasi Ekspedisi</h4>
                    <p class="font-field text-[#e8dfc8] leading-relaxed text-[15px]">{{ $card->teks_situasi }}</p>
                </div>
                @if($choosing)
                <p class="text-center text-[#a89c7d] text-[10px] italic mb-3 font-instrument">Konsekuensi akan terungkap setelah kamu memilih.</p>
                <div class="space-y-3">
                    <button wire:click="chooseOption('A')" class="opt-tablet">
                        <span class="tag-notch absolute left-3.5 top-1/2 -translate-y-1/2 w-[26px] h-[26px]">A</span>
                        {{ $card->opsi_a_teks }}
                    </button>
                    <button wire:click="chooseOption('B')" class="opt-tablet">
                        <span class="tag-notch absolute left-3.5 top-1/2 -translate-y-1/2 w-[26px] h-[26px]">B</span>
                        {{ $card->opsi_b_teks }}
                    </button>
                </div>
                @endif
            </div>
        </div>
    </div>
    @if($card->tipe==='krisis' && $choosing)
    <div class="text-center text-[#e6603a] text-[10px] mt-3 animate-pulse font-instrument leading-relaxed px-4">
        <p class="font-bold uppercase tracking-wider">⚠ Kartu Krisis</p>
        <p class="text-[#cdc2a0] normal-case tracking-normal mt-0.5">Apapun pilihanmu, akan ada kemungkinan konsekuensi negatif terhadap Trust Token timmu — ini mensimulasikan kenyataan bahwa keputusan pemimpin tidak selalu bisa dikendalikan hasilnya, sebaik apapun niatnya.</p>
    </div>
    @endif
</div>
@endif

@if($showEffects && !empty($effects))
<div class="max-w-lg mx-auto animate-slide-up">
    <div class="card-frame">
        <div class="card-frame-inner p-6 text-center">
            <div class="grain-overlay" style="opacity:.25;"></div>
            <div class="relative z-10">
                <h4 class="font-instrument text-[10px] uppercase tracking-[.18em] text-[#8a6a30] mb-4">Efek Diterapkan</h4>
                <div class="flex justify-center gap-5 mb-2 flex-wrap">
                    <x-stat-gauge :value="$effects['mp']" :max="4" label="MP" title="Mindset Points — cara berpikir & adaptasi jangka panjang" />
                    <x-stat-gauge :value="$effects['sp']" :max="4" label="SP" title="Skillset Points — kemampuan teknis & eksekusi" />
                    <x-stat-gauge :value="$effects['tt']" :max="4" label="TT" title="Trust Tokens — modal kepercayaan antar rekan tim" />
                </div>
                @isset($effects['reputation'])
                <div class="flex justify-center gap-5 mb-2 mt-2">
                    <x-stat-gauge :value="$effects['reputation']??0" :max="4" label="Rep" title="Reputasi — bagaimana orang lain menilaimu secara publik" />
                    <x-stat-gauge :value="$effects['resources']??0" :max="4" label="Res" title="Resources — aset material/operasional yang kamu kendalikan" />
                </div>
                @endisset

                @if($riskDieResult !== null)
                <div class="border-t border-[#4a3a1b] pt-3 mt-3 font-instrument">
                    <div class="text-sm text-[#cdc2a0] mb-1">Risk Die: <span class="font-bold text-lg text-[#e8dfc8]">{{ $riskDieResult }}</span></div>
                    @if($riskDieResult<=2)<div class="text-[#e6603a] text-xs font-semibold animate-pulse">Dysfunction: {{ config("summit.dysfunctions.$dysfunction",$dysfunction) }} (TT -2) | Semua pemain terdampak!</div>
                    @elseif($riskDieResult>=5)<div class="text-[#7fae6c] text-xs font-semibold">Bonus! TT +1</div>
                    @else<div class="text-[#a89c7d] text-xs">Netral</div>@endif
                </div>
                @endif

                @if($wasHidden && $hiddenInfo)
                <div class="border-t border-[#6b5325] pt-3 mt-3 animate-fade-in font-instrument">
                    <div class="text-[#d6a94e] text-xs font-semibold mb-1">Informasi Tersembunyi Terungkap:</div>
                    <div class="text-[#cdc2a0] text-xs italic font-field">{{ $hiddenInfo }}</div>
                </div>
                @endif

                @if(!empty($createdConsequences))
                <div class="border-t border-[#6b5325] pt-3 mt-3 font-instrument text-left">
                    <div class="text-[#d6a94e] text-xs font-semibold mb-1 text-center">Konsekuensi Baru Dibuat:</div>
                    @foreach($createdConsequences as $cons)
                    <div class="text-xs text-[#cdc2a0] mt-1">
                        @if($cons['is_hidden'])
                        <span class="text-[#d6a94e]">???</span> — <span class="text-[#a89c7d]">efek tersembunyi</span>
                        @else
                        ⏳ {{ $cons['description'] }} ({{ $cons['stat'] }}{{ $cons['delta']>=0?'+':'' }}{{ $cons['delta'] }})
                        @endif
                    </div>
                    @endforeach
                </div>
                @endif

                @if(!empty($crossPlayerEffects))
                <div class="border-t border-[#3d5a33] pt-3 mt-3 font-instrument text-left">
                    <div class="text-[#7fae6c] text-xs font-semibold mb-1 text-center">Efek ke Tim:</div>
                    @foreach($crossPlayerEffects as $cpe)
                    <div class="text-xs text-[#a8c79a] mt-1">
                        {{ $cpe['target'] }}: {{ $cpe['stat'] }}{{ $cpe['delta']>=0?'+':'' }}{{ $cpe['delta'] }} — {{ $cpe['description'] }}
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endif
