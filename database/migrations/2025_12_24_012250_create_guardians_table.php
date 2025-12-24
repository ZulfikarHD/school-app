<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Membuat tabel guardians untuk menyimpan data orang tua/wali siswa
     * yang digunakan untuk kontak darurat, portal orang tua, dan keperluan
     * administrasi sekolah
     */
    public function up(): void
    {
        Schema::create('guardians', function (Blueprint $table) {
            $table->id();

            // Identitas
            $table->string('nik', 16)->unique();
            $table->string('nama_lengkap', 100);
            $table->enum('hubungan', ['ayah', 'ibu', 'wali']);

            // Data Pekerjaan & Pendidikan
            $table->string('pekerjaan', 100);
            $table->enum('pendidikan', ['SD', 'SMP', 'SMA', 'D3', 'S1', 'S2', 'S3']);
            $table->enum('penghasilan', ['<1jt', '1-3jt', '3-5jt', '>5jt']);

            // Kontak
            $table->string('no_hp', 20);
            $table->string('email', 100)->nullable();
            $table->text('alamat')->nullable();

            // Link ke User Account untuk Portal Orang Tua
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete()
                ->comment('Link ke akun portal orang tua');

            $table->timestamps();

            // Indexes
            $table->index('nik');
            $table->index('no_hp');
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('guardians');
    }
};
