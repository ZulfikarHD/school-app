<?php

/**
 * Migration untuk tabel psb_settings yang menyimpan konfigurasi
 * periode pendaftaran siswa baru per tahun ajaran
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations untuk membuat tabel psb_settings
     * dengan konfigurasi tanggal pendaftaran, kuota, dan biaya
     */
    public function up(): void
    {
        Schema::create('psb_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('academic_year_id')
                ->constrained('academic_years')
                ->onDelete('cascade')
                ->comment('Relasi ke tahun ajaran');
            $table->date('registration_open_date')->comment('Tanggal buka pendaftaran');
            $table->date('registration_close_date')->comment('Tanggal tutup pendaftaran');
            $table->date('announcement_date')->comment('Tanggal pengumuman hasil seleksi');
            $table->unsignedTinyInteger('re_registration_deadline_days')
                ->default(7)
                ->comment('Jumlah hari deadline daftar ulang setelah pengumuman');
            $table->unsignedInteger('registration_fee')
                ->default(0)
                ->comment('Biaya pendaftaran dalam Rupiah');
            $table->unsignedSmallInteger('quota_per_class')
                ->default(30)
                ->comment('Kuota maksimal per kelas');
            $table->boolean('waiting_list_enabled')
                ->default(true)
                ->comment('Aktifkan waiting list jika kuota penuh');
            $table->timestamps();

            $table->unique('academic_year_id');
            $table->index(['registration_open_date', 'registration_close_date'], 'psb_settings_reg_dates_index');
        });
    }

    /**
     * Reverse the migrations
     */
    public function down(): void
    {
        Schema::dropIfExists('psb_settings');
    }
};
