<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('data_ats', function (Blueprint $table) {
            $table->integer('ews_score')->default(0)->after('status_penanganan');
            $table->string('ews_level', 20)->default('Hijau')->after('ews_score');
            $table->text('catatan_penanganan')->nullable()->after('ews_level');
            $table->foreignUuid('ditangani_oleh')->nullable()->after('catatan_penanganan')
                ->constrained('users')->nullOnDelete();
            $table->timestamp('status_updated_at')->nullable()->after('ditangani_oleh');
        });

        // Selaraskan default lama ('Belum Ditangani') dengan nilai yang dipakai kode saat ini ('Baru')
        DB::table('data_ats')->where('status_penanganan', 'Belum Ditangani')->update(['status_penanganan' => 'Baru']);
    }

    public function down(): void
    {
        Schema::table('data_ats', function (Blueprint $table) {
            $table->dropConstrainedForeignId('ditangani_oleh');
            $table->dropColumn(['ews_score', 'ews_level', 'catatan_penanganan', 'status_updated_at']);
        });
    }
};
