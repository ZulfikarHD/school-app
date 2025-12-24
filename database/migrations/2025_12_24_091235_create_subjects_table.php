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
        Schema::create('subjects', function (Blueprint $table) {
            $table->id();

            $table->string('kode_mapel', 10)->unique()->comment('Kode mata pelajaran, contoh: MAT, IPA, IPS');
            $table->string('nama_mapel', 100)->comment('Nama lengkap mata pelajaran');
            $table->unsignedTinyInteger('tingkat')->nullable()->comment('Tingkat kelas (1-6), null = semua tingkat');
            $table->enum('kategori', ['UTAMA', 'MUATAN_LOKAL', 'PENGEMBANGAN_DIRI'])->default('UTAMA');
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            // Indexes untuk optimasi query
            $table->index(['tingkat', 'is_active']);
            $table->index('kategori');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subjects');
    }
};
