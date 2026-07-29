<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add LRA item tracking to player_behaviors
        Schema::table('player_behaviors', function (Blueprint $table) {
            $table->string('lra_item')->nullable()->after('behavior_type');
            $table->string('lra_signal')->nullable()->after('lra_item'); // proving, disproving, mixed
            $table->index(['game_player_id', 'lra_item']);
        });

        // Add LRA assessment results to leadership_profiles
        Schema::table('leadership_profiles', function (Blueprint $table) {
            $table->json('lra_assessment')->nullable()->after('confidence_data');
            $table->text('lra_narrative')->nullable()->after('lra_assessment');
        });
    }

    public function down(): void
    {
        Schema::table('player_behaviors', function (Blueprint $table) {
            $table->dropColumn(['lra_item', 'lra_signal']);
            $table->dropIndex(['game_player_id', 'lra_item']);
        });

        Schema::table('leadership_profiles', function (Blueprint $table) {
            $table->dropColumn(['lra_assessment', 'lra_narrative']);
        });
    }
};
