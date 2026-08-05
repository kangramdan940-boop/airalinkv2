<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rekening_valids', function (Blueprint $table) {
            $table->id();
            $table->string('no_rekening');
            $table->string('atas_nama');
            $table->string('kode_bank');
            $table->string('nama_bank')->nullable();
            $table->integer('total_trx')->default(0);
            $table->timestamps();

            $table->index(['no_rekening', 'kode_bank']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rekening_valids');
    }
};
