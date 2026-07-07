<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('game_turns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_room_id')->constrained()->cascadeOnDelete();
            $table->foreignId('game_player_id')->constrained()->cascadeOnDelete();
            $table->foreignId('expedition_card_id')->constrained()->cascadeOnDelete();
            $table->string('chosen_option', 1);
            $table->tinyInteger('risk_die_result')->nullable();
            $table->integer('mp_effect')->default(0);
            $table->integer('sp_effect')->default(0);
            $table->integer('tt_effect')->default(0);
            $table->string('extra_effect_applied')->nullable();
            $table->boolean('rope_bridge_attempted')->default(false);
            $table->boolean('rope_bridge_success')->default(false);
            $table->string('dysfunction_triggered')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->index(['game_room_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('game_turns');
    }
};