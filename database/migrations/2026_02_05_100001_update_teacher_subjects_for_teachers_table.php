<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Migration untuk mengupdate teacher_subjects table agar mereferensikan
     * teachers table (bukan users table) dan menambahkan kolom is_primary
     */
    public function up(): void
    {
        // Step 1: Drop existing foreign key and unique constraint
        Schema::table('teacher_subjects', function (Blueprint $table) {
            $table->dropForeign(['teacher_id']);
            $table->dropUnique('unique_teacher_subject_class');
        });

        // Step 2: Rename old teacher_id column temporarily
        Schema::table('teacher_subjects', function (Blueprint $table) {
            $table->renameColumn('teacher_id', 'old_user_id');
        });

        // Step 3: Add new teacher_id column referencing teachers table
        Schema::table('teacher_subjects', function (Blueprint $table) {
            $table->foreignId('teacher_id')->nullable()->after('id')->constrained('teachers')->cascadeOnDelete();
            $table->boolean('is_primary')->default(false)->after('subject_id')->comment('Apakah mata pelajaran ini adalah mapel utama guru');
        });

        // Step 4: Migrate existing data (if any)
        // Data yang ada di teacher_subjects akan di-null-kan karena teacher records belum ada
        // Data lama akan di-preserve di old_user_id untuk referensi jika dibutuhkan
        // Setelah teachers di-seed, data bisa di-migrate manual jika diperlukan

        // Step 5: Remove old column (optional - keep for data reference)
        // Uncomment line below jika ingin menghapus kolom lama setelah data di-migrate
        // Schema::table('teacher_subjects', function (Blueprint $table) {
        //     $table->dropColumn('old_user_id');
        // });

        // Step 6: Re-create unique constraint dengan teacher_id baru
        Schema::table('teacher_subjects', function (Blueprint $table) {
            $table->unique(['teacher_id', 'subject_id', 'class_id', 'tahun_ajaran'], 'unique_teacher_subject_class');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Step 1: Drop new foreign key and unique constraint
        Schema::table('teacher_subjects', function (Blueprint $table) {
            $table->dropForeign(['teacher_id']);
            $table->dropUnique('unique_teacher_subject_class');
        });

        // Step 2: Drop new columns
        Schema::table('teacher_subjects', function (Blueprint $table) {
            $table->dropColumn(['teacher_id', 'is_primary']);
        });

        // Step 3: Rename old column back
        Schema::table('teacher_subjects', function (Blueprint $table) {
            $table->renameColumn('old_user_id', 'teacher_id');
        });

        // Step 4: Re-create original foreign key and unique constraint
        Schema::table('teacher_subjects', function (Blueprint $table) {
            $table->foreign('teacher_id')->references('id')->on('users')->cascadeOnDelete();
            $table->unique(['teacher_id', 'subject_id', 'class_id', 'tahun_ajaran'], 'unique_teacher_subject_class');
        });
    }
};
