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
        Schema::create('classes', function (Blueprint $table) {
            $table->id();

            $table->unsignedTinyInteger('tingkat')->comment('1-6 untuk SD');
            $table->string('nama', 10)->comment('A, B, C, dst');
            // nama_lengkap removed, will use accessor

            $table->foreignId('wali_kelas_id')->nullable()->constrained('users')->nullOnDelete();
            $table->integer('kapasitas')->default(30);
            $table->string('tahun_ajaran', 9)->comment('Format: 2024/2025');
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            // Indexes
            $table->index(['tingkat', 'tahun_ajaran']);
            $table->index('is_active');
            // Unique constraint
            $table->unique(['tingkat', 'nama', 'tahun_ajaran']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('classes');
    }
};
