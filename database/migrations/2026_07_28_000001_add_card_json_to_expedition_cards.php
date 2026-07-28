<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('expedition_cards', function (Blueprint $table) {
            $table->string('card_id')->nullable()->unique()->after('id');
            $table->json('card_json')->nullable()->after('hidden_info_reveal');
        });
    }

    public function down(): void
    {
        Schema::table('expedition_cards', function (Blueprint $table) {
            $table->dropColumn(['card_id', 'card_json']);
        });
    }
};
