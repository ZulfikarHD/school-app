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
        Schema::create('teacher_leaves', function (Blueprint $table) {
            $table->id();

            $table->foreignId('teacher_id')->constrained('users')->cascadeOnDelete();
            $table->enum('jenis', ['IZIN', 'SAKIT', 'CUTI'])->comment('Jenis permohonan cuti');
            $table->date('tanggal_mulai')->comment('Tanggal mulai cuti');
            $table->date('tanggal_selesai')->comment('Tanggal selesai cuti');
            $table->unsignedInteger('jumlah_hari')->comment('Total hari cuti');
            $table->text('alasan')->comment('Alasan cuti');
            $table->string('attachment_path')->nullable()->comment('Path file lampiran (surat dokter, dll)');

            $table->enum('status', ['PENDING', 'APPROVED', 'REJECTED'])->default('PENDING');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete()->comment('Admin/Principal yang review');
            $table->timestamp('reviewed_at')->nullable()->comment('Waktu direview');
            $table->text('rejection_reason')->nullable()->comment('Alasan reject jika ditolak');

            $table->timestamps();

            // Indexes untuk optimasi query
            $table->index(['teacher_id', 'status']);
            $table->index(['status', 'created_at']);
            $table->index(['tanggal_mulai', 'tanggal_selesai']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teacher_leaves');
    }
};
