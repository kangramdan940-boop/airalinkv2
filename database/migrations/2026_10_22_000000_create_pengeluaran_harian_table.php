<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pengeluaran_harian', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal')->index();
            $table->string('nama');
            $table->double('nominal')->default(0);
            $table->string('keterangan')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengeluaran_harian');
    }
};
