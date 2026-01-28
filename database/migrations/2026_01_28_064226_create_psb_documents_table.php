<?php

/**
 * Migration untuk tabel psb_documents yang menyimpan dokumen
 * persyaratan pendaftaran seperti akte, KK, KTP, dan foto
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations untuk membuat tabel psb_documents
     * dengan informasi file dan status verifikasi dokumen
     */
    public function up(): void
    {
        Schema::create('psb_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('psb_registration_id')
                ->constrained('psb_registrations')
                ->onDelete('cascade')
                ->comment('Relasi ke data pendaftaran');
            $table->enum('document_type', [
                'birth_certificate',  // Akte kelahiran
                'family_card',        // Kartu keluarga
                'parent_id',          // KTP orang tua
                'photo',              // Pas foto 3x4
                'other',              // Dokumen lainnya
            ])->comment('Jenis dokumen');
            $table->string('file_path', 255)->comment('Path file di storage');
            $table->string('original_name', 255)->comment('Nama file asli saat upload');
            $table->enum('status', [
                'pending',    // Belum diverifikasi
                'approved',   // Dokumen valid
                'rejected',   // Dokumen ditolak/perlu revisi
            ])->default('pending')->comment('Status verifikasi dokumen');
            $table->text('revision_note')->nullable()->comment('Catatan revisi jika ditolak');
            $table->timestamps();

            $table->index(['psb_registration_id', 'document_type']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations
     */
    public function down(): void
    {
        Schema::dropIfExists('psb_documents');
    }
};
