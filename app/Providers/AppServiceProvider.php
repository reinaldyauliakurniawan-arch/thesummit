<?php
namespace App\Providers;use Illuminate\Support\ServiceProvider;use Livewire\Livewire;
class AppServiceProvider extends ServiceProvider{
public function register():void{}
public function boot():void{Livewire::component('dashboard',\App\Livewire\Dashboard::class);Livewire::component('room-lobby',\App\Livewire\RoomLobby::class);Livewire::component('game-board',\App\Livewire\GameBoard::class);Livewire::component('game-summary',\App\Livewire\GameSummary::class);}
}
