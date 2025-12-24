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
        Schema::create('student_attendances', function (Blueprint $table) {
            $table->id();

            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('class_id')->constrained('classes')->cascadeOnDelete();
            $table->date('tanggal')->comment('Tanggal kehadiran');
            $table->enum('status', ['H', 'I', 'S', 'A'])->comment('H=Hadir, I=Izin, S=Sakit, A=Alpha');
            $table->text('keterangan')->nullable()->comment('Catatan tambahan, misal alasan izin/sakit');

            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete()->comment('Teacher yang input data');
            $table->timestamp('recorded_at')->nullable()->comment('Waktu input data');

            $table->timestamps();

            // Unique constraint untuk mencegah duplicate attendance
            $table->unique(['student_id', 'tanggal'], 'unique_student_daily_attendance');

            // Indexes untuk optimasi query
            $table->index(['tanggal', 'status']);
            $table->index(['class_id', 'tanggal']);
            $table->index('recorded_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_attendances');
    }
};
