<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('player_behaviors', function (Blueprint $table) {
            $table->string('source')->default('structural')->after('evidence');
            $table->float('context_modifier')->default(1.0)->after('source');
        });

        Schema::table('game_turns', function (Blueprint $table) {
            $table->unsignedInteger('turn_index')->nullable()->after('game_player_id');
        });
    }

    public function down(): void
    {
        Schema::table('player_behaviors', function (Blueprint $table) {
            $table->dropColumn(['source', 'context_modifier']);
        });

        Schema::table('game_turns', function (Blueprint $table) {
            $table->dropColumn('turn_index');
        });
    }
};
