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
        Schema::create('kompetensis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pegawai_id')->constrained('pegawais')->onDelete('cascade');
            $table->string('nama_sertifikat');
            $table->string('nomor_sertifikat')->nullable();
            $table->string('penerbit');
            $table->date('tanggal_terbit');
            $table->date('tanggal_kadaluarsa');
            $table->string('status')->default('Aktif'); // Aktif, Kadaluarsa
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kompetensis');
    }
};
