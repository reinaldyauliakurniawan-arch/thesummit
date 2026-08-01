@component('layouts.app')
<div class="max-w-sm mx-auto px-4 py-12 relative">
    <div class="absolute right-[-40px] top-[-20px] w-[160px] h-[160px] opacity-[.05] pointer-events-none">
        <x-compass-watermark />
    </div>
    <div class="text-center mb-8 relative">
        <svg class="w-12 h-12 mx-auto text-[#d6a94e] mb-3" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2L2 22h20L12 2z"/></svg>
        <h1 class="text-2xl font-bold font-expedition text-[#e8dfc8] tracking-wide">Kembali ke Pendakian</h1>
    </div>
    <form method="POST" action="{{ route('login') }}" class="space-y-4 relative font-field">
        @csrf
        <div>
            <label class="block text-xs text-[#a89c7d] mb-1 font-instrument uppercase tracking-wider">Email</label>
            <input type="email" name="email" value="{{ old('email') }}" required class="w-full px-4 py-2.5 notch-sm bg-[#1c1810] border border-[#4a3a1b] text-[#e8dfc8] focus:border-[#d6a94e] focus:ring-1 focus:ring-[#d6a94e] outline-none text-sm">
            @error('email')
                <p class="text-[#e6603a] text-xs mt-1 font-instrument">{{ $message }}</p>
            @enderror
        </div>
        <div>
            <label class="block text-xs text-[#a89c7d] mb-1 font-instrument uppercase tracking-wider">Password</label>
            <input type="password" name="password" required class="w-full px-4 py-2.5 notch-sm bg-[#1c1810] border border-[#4a3a1b] text-[#e8dfc8] focus:border-[#d6a94e] focus:ring-1 focus:ring-[#d6a94e] outline-none text-sm">
        </div>
        <button type="submit" class="w-full py-2.5 notch-md bg-[#d6a94e] text-[#15130f] font-bold hover:bg-[#e3c483] font-instrument uppercase tracking-wider text-sm">Login</button>
    </form>
    <p class="text-center text-sm text-[#a89c7d] mt-6 font-field relative">Belum punya akun? <a href="{{ route('register') }}" class="text-[#d6a94e] hover:underline">Daftar</a></p>
</div>
@endcomponent