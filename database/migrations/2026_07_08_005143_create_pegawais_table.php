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
        Schema::create('pegawais', function (Blueprint $table) {
            $table->id();
            $table->string('nip')->nullable()->unique();
            $table->string('nama');
            $table->string('jabatan');
            $table->string('unit_kerja');
            $table->string('status_kepegawaian'); // PNS, PPPK, Non-ASN
            $table->string('email')->nullable();
            $table->string('telepon')->nullable();
            $table->date('tanggal_masuk')->nullable();
            $table->integer('skor_kompetensi')->default(0);
            $table->string('status')->default('Aktif'); // Aktif, Cuti, Nonaktif
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pegawais');
    }
};
