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
        Schema::create('leave_requests', function (Blueprint $table) {
            $table->id();

            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->enum('jenis', ['IZIN', 'SAKIT'])->comment('Jenis permohonan');
            $table->date('tanggal_mulai')->comment('Tanggal mulai izin/sakit');
            $table->date('tanggal_selesai')->comment('Tanggal selesai izin/sakit');
            $table->text('alasan')->comment('Alasan izin/sakit');
            $table->string('attachment_path')->nullable()->comment('Path file lampiran (surat dokter, dll)');

            $table->enum('status', ['PENDING', 'APPROVED', 'REJECTED'])->default('PENDING');
            $table->foreignId('submitted_by')->constrained('users')->cascadeOnDelete()->comment('Parent yang submit');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete()->comment('Teacher/Admin yang review');
            $table->timestamp('reviewed_at')->nullable()->comment('Waktu direview');
            $table->text('rejection_reason')->nullable()->comment('Alasan reject jika ditolak');

            $table->timestamps();

            // Indexes untuk optimasi query
            $table->index(['student_id', 'status']);
            $table->index(['status', 'created_at']);
            $table->index('submitted_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leave_requests');
    }
};
