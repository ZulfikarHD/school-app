<?php

/**
 * Migration untuk tabel psb_registrations yang menyimpan data pendaftaran
 * calon siswa baru dengan informasi lengkap biodata dan orang tua
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations untuk membuat tabel psb_registrations
     * dengan data siswa, orang tua, dan status pendaftaran
     */
    public function up(): void
    {
        Schema::create('psb_registrations', function (Blueprint $table) {
            $table->id();
            $table->string('registration_number', 20)
                ->unique()
                ->comment('Nomor pendaftaran format PSB/2025/0001');
            $table->foreignId('academic_year_id')
                ->constrained('academic_years')
                ->onDelete('cascade')
                ->comment('Relasi ke tahun ajaran');

            // Status pendaftaran
            $table->enum('status', [
                'pending',           // Baru mendaftar
                'document_review',   // Sedang verifikasi dokumen
                'approved',          // Diterima
                'rejected',          // Ditolak
                'waiting_list',      // Masuk waiting list
                're_registration',   // Tahap daftar ulang
                'completed',         // Selesai/jadi siswa
            ])->default('pending')->comment('Status proses pendaftaran');

            // Data Calon Siswa
            $table->string('student_name', 100)->comment('Nama lengkap calon siswa');
            $table->string('student_nik', 16)->comment('NIK calon siswa');
            $table->string('birth_place', 100)->comment('Tempat lahir');
            $table->date('birth_date')->comment('Tanggal lahir');
            $table->enum('gender', ['L', 'P'])->comment('Jenis kelamin L/P');
            $table->enum('religion', [
                'Islam', 'Kristen', 'Katolik', 'Hindu', 'Buddha', 'Konghucu', 'Lainnya',
            ])->comment('Agama');
            $table->text('address')->comment('Alamat lengkap');
            $table->unsignedTinyInteger('child_order')->comment('Anak ke-');
            $table->string('origin_school', 150)->nullable()->comment('Asal sekolah (TK/PAUD)');

            // Data Ayah
            $table->string('father_name', 100)->comment('Nama ayah');
            $table->string('father_nik', 16)->comment('NIK ayah');
            $table->string('father_occupation', 100)->comment('Pekerjaan ayah');
            $table->string('father_phone', 20)->comment('Nomor HP ayah');
            $table->string('father_email', 100)->nullable()->comment('Email ayah');

            // Data Ibu
            $table->string('mother_name', 100)->comment('Nama ibu');
            $table->string('mother_nik', 16)->comment('NIK ibu');
            $table->string('mother_occupation', 100)->comment('Pekerjaan ibu');
            $table->string('mother_phone', 20)->nullable()->comment('Nomor HP ibu');
            $table->string('mother_email', 100)->nullable()->comment('Email ibu');

            // Catatan dan verifikasi
            $table->text('notes')->nullable()->comment('Catatan tambahan dari pendaftar');
            $table->text('rejection_reason')->nullable()->comment('Alasan penolakan');
            $table->foreignId('verified_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete()
                ->comment('User yang memverifikasi');
            $table->timestamp('verified_at')->nullable()->comment('Waktu verifikasi');
            $table->timestamp('announced_at')->nullable()->comment('Waktu pengumuman ke pendaftar');
            $table->timestamps();

            // Indexes untuk performa query
            $table->index('status');
            $table->index('student_nik');
            $table->index(['academic_year_id', 'status']);
        });
    }

    /**
     * Reverse the migrations
     */
    public function down(): void
    {
        Schema::dropIfExists('psb_registrations');
    }
};
