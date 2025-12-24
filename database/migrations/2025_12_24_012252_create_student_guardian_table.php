<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Membuat pivot table student_guardian untuk many-to-many relationship
     * antara siswa dan wali, dimana satu siswa bisa punya multiple guardians
     * (ayah, ibu, wali) dan satu guardian bisa punya multiple children
     */
    public function up(): void
    {
        Schema::create('student_guardian', function (Blueprint $table) {
            $table->id();

            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('guardian_id')->constrained('guardians')->cascadeOnDelete();

            // Flag untuk menandai kontak utama yang akan digunakan untuk notifikasi
            $table->boolean('is_primary_contact')->default(false);

            $table->timestamps();

            // Unique constraint untuk prevent duplicate relationships
            $table->unique(['student_id', 'guardian_id']);

            // Indexes
            $table->index('student_id');
            $table->index('guardian_id');
            $table->index('is_primary_contact');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_guardian');
    }
};
