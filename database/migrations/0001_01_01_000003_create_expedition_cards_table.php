<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expedition_cards', function (Blueprint $table) {
            $table->id();
            $table->string('level', 20);
            $table->string('kategori', 20);
            $table->string('tipe', 20);
            $table->text('teks_situasi');
            $table->text('opsi_a_teks');
            $table->tinyInteger('opsi_a_mp')->default(0);
            $table->tinyInteger('opsi_a_sp')->default(0);
            $table->tinyInteger('opsi_a_tt')->default(0);
            $table->string('opsi_a_extra')->nullable();
            $table->text('opsi_b_teks');
            $table->tinyInteger('opsi_b_mp')->default(0);
            $table->tinyInteger('opsi_b_sp')->default(0);
            $table->tinyInteger('opsi_b_tt')->default(0);
            $table->string('opsi_b_extra')->nullable();
            $table->string('dysfunction_tag')->nullable();
            $table->timestamps();
            $table->index(['level', 'kategori', 'tipe']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expedition_cards');
    }
};