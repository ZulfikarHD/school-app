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
        Schema::create('teacher_attendances', function (Blueprint $table) {
            $table->id();

            $table->foreignId('teacher_id')->constrained('users')->cascadeOnDelete();
            $table->date('tanggal')->comment('Tanggal kehadiran');

            $table->time('clock_in')->nullable()->comment('Waktu clock in');
            $table->time('clock_out')->nullable()->comment('Waktu clock out');

            $table->decimal('latitude_in', 10, 8)->nullable()->comment('Latitude saat clock in');
            $table->decimal('longitude_in', 11, 8)->nullable()->comment('Longitude saat clock in');
            $table->decimal('latitude_out', 10, 8)->nullable()->comment('Latitude saat clock out');
            $table->decimal('longitude_out', 11, 8)->nullable()->comment('Longitude saat clock out');

            $table->boolean('is_late')->default(false)->comment('Apakah terlambat');
            $table->enum('status', ['HADIR', 'TERLAMBAT', 'IZIN', 'SAKIT', 'ALPHA'])->default('HADIR');
            $table->text('keterangan')->nullable();

            $table->timestamps();

            // Unique constraint untuk mencegah duplicate clock
            $table->unique(['teacher_id', 'tanggal'], 'unique_teacher_attendance');

            // Indexes untuk optimasi query
            $table->index(['tanggal', 'status']);
            $table->index('teacher_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teacher_attendances');
    }
};
