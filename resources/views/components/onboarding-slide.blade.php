@props(['currentSlide' => 0])

<div x-data="{
    slide: 0,
    total: 5,
    slides: [
        {
            icon: 'mountain',
            title: 'Selamat Datang di The Summit!',
            body: 'Sebuah board game simulasi leadership pipeline berbasis Lencioni\\\'s 5 Dysfunctions. Naiki 3 level — Basecamp, Camp, hingga Summit — bersama timmu.'
        },
        {
            icon: 'compass',
            title: '3 Sumber Daya Utama',
            body: 'MP (Mindset Point) = kepemimpinan diri. SP (Skillset Point) = kemampuan memimpin orang lain. TT (Trust Token) = kepercayaan tim. Kelola ketiganya dengan bijak!'
        },
        {
            icon: 'cards',
            title: 'Expedition Cards & Risiko',
            body: 'Setiap giliran kamu ambil kartu dan pilih Opsi A atau B. Kartu Krisis memicu Risk Die: 1-2 = Dysfunction (TT -2), 3-4 = Netral, 5-6 = Bonus (TT +1).'
        },
        {
            icon: 'bridge',
            title: 'Rope Bridge & Level Naik',
            body: 'Kumpulkan MP + SP (dan TT untuk level tertentu) untuk melewati Rope Bridge ke level berikutnya. Rope Bridge bisa dilewati kapan saja saat threshold terpenuhi.'
        },
        {
            icon: 'badge',
            title: 'Badge & Scoring',
            body: 'Skor = (Level x 10) + TT akhir. Raih badge \"The Carrier\" (Summit + TT>=8) atau \"Solo Peak\" (Summit + TT<8). Main asinkron — 24 jam per giliran!'
        }
    ],
    next() {
        if (this.slide < this.total - 1) this.slide++;
    },
    prev() {
        if (this.slide > 0) this.slide--;
    },
    isLast() {
        return this.slide === this.total - 1;
    },
    dismiss() {
        @this.dismiss();
    }
}" x-show="true" x-cloak
   class="fixed inset-0 bg-black/70 z-50 flex items-center justify-center p-4"
   @keydown.escape.window="dismiss()">

    <div class="bg-mountain-800 rounded-2xl border border-mountain-600 max-w-lg w-full shadow-2xl overflow-hidden animate-slide-up"
         @click.outside="dismiss()">

        <!-- Slide indicator dots -->
        <div class="flex justify-center gap-1.5 pt-4">
            <template x-for="(s, i) in slides" :key="i">
                <div class="h-2 rounded-full transition-all duration-300"
                     :class="i === slide ? 'bg-trust-400 w-6' : 'bg-mountain-600 w-2'">
                </div>
            </template>
        </div>

        <!-- Slide content -->
        <div class="p-6 text-center">
            <!-- Icon -->
            <div class="mx-auto w-16 h-16 rounded-2xl bg-mountain-700 flex items-center justify-center mb-4">
                <!-- Mountain icon -->
                <svg x-show="slides[slide].icon === 'mountain'" class="w-8 h-8 text-trust-400" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 2L2 22h20L12 2z"/>
                </svg>
                <!-- Compass icon -->
                <svg x-show="slides[slide].icon === 'compass'" class="w-8 h-8 text-basecamp-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="10"/><path d="M16.24 7.76l-2.12 6.36-6.36 2.12 2.12-6.36 6.36-2.12z"/>
                </svg>
                <!-- Cards icon -->
                <svg x-show="slides[slide].icon === 'cards'" class="w-8 h-8 text-camp-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/>
                </svg>
                <!-- Bridge icon -->
                <svg x-show="slides[slide].icon === 'bridge'" class="w-8 h-8 text-trust-300" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z"/><line x1="4" y1="22" x2="4" y2="15"/><line x1="20" y1="22" x2="20" y2="15"/>
                </svg>
                <!-- Badge icon -->
                <svg x-show="slides[slide].icon === 'badge'" class="w-8 h-8 text-summit-400" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                </svg>
            </div>

            <!-- Title -->
            <h3 class="text-xl font-bold text-mountain-100 mb-3 font-expedition"
                x-text="slides[slide].title"></h3>

            <!-- Body -->
            <p class="text-mountain-300 text-sm leading-relaxed"
               x-text="slides[slide].body"></p>
        </div>

        <!-- Navigation -->
        <div class="px-6 pb-6 flex items-center justify-between">
            <button x-show="slide > 0"
                    x-cloak
                    @click="prev()"
                    class="text-sm text-mountain-400 hover:text-mountain-200 transition-colors">
                &larr; Kembali
            </button>

            <div x-show="slide === 0" class="flex-1"></div>

            <div class="flex gap-3">
                <button @click="dismiss()"
                        class="text-sm text-mountain-500 hover:text-mountain-300 transition-colors px-3 py-2">
                    Lewati
                </button>
                <button @click="isLast() ? dismiss() : next()"
                        class="px-5 py-2 rounded-xl bg-trust-500 text-mountain-950 font-bold text-sm hover:bg-trust-400 transition-colors"
                        x-text="isLast() ? 'Mulai Bermain!' : 'Lanjut'">
                </button>
            </div>
        </div>
    </div>
</div>