<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('data_ats')
            ->where('status_verifikasi', 'Disetujui')
            ->where('status_validasi', 'Belum Divalidasi')
            ->update([
                'status_validasi' => 'Valid',
                'catatan_validasi' => 'Data lama sebelum validasi lapangan diberlakukan.',
                'divalidasi_at' => now(),
            ]);

        Schema::table('data_ats', function (Blueprint $table) {
            $table->index(['status_verifikasi', 'status_validasi'], 'data_ats_verifikasi_validasi_idx');
        });
    }

    public function down(): void
    {
        Schema::table('data_ats', function (Blueprint $table) {
            $table->dropIndex('data_ats_verifikasi_validasi_idx');
        });
    }
};
