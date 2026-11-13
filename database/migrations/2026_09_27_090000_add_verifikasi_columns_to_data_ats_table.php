<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('data_ats', function (Blueprint $table) {
            // Default 'Disetujui' supaya data yang sudah ada (termasuk hasil import Excel)
            // tidak mendadak berstatus "menunggu verifikasi". Hanya input baru dari
            // form pengawas yang akan diset eksplisit ke 'Menunggu Verifikasi'.
            $table->string('status_verifikasi', 30)->default('Disetujui')->after('status_penanganan');
            $table->text('catatan_verifikasi')->nullable()->after('status_verifikasi');
            $table->foreignUuid('diverifikasi_oleh')->nullable()->after('catatan_verifikasi')
                ->constrained('users')->nullOnDelete();
            $table->timestamp('diverifikasi_at')->nullable()->after('diverifikasi_oleh');
        });
    }

    public function down(): void
    {
        Schema::table('data_ats', function (Blueprint $table) {
            $table->dropConstrainedForeignId('diverifikasi_oleh');
            $table->dropColumn(['status_verifikasi', 'catatan_verifikasi', 'diverifikasi_at']);
        });
    }
};
