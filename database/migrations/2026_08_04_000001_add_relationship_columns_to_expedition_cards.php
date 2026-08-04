<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('expedition_cards', function (Blueprint $table) {
            $table->json('opsi_a_relationship')->nullable()->after('opsi_a_cross_player');
            $table->json('opsi_b_relationship')->nullable()->after('opsi_b_cross_player');
        });
    }

    public function down(): void
    {
        Schema::table('expedition_cards', function (Blueprint $table) {
            $table->dropColumn(['opsi_a_relationship', 'opsi_b_relationship']);
        });
    }
};
