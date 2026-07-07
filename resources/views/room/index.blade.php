@extends('layouts.app')
<div class="max-w-4xl mx-auto px-4 pt-6 pb-8">
<div class="flex items-center justify-between mb-6"><h1 class="text-2xl font-bold font-expedition text-mountain-100">Daftar Room</h1>
<form method="POST" action="{{ route('rooms.store') }}">@csrf<button class="px-4 py-2 rounded-xl bg-trust-500 text-mountain-950 font-bold text-sm">+ Buat Room</button></form></div>
<div class="mb-6 p-4 rounded-xl bg-mountain-900/50 border border-mountain-800">
<form id="jf" method="POST" action="/rooms/__CODE__/join">@csrf
<label class="text-sm text-mountain-300 block mb-1">Gabung dengan kode:</label>
<div class="flex gap-2"><input type="text" id="jc" placeholder="Kode room" maxlength="6" class="flex-1 px-4 py-2 rounded-xl bg-mountain-800 border border-mountain-700 text-mountain-100 font-mono uppercase text-sm focus:border-trust-400 outline-none"
onkeydown="if(event.key==='Enter'){event.preventDefault();document.getElementById('jf').action='/rooms/join/'+document.getElementById('jc').value.toUpperCase();document.getElementById('jf').submit();}">
<button type="button" onclick="document.getElementById('jf').action='/rooms/join/'+document.getElementById('jc').value.toUpperCase();document.getElementById('jf').submit();" class="px-4 py-2 rounded-xl bg-mountain-700 text-mountain-200 text-sm">Gabung</button></div></form></div></div>
