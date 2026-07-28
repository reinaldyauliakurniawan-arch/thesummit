<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('real_world_challenges', function (Blueprint $table) {
            $table->timestamp('acknowledged_at')->nullable()->after('deadline');
            $table->timestamp('completed_at')->nullable()->after('acknowledged_at');
        });
    }

    public function down(): void
    {
        Schema::table('real_world_challenges', function (Blueprint $table) {
            $table->dropColumn(['acknowledged_at', 'completed_at']);
        });
    }
};
