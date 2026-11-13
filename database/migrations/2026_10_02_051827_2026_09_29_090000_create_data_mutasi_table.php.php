<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('data_mutasi', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->string('nama_siswa', 150);
            $table->string('nik', 16);
            $table->foreignUuid('kecamatan_domisili_id')->constrained('kecamatans')->restrictOnDelete();
            $table->foreignUuid('sekolah_asal_id')->constrained('sekolahs')->restrictOnDelete();
            $table->foreignUuid('sekolah_tujuan_id')->constrained('sekolahs')->restrictOnDelete();
            $table->date('tanggal_mutasi');
            $table->text('keterangan')->nullable();
            $table->foreignUuid('input_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('data_mutasi');
    }
};