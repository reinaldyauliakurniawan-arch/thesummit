<!DOCTYPE html>
<html lang="id"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0"><meta name="csrf-token" content="{{ csrf_token() }}"><title>{{ $title ?? 'The Summit' }}</title>@vite(['resources/css/app.css','resources/js/app.js'])<style>[x-cloak]{display:none!important}</style></head>
<body class="bg-mountain-950 text-mountain-100 min-h-screen font-sans antialiased">
<nav class="bg-mountain-900/80 backdrop-blur border-b border-mountain-800 sticky top-0 z-40">
<div class="max-w-4xl mx-auto px-4 h-14 flex items-center justify-between">
<a href="{{ route('dashboard') }}" class="flex items-center gap-2 text-trust-400 font-bold font-expedition text-lg">
<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2L2 22h20L12 2z"/></svg>The Summit</a>
<div class="flex items-center gap-3">
@auth
<a href="{{ route('dashboard') }}" class="text-sm text-mountain-300 hover:text-mountain-100">Dashboard</a>
<a href="{{ route('rooms.index') }}" class="text-sm text-mountain-300 hover:text-mountain-100">Rooms</a>
<form method="POST" action="{{ route('logout') }}" class="inline">@csrf<button class="text-sm text-mountain-400 hover:text-crisis-400">Logout</button></form>
@endauth
@guest
<a href="{{ route('login') }}" class="text-sm text-mountain-300">Login</a>
<a href="{{ route('register') }}" class="text-sm px-3 py-1 rounded-lg bg-trust-500 text-mountain-950 font-semibold hover:bg-trust-400">Daftar</a>
@endguest
</div></div></nav>
<main>{{ $slot }}</main>
@livewireStyles @livewireScripts</body></html>
