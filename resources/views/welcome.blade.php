@extends('layouts.app')
@section('title','The Summit')
<div class="max-w-2xl mx-auto px-4 py-16 text-center">
<div class="mb-8"><svg class="w-20 h-20 mx-auto text-trust-400 mb-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2L2 22h20L12 2z"/></svg>
<h1 class="text-4xl md:text-5xl font-bold font-expedition text-mountain-100 mb-3">The Summit</h1>
<p class="text-mountain-400 text-lg max-w-md mx-auto">Naiki gunung leadership bersama timmu. Leading Self, Leading Others, Leading Leaders.</p>
<p class="text-trust-400 text-sm mt-2 italic font-expedition">"The real winner is the one who makes everybody win."</p></div>
<div class="flex flex-col sm:flex-row items-center justify-center gap-3">
@auth <a href="{{ route('dashboard') }}" class="w-full sm:w-auto px-8 py-3 rounded-xl bg-trust-500 text-mountain-950 font-bold text-lg hover:bg-trust-400">Ke Dashboard</a>
@endauth
@guest
<a href="{{ route('login') }}" class="w-full sm:w-auto px-8 py-3 rounded-xl bg-trust-500 text-mountain-950 font-bold text-lg hover:bg-trust-400">Mulai Pendakian</a>
<a href="{{ route('register') }}" class="w-full sm:w-auto px-8 py-3 rounded-xl border border-mountain-600 text-mountain-200 hover:bg-mountain-800">Buat Akun</a>
@endguest</div>
<div class="mt-16 grid grid-cols-1 sm:grid-cols-3 gap-4 text-left">
<div class="p-4 rounded-xl bg-mountain-900/50 border border-mountain-800"><div class="w-10 h-10 rounded-lg bg-basecamp-500 flex items-center justify-center text-white font-bold mb-2">B</div><h3 class="font-semibold text-mountain-200 text-sm">Basecamp</h3><p class="text-xs text-mountain-400 mt-1">Leading Self — bangun fondasi mindset dan skillset.</p></div>
<div class="p-4 rounded-xl bg-mountain-900/50 border border-mountain-800"><div class="w-10 h-10 rounded-lg bg-camp-500 flex items-center justify-center text-white font-bold mb-2">C</div><h3 class="font-semibold text-mountain-200 text-sm">Camp</h3><p class="text-xs text-mountain-400 mt-1">Leading Others — latih kemampuan memimpin dan membangun tim.</p></div>
<div class="p-4 rounded-xl bg-mountain-900/50 border border-mountain-800"><div class="w-10 h-10 rounded-lg bg-summit-500 flex items-center justify-center text-summit-950 font-bold mb-2">S</div><h3 class="font-semibold text-mountain-200 text-sm">Summit</h3><p class="text-xs text-mountain-400 mt-1">Leading Leaders — pimpin para pemimpin, raih puncak bersama.</p></div>
</div></div>
