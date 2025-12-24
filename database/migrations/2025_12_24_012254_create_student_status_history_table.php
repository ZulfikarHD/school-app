<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Membuat tabel student_status_history untuk tracking perubahan status siswa
     * seperti mutasi, DO, atau lulus dengan detail alasan dan tanggal perubahan
     */
    public function up(): void
    {
        Schema::create('student_status_history', function (Blueprint $table) {
            $table->id();

            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();

            // Status Change Details
            $table->enum('status_lama', ['aktif', 'mutasi', 'do', 'lulus']);
            $table->enum('status_baru', ['aktif', 'mutasi', 'do', 'lulus']);
            $table->date('tanggal');

            // Additional Info berdasarkan status
            $table->text('alasan');
            $table->text('keterangan')->nullable();
            $table->string('sekolah_tujuan', 200)->nullable()->comment('Untuk status mutasi');

            // Audit Trail
            $table->foreignId('changed_by')->constrained('users')->cascadeOnDelete()
                ->comment('User yang melakukan perubahan status');

            $table->timestamps();

            // Indexes
            $table->index('student_id');
            $table->index('status_baru');
            $table->index('tanggal');
            $table->index('changed_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_status_history');
    }
};
