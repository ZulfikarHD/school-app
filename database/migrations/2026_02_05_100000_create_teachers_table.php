<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Migration untuk membuat tabel teachers yang menyimpan data lengkap guru
     * dengan relasi one-to-one ke users table untuk autentikasi
     */
    public function up(): void
    {
        Schema::create('teachers', function (Blueprint $table) {
            $table->id();

            // Relasi ke User untuk autentikasi (nullable, guru bisa tidak punya akun login)
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();

            // Nomor Identitas
            $table->string('nip', 30)->nullable()->unique()->comment('Nomor Induk Pegawai, nullable untuk guru honorer');
            $table->string('nik', 16)->unique()->comment('Nomor Induk Kependudukan');

            // Data Pribadi
            $table->string('nama_lengkap', 150)->comment('Nama lengkap sesuai KTP');
            $table->string('tempat_lahir', 100)->nullable();
            $table->date('tanggal_lahir')->nullable();
            $table->enum('jenis_kelamin', ['L', 'P'])->comment('L = Laki-laki, P = Perempuan');
            $table->text('alamat')->nullable();
            $table->string('no_hp', 20)->nullable();
            $table->string('email', 100)->nullable()->unique();
            $table->string('foto', 255)->nullable()->comment('Path foto profil di storage');

            // Data Kepegawaian
            $table->enum('status_kepegawaian', ['tetap', 'honorer', 'kontrak'])->default('honorer');
            $table->date('tanggal_mulai_kerja')->nullable();
            $table->date('tanggal_berakhir_kontrak')->nullable()->comment('Hanya untuk status kontrak');

            // Kualifikasi Akademik
            $table->string('kualifikasi_pendidikan', 50)->nullable()->comment('Contoh: S1, S2, S3');

            // Status
            $table->boolean('is_active')->default(true);

            $table->timestamps();
            $table->softDeletes();

            // Indexes untuk optimasi query
            $table->index('nama_lengkap');
            $table->index('status_kepegawaian');
            $table->index('is_active');
            $table->index(['status_kepegawaian', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teachers');
    }
};
