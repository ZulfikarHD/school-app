<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Membuat tabel failed_login_attempts untuk tracking login gagal dan
     * implementasi brute force protection dengan auto-lock setelah 5 percobaan gagal
     */
    public function up(): void
    {
        Schema::create('failed_login_attempts', function (Blueprint $table) {
            $table->id();
            $table->string('identifier');
            $table->string('ip_address', 45);
            $table->integer('attempts')->default(1);
            $table->timestamp('locked_until')->nullable();
            $table->timestamps();

            $table->unique(['identifier', 'ip_address']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('failed_login_attempts');
    }
};
