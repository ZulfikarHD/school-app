<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Membuat tabel students untuk menyimpan data siswa lengkap yang mencakup
     * biodata pribadi, alamat, kontak, dan data akademik untuk keperluan
     * manajemen siswa secara terpusat
     */
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();

            // Nomor Identitas - unique identifiers
            $table->string('nis', 20)->unique()->comment('Nomor Induk Siswa - auto generated');
            $table->string('nisn', 10)->unique()->comment('Nomor Induk Siswa Nasional');
            $table->string('nik', 16)->unique()->comment('Nomor Induk Kependudukan');

            // Biodata Pribadi
            $table->string('nama_lengkap', 100);
            $table->string('nama_panggilan', 50)->nullable();
            $table->enum('jenis_kelamin', ['L', 'P']);
            $table->string('tempat_lahir', 100);
            $table->date('tanggal_lahir');
            $table->enum('agama', ['Islam', 'Kristen', 'Katolik', 'Hindu', 'Buddha', 'Konghucu']);
            $table->unsignedTinyInteger('anak_ke')->default(1);
            $table->unsignedTinyInteger('jumlah_saudara')->default(1);
            $table->enum('status_keluarga', ['Anak Kandung', 'Anak Tiri', 'Anak Angkat'])->default('Anak Kandung');

            // Alamat Lengkap
            $table->text('alamat');
            $table->string('rt_rw', 10)->nullable();
            $table->string('kelurahan', 100);
            $table->string('kecamatan', 100);
            $table->string('kota', 100);
            $table->string('provinsi', 100);
            $table->string('kode_pos', 10)->nullable();

            // Kontak
            $table->string('no_hp', 20)->nullable();
            $table->string('email', 100)->nullable();
            $table->string('foto')->nullable()->comment('Path to student photo');

            // Data Akademik
            $table->unsignedBigInteger('kelas_id')->nullable()->comment('Current class - FK to classes table (future)');
            $table->string('tahun_ajaran_masuk', 9)->comment('Format: 2024/2025');
            $table->date('tanggal_masuk');
            $table->enum('status', ['aktif', 'mutasi', 'do', 'lulus'])->default('aktif');

            // Soft Delete & Timestamps
            $table->softDeletes();
            $table->timestamps();

            // Indexes untuk optimasi query
            $table->index('nis');
            $table->index('nisn');
            $table->index('nik');
            $table->index('nama_lengkap');
            $table->index('kelas_id');
            $table->index('status');
            $table->index('tahun_ajaran_masuk');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
