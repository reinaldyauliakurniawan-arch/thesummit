@component('layouts.app', ['title' => 'The Summit'])
<div class="max-w-2xl mx-auto px-4 py-16 text-center relative overflow-hidden">
    <div class="absolute right-[-60px] top-[-40px] w-[240px] h-[240px] pointer-events-none">
        <x-compass-watermark />
    </div>
    <div class="mb-8 relative">
        <svg class="w-20 h-20 mx-auto text-[#d6a94e] mb-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2L2 22h20L12 2z"/></svg>
        <h1 class="text-4xl md:text-5xl font-bold font-expedition text-[#e8dfc8] mb-3 tracking-wide">THE SUMMIT</h1>
        <p class="text-[#a89c7d] text-lg max-w-md mx-auto font-field">Naiki gunung leadership bersama timmu. Leading Self, Leading Others, Leading Leaders.</p>
        <p class="text-[#d6a94e] text-sm mt-2 italic font-expedition">"The real winner is the one who makes everybody win."</p>
    </div>

    <div class="flex flex-col sm:flex-row items-center justify-center gap-3 relative font-instrument text-sm uppercase tracking-wider">
        @auth
            <a href="{{ route('dashboard') }}" class="w-full sm:w-auto px-8 py-3 notch-md bg-[#d6a94e] text-[#15130f] font-bold text-lg hover:bg-[#e3c483]">Ke Dashboard</a>
        @endauth
        @guest
            <a href="{{ route('login') }}" class="w-full sm:w-auto px-8 py-3 notch-md bg-[#d6a94e] text-[#15130f] font-bold text-lg hover:bg-[#e3c483]">Mulai Pendakian</a>
            <a href="{{ route('register') }}" class="w-full sm:w-auto px-8 py-3 notch-md border border-[#4a3a1b] text-[#cdc2a0] hover:bg-[#1c1810]">Buat Akun</a>
        @endguest
    </div>

    <div class="mt-16 grid grid-cols-1 sm:grid-cols-3 gap-4 text-left relative">
        <div class="p-4 notch-sm bg-[#1c1810] border border-[#332b1c]">
            <div class="w-10 h-10 tag-notch mb-2">B</div>
            <h3 class="font-expedition font-semibold text-[#e8dfc8] text-sm tracking-wide">Basecamp</h3>
            <p class="text-xs text-[#a89c7d] mt-1 font-field">Leading Self — bangun fondasi mindset dan skillset.</p>
        </div>
        <div class="p-4 notch-sm bg-[#1c1810] border border-[#332b1c]">
            <div class="w-10 h-10 tag-notch mb-2" style="border-color:#7fae6c;color:#7fae6c;">C</div>
            <h3 class="font-expedition font-semibold text-[#e8dfc8] text-sm tracking-wide">Camp</h3>
            <p class="text-xs text-[#a89c7d] mt-1 font-field">Leading Others — latih kemampuan memimpin dan membangun tim.</p>
        </div>
        <div class="p-4 notch-sm bg-[#1c1810] border border-[#332b1c]">
            <div class="w-10 h-10 tag-notch mb-2" style="border-color:#8a97ab;color:#8a97ab;">S</div>
            <h3 class="font-expedition font-semibold text-[#e8dfc8] text-sm tracking-wide">Summit</h3>
            <p class="text-xs text-[#a89c7d] mt-1 font-field">Leading Leaders — pimpin para pemimpin, raih puncak bersama.</p>
        </div>
    </div>
</div>
@endcomponent