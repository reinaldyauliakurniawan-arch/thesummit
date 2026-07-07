@extends('layouts.app')
<div class="max-w-6xl mx-auto px-4 pt-6 pb-8">
<div class="flex items-center justify-between mb-6"><h1 class="text-2xl font-bold font-expedition text-mountain-100">Expedition Cards</h1>
<a href="{{ route('admin.cards.create') }}" class="px-4 py-2 rounded-xl bg-trust-500 text-mountain-950 font-bold text-sm">+ Tambah</a></div>
@if(session('success'))<div class="mb-4 p-3 rounded-xl bg-camp-900/30 border border-camp-500/30 text-camp-300 text-sm">{{ session('success') }}</div>@endif
<div class="overflow-x-auto"><table class="w-full text-sm"><thead><tr class="text-left text-mountain-400 border-b border-mountain-800"><th class="pb-2 pr-4">#</th><th class="pb-2 pr-4">Level</th><th class="pb-2 pr-4">Kat</th><th class="pb-2 pr-4">Tipe</th><th class="pb-2 pr-4">Situasi</th><th class="pb-2">Aksi</th></tr></thead><tbody>
@foreach($cards as $c)<tr class="border-b border-mountain-800/50 hover:bg-mountain-900/30"><td class="py-2 pr-4 font-mono text-mountain-500">{{ $c->id }}</td>
<td class="py-2 pr-4"><span class="px-2 py-0.5 rounded text-xs {{ $c->level==='basecamp'?'bg-basecamp-900 text-basecamp-300':($c->level==='camp'?'bg-camp-900 text-camp-300':'bg-summit-900 text-summit-300') }}">{{ ucfirst($c->level) }}</span></td>
<td class="py-2 pr-4 text-mountain-300">{{ ucfirst($c->kategori) }}</td>
<td class="py-2 pr-4"><span class="px-2 py-0.5 rounded text-xs {{ $c->tipe==='krisis'?'bg-crisis-900 text-crisis-300':'bg-mountain-800 text-mountain-300' }}">{{ ucfirst($c->tipe) }}</span></td>
<td class="py-2 pr-4 text-mountain-400 max-w-xs truncate">{{ Str::limit($c->teks_situasi,50) }}</td>
<td class="py-2"><a href="{{ route('admin.cards.edit',$c) }}" class="text-trust-400 hover:underline mr-2">Edit</a>
<form method="POST" action="{{ route('admin.cards.delete',$c) }}" class="inline" onsubmit="return confirm('Hapus?')">@csrf @method('DELETE')<button class="text-crisis-400 hover:underline">Hapus</button></form></td></tr>
@endforeach</tbody></table></div></div>
