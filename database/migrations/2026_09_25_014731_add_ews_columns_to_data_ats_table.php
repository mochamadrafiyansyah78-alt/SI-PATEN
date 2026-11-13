<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('data_ats', function (Blueprint $table) {
            $table->integer('skor_risiko')->default(0);
            $table->text('catatan_intervensi')->nullable();
            $table->string('status_penanganan', 50)->default('Belum Ditangani');
        });
    }

    public function down(): void
    {
        Schema::table('data_ats', function (Blueprint $table) {
            $table->dropColumn(['skor_risiko', 'catatan_intervensi', 'status_penanganan']);
        });
    }
};