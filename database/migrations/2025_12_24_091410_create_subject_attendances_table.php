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
        Schema::create('subject_attendances', function (Blueprint $table) {
            $table->id();

            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('class_id')->constrained('classes')->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained('subjects')->cascadeOnDelete();
            $table->foreignId('teacher_id')->constrained('users')->cascadeOnDelete()->comment('Guru yang mengajar');

            $table->date('tanggal')->comment('Tanggal pelajaran');
            $table->unsignedTinyInteger('jam_ke')->comment('Jam ke berapa (1-10)');
            $table->enum('status', ['H', 'I', 'S', 'A'])->comment('H=Hadir, I=Izin, S=Sakit, A=Alpha');
            $table->text('keterangan')->nullable();

            $table->timestamps();

            // Unique constraint untuk mencegah duplicate subject attendance
            $table->unique(['student_id', 'subject_id', 'tanggal', 'jam_ke'], 'unique_subject_attendance');

            // Indexes untuk optimasi query
            $table->index(['subject_id', 'tanggal']);
            $table->index(['teacher_id', 'tanggal']);
            $table->index(['class_id', 'tanggal']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subject_attendances');
    }
};
