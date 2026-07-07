<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Dashboard;
use App\Livewire\RoomLobby;
use App\Livewire\GameBoard;
use App\Livewire\GameSummary;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\AdminController;

// Public
Route::get('/', fn () => view('welcome'))->name('home');

// Guest only
Route::middleware('guest')->group(function () {
    Route::get('login', [App\Http\Controllers\Auth\LoginController::class, 'showLoginForm'])->name('login');
    Route::post('login', [App\Http\Controllers\Auth\LoginController::class, 'login']);
    Route::get('register', [App\Http\Controllers\Auth\RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('register', [App\Http\Controllers\Auth\RegisterController::class, 'register']);
});

Route::post('logout', [App\Http\Controllers\Auth\LoginController::class, 'logout'])->name('logout');

// Authenticated
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', Dashboard::class)->name('dashboard');

    // Rooms
    Route::get('/rooms', [RoomController::class, 'index'])->name('rooms.index');
    Route::post('/rooms', [RoomController::class, 'store'])->name('rooms.store');
    Route::get('/rooms/join/{code}', [RoomController::class, 'join'])->name('rooms.join');
    Route::delete('/rooms/{room}/leave', [RoomController::class, 'leave'])->name('rooms.leave');
    Route::get('/rooms/{room}/lobby', RoomLobby::class)
        ->middleware('game.player')
        ->name('rooms.lobby');
    Route::post('/rooms/{room}/start', [RoomController::class, 'start'])
        ->middleware('game.player')
        ->name('rooms.start');

    // Game
    Route::get('/game/{room}', GameBoard::class)
        ->middleware('game.player')
        ->name('game.board');
    Route::get('/game/{room}/summary', GameSummary::class)
        ->middleware('game.player')
        ->name('game.summary');

    // Admin
    Route::prefix('admin')->name('admin.')->middleware('admin')->group(function () {
        Route::get('/cards', [AdminController::class, 'indexCards'])->name('cards.index');
        Route::get('/cards/create', [AdminController::class, 'createCard'])->name('cards.create');
        Route::post('/cards', [AdminController::class, 'storeCard'])->name('cards.store');
        Route::get('/cards/{card}/edit', [AdminController::class, 'editCard'])->name('cards.edit');
        Route::put('/cards/{card}', [AdminController::class, 'updateCard'])->name('cards.update');
        Route::delete('/cards/{card}', [AdminController::class, 'deleteCard'])->name('cards.delete');
    });
});