<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('pelatihans', function (Blueprint $table) {
            $table->id();
            $table->string('nama_pelatihan');
            $table->string('penyelenggara');
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');
            $table->integer('kuota')->default(0);
            $table->string('status')->default('Terjadwal'); // Terjadwal, Berjalan, Selesai
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pelatihans');
    }
};
