<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Migration untuk table password reset tokens yang menyimpan token
     * untuk proses forgot password dengan expiry 1 jam sesuai business rule
     */
    public function up(): void
    {
        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->index();
            $table->string('token');
            $table->timestamp('created_at')->nullable();

            // Index untuk performance query by email
            $table->index(['email', 'created_at']);
        });
    }

    /**
     * Reverse the migrations untuk rollback database changes
     */
    public function down(): void
    {
        Schema::dropIfExists('password_reset_tokens');
    }
};
