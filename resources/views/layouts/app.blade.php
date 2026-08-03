<!DOCTYPE html>
<html lang="id"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0"><meta name="csrf-token" content="{{ csrf_token() }}"><title>{{ $title ?? 'The Summit' }}</title><link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin><link href="https://fonts.googleapis.com/css2?family=Fjalla+One&family=Spectral:ital,wght@0,400;0,500;0,600;1,400&family=JetBrains+Mono:wght@400;500;700&display=swap" rel="stylesheet">@vite(['resources/css/app.css','resources/js/app.js'])<style>[x-cloak]{display:none!important}</style></head>
<body class="bg-[#15130f] text-[#e8dfc8] min-h-screen font-field antialiased">
<nav class="bg-[#1c1810]/90 backdrop-blur border-b border-[#4a3a1b] sticky top-0 z-40">
<div class="max-w-4xl mx-auto px-4 h-14 flex items-center justify-between">
<a href="{{ route('dashboard') }}" class="flex items-center gap-2 text-[#d6a94e] font-bold font-expedition text-lg tracking-wide">
<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2L2 22h20L12 2z"/></svg>THE SUMMIT</a>
<div class="flex items-center gap-3 font-instrument text-[11px] uppercase tracking-wider">
@auth
<a href="{{ route('dashboard') }}" class="text-[#a89c7d] hover:text-[#e8dfc8]">Dashboard</a>
<form method="POST" action="{{ route('logout') }}" class="inline">@csrf<button class="text-[#a89c7d] hover:text-[#e6603a]">Logout</button></form>
@endauth
@guest
<a href="{{ route('login') }}" class="text-[#a89c7d]">Login</a>
<a href="{{ route('register') }}" class="pill-notch pill-brass">Daftar</a>
@endguest
</div></div></nav>
<main>{{ $slot }}</main>

@auth
<livewire:onboarding />
@endauth

@livewireStyles @livewireScripts</body></html>
