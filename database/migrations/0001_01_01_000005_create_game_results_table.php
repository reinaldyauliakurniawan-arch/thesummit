<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('game_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_room_id')->constrained()->cascadeOnDelete();
            $table->foreignId('game_player_id')->constrained()->cascadeOnDelete();
            $table->string('final_level', 20);
            $table->unsignedTinyInteger('final_mp');
            $table->unsignedTinyInteger('final_sp');
            $table->unsignedTinyInteger('final_tt');
            $table->unsignedSmallInteger('final_score');
            $table->string('badge', 30)->default('none');
            $table->unsignedTinyInteger('rank');
            $table->timestamps();
            $table->unique(['game_room_id', 'game_player_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('game_results');
    }
};