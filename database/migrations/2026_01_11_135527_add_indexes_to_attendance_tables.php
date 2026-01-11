<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations untuk menambahkan indexes pada attendance tables
     * untuk meningkatkan performance query yang sering digunakan
     */
    public function up(): void
    {
        // Indexes untuk student_attendances table
        Schema::table('student_attendances', function (Blueprint $table) {
            // Index untuk query by tanggal (most common filter)
            $table->index('tanggal', 'idx_student_attendances_tanggal');

            // Index untuk query by class_id dan tanggal (untuk class reports)
            $table->index(['class_id', 'tanggal'], 'idx_student_attendances_class_date');

            // Index untuk query by status (untuk filtering)
            $table->index('status', 'idx_student_attendances_status');

            // Index untuk query by student_id dan tanggal (untuk student reports)
            $table->index(['student_id', 'tanggal'], 'idx_student_attendances_student_date');
        });

        // Indexes untuk teacher_attendances table
        Schema::table('teacher_attendances', function (Blueprint $table) {
            // Index untuk query by tanggal
            $table->index('tanggal', 'idx_teacher_attendances_tanggal');

            // Index untuk query by teacher_id dan tanggal
            $table->index(['teacher_id', 'tanggal'], 'idx_teacher_attendances_teacher_date');

            // Index untuk query by is_late (untuk late reports)
            $table->index('is_late', 'idx_teacher_attendances_is_late');
        });

        // Indexes untuk leave_requests table
        Schema::table('leave_requests', function (Blueprint $table) {
            // Index untuk query by status (pending, approved, rejected)
            $table->index('status', 'idx_leave_requests_status');

            // Index untuk query by student_id
            $table->index('student_id', 'idx_leave_requests_student');

            // Index untuk query by tanggal range
            $table->index(['tanggal_mulai', 'tanggal_selesai'], 'idx_leave_requests_date_range');
        });

        // Indexes untuk teacher_leaves table
        Schema::table('teacher_leaves', function (Blueprint $table) {
            // Index untuk query by status
            $table->index('status', 'idx_teacher_leaves_status');

            // Index untuk query by teacher_id
            $table->index('teacher_id', 'idx_teacher_leaves_teacher');

            // Index untuk query by tanggal range
            $table->index(['tanggal_mulai', 'tanggal_selesai'], 'idx_teacher_leaves_date_range');
        });

        // Indexes untuk subject_attendances table
        Schema::table('subject_attendances', function (Blueprint $table) {
            // Index untuk query by tanggal
            $table->index('tanggal', 'idx_subject_attendances_tanggal');

            // Index untuk query by class_id dan subject_id
            $table->index(['class_id', 'subject_id'], 'idx_subject_attendances_class_subject');

            // Index untuk query by teacher_id
            $table->index('teacher_id', 'idx_subject_attendances_teacher');
        });
    }

    /**
     * Reverse the migrations dengan drop semua indexes
     */
    public function down(): void
    {
        Schema::table('student_attendances', function (Blueprint $table) {
            $table->dropIndex('idx_student_attendances_tanggal');
            $table->dropIndex('idx_student_attendances_class_date');
            $table->dropIndex('idx_student_attendances_status');
            $table->dropIndex('idx_student_attendances_student_date');
        });

        Schema::table('teacher_attendances', function (Blueprint $table) {
            $table->dropIndex('idx_teacher_attendances_tanggal');
            $table->dropIndex('idx_teacher_attendances_teacher_date');
            $table->dropIndex('idx_teacher_attendances_is_late');
        });

        Schema::table('leave_requests', function (Blueprint $table) {
            $table->dropIndex('idx_leave_requests_status');
            $table->dropIndex('idx_leave_requests_student');
            $table->dropIndex('idx_leave_requests_date_range');
        });

        Schema::table('teacher_leaves', function (Blueprint $table) {
            $table->dropIndex('idx_teacher_leaves_status');
            $table->dropIndex('idx_teacher_leaves_teacher');
            $table->dropIndex('idx_teacher_leaves_date_range');
        });

        Schema::table('subject_attendances', function (Blueprint $table) {
            $table->dropIndex('idx_subject_attendances_tanggal');
            $table->dropIndex('idx_subject_attendances_class_subject');
            $table->dropIndex('idx_subject_attendances_teacher');
        });
    }
};
