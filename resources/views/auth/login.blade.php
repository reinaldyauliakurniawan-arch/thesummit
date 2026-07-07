@extends('layouts.app')
<div class="max-w-sm mx-auto px-4 py-12"><div class="text-center mb-8"><svg class="w-12 h-12 mx-auto text-trust-400 mb-3" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2L2 22h20L12 2z"/></svg><h1 class="text-2xl font-bold font-expedition">Kembali ke Pendakian</h1></div>
<form method="POST" action="{{ route('login') }}" class="space-y-4">@csrf
<div><label class="block text-sm text-mountain-300 mb-1">Email</label><input type="email" name="email" value="{{ old('email') }}" required class="w-full px-4 py-2.5 rounded-xl bg-mountain-800 border border-mountain-700 text-mountain-100 focus:border-trust-400 focus:ring-1 focus:ring-trust-400 outline-none text-sm">@error('email')<p class="text-crisis-400 text-xs mt-1">{{ $message }}</p>@enderror</div>
<div><label class="block text-sm text-mountain-300 mb-1">Password</label><input type="password" name="password" required class="w-full px-4 py-2.5 rounded-xl bg-mountain-800 border border-mountain-700 text-mountain-100 focus:border-trust-400 focus:ring-1 focus:ring-trust-400 outline-none text-sm"></div>
<button type="submit" class="w-full py-2.5 rounded-xl bg-trust-500 text-mountain-950 font-bold hover:bg-trust-400">Login</button></form>
<p class="text-center text-sm text-mountain-400 mt-6">Belum punya akun? <a href="{{ route('register') }}" class="text-trust-400 hover:underline">Daftar</a></p></div>
