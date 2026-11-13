<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sekolahs', function (Blueprint $table) {
            $table->dropForeign(['kecamatan_id']);
        });

        DB::statement('ALTER TABLE sekolahs ALTER COLUMN kecamatan_id DROP NOT NULL');

        Schema::table('sekolahs', function (Blueprint $table) {
            $table->foreign('kecamatan_id')->references('id')->on('kecamatans')->restrictOnDelete();
            $table->string('kecamatan_luar_nama', 100)->nullable()->after('kecamatan_id');
            $table->string('kabupaten_sekolah', 100)->default('Kab. Lebak')->after('kecamatan_luar_nama');
        });
    }

    public function down(): void
    {
        Schema::table('sekolahs', function (Blueprint $table) {
            $table->dropColumn(['kecamatan_luar_nama', 'kabupaten_sekolah']);
        });

        DB::statement("DELETE FROM sekolahs WHERE kecamatan_id IS NULL");
        DB::statement('ALTER TABLE sekolahs ALTER COLUMN kecamatan_id SET NOT NULL');
    }
};