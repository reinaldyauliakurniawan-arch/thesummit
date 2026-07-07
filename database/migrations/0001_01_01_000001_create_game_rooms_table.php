<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('game_rooms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('host_user_id')->constrained()->cascadeOnDelete();
            $table->string('code', 8)->unique();
            $table->string('status', 20)->default('waiting')->index();
            $table->foreignId('current_turn_player_id')
                ->nullable()
                ->constrained('game_players')
                ->nullOnDelete();
            $table->timestamp('current_turn_started_at')->nullable();
            $table->timestamp('final_round_started_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('game_rooms');
    }
};