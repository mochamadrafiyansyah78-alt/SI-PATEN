<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('data_mutasi', function (Blueprint $table) {
            $table->dropForeign(['sekolah_asal_id']);
            $table->dropForeign(['sekolah_tujuan_id']);
            $table->dropColumn(['sekolah_asal_id', 'sekolah_tujuan_id']);

            $table->string('sekolah_asal', 200)->after('kecamatan_domisili_id');
            $table->string('sekolah_tujuan', 200)->after('sekolah_asal');
            $table->foreignUuid('sekolah_pencatat_id')->nullable()->after('sekolah_tujuan')
                ->constrained('sekolahs')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('data_mutasi', function (Blueprint $table) {
            $table->dropConstrainedForeignId('sekolah_pencatat_id');
            $table->dropColumn(['sekolah_asal', 'sekolah_tujuan']);

            $table->foreignUuid('sekolah_asal_id')->nullable()->constrained('sekolahs')->restrictOnDelete();
            $table->foreignUuid('sekolah_tujuan_id')->nullable()->constrained('sekolahs')->restrictOnDelete();
        });
    }
};