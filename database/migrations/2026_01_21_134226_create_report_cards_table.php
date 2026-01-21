<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Migration untuk tabel report_cards yang menyimpan data rapor siswa
     * mencakup status approval flow dan tracking file PDF
     */
    public function up(): void
    {
        Schema::create('report_cards', function (Blueprint $table) {
            $table->id();

            // Foreign keys untuk relasi
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->foreignId('class_id')->constrained('classes')->onDelete('cascade');

            // Konteks akademik
            $table->string('tahun_ajaran', 9)->comment('Format: 2024/2025');
            $table->enum('semester', ['1', '2'])->comment('Semester 1 atau 2');

            // Status approval flow
            $table->enum('status', ['DRAFT', 'PENDING_APPROVAL', 'APPROVED', 'RELEASED'])
                ->default('DRAFT')
                ->comment('DRAFT=belum final, PENDING_APPROVAL=menunggu approval, APPROVED=sudah disetujui, RELEASED=sudah dirilis ke parent');

            // Tracking generate
            $table->timestamp('generated_at')->nullable()->comment('Waktu rapor di-generate');
            $table->foreignId('generated_by')->nullable()->constrained('users')->onDelete('set null')
                ->comment('User yang men-generate rapor');

            // Tracking approval oleh Principal
            $table->timestamp('approved_at')->nullable()->comment('Waktu rapor disetujui');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null')
                ->comment('Principal yang menyetujui rapor');
            $table->text('approval_notes')->nullable()->comment('Catatan dari principal saat approval/reject');

            // Tracking release
            $table->timestamp('released_at')->nullable()->comment('Waktu rapor dirilis ke parent');

            // File PDF
            $table->string('pdf_path')->nullable()->comment('Path ke file PDF rapor di storage');

            // Summary data untuk quick access
            $table->decimal('average_score', 5, 2)->nullable()->comment('Rata-rata nilai semua mapel');
            $table->unsignedSmallInteger('rank')->nullable()->comment('Ranking siswa di kelas');

            $table->timestamps();

            // Unique constraint untuk memastikan 1 siswa hanya punya 1 rapor per semester
            $table->unique(['student_id', 'tahun_ajaran', 'semester'], 'report_cards_unique');

            // Indexes untuk optimasi query
            $table->index(['class_id', 'tahun_ajaran', 'semester', 'status']);
            $table->index(['status', 'tahun_ajaran', 'semester']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('report_cards');
    }
};
