<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('game_players', function (Blueprint $table) {
            $table->dropUnique(['game_room_id', 'user_id']);
            $table->foreignId('user_id')->nullable()->change();
            $table->string('guest_name', 60)->nullable()->after('user_id');
        });
    }

    public function down(): void
    {
        Schema::table('game_players', function (Blueprint $table) {
            $table->dropColumn('guest_name');
            $table->foreignId('user_id')->nullable(false)->change();
            $table->unique(['game_room_id', 'user_id']);
        });
    }
};
