<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('data_ats', function (Blueprint $table) {
            $table->string('status_validasi', 20)->default('Belum Divalidasi')->after('status_verifikasi');
            $table->text('catatan_validasi')->nullable()->after('status_validasi');
            $table->foreignUuid('divalidasi_oleh')->nullable()->after('catatan_validasi')
                ->constrained('users')->nullOnDelete();
            $table->timestamp('divalidasi_at')->nullable()->after('divalidasi_oleh');
        });
    }

    public function down(): void
    {
        Schema::table('data_ats', function (Blueprint $table) {
            $table->dropConstrainedForeignId('divalidasi_oleh');
            $table->dropColumn(['status_validasi', 'catatan_validasi', 'divalidasi_at']);
        });
    }
};