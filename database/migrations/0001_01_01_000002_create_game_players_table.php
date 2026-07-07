<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('game_players', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_room_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('current_level', 20)->default('basecamp');
            $table->unsignedTinyInteger('mp')->default(0);
            $table->unsignedTinyInteger('sp')->default(0);
            $table->unsignedTinyInteger('tt')->default(0);
            $table->unsignedTinyInteger('turn_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamp('joined_at')->useCurrent();
            $table->timestamps();
            $table->unique(['game_room_id', 'user_id']);
            $table->index(['game_room_id', 'turn_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('game_players');
    }
};