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
        Schema::create('teacher_subjects', function (Blueprint $table) {
            $table->id();

            $table->foreignId('teacher_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained('subjects')->cascadeOnDelete();
            $table->foreignId('class_id')->nullable()->constrained('classes')->nullOnDelete()->comment('Null jika guru mengajar mapel ini di semua kelas');

            $table->string('tahun_ajaran', 9)->comment('Format: 2024/2025');

            $table->timestamps();

            // Unique constraint: satu guru tidak bisa mengajar mapel yang sama di kelas yang sama dalam tahun ajaran yang sama
            $table->unique(['teacher_id', 'subject_id', 'class_id', 'tahun_ajaran'], 'unique_teacher_subject_class');

            // Indexes untuk query optimization
            $table->index(['teacher_id', 'tahun_ajaran']);
            $table->index(['subject_id', 'class_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teacher_subjects');
    }
};
