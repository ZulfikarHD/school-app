<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Menambahkan field authentication dan authorization yang diperlukan untuk
     * manajemen user, role-based access control, dan audit trail
     *
     * Note: STUDENT role tersedia di database untuk future implementation,
     * namun currently tidak digunakan di aplikasi
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('username')->unique()->after('name');
            $table->enum('role', ['SUPERADMIN', 'PRINCIPAL', 'ADMIN', 'TEACHER', 'PARENT', 'STUDENT'])->after('username');
            $table->enum('status', ['ACTIVE', 'INACTIVE'])->default('ACTIVE')->after('role');
            $table->boolean('is_first_login')->default(true)->after('status');
            $table->timestamp('last_login_at')->nullable()->after('is_first_login');
            $table->string('last_login_ip', 45)->nullable()->after('last_login_at');
            $table->string('phone_number', 20)->nullable()->after('last_login_ip');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'username',
                'role',
                'status',
                'is_first_login',
                'last_login_at',
                'last_login_ip',
                'phone_number',
            ]);
        });
    }
};
