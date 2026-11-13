<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Data DO SMA/SMK dari sumber baru tidak memiliki NIK.
        // Kolom tetap unik & berformat 16 digit, hanya boleh kosong (NULL tidak dianggap duplikat).
        DB::statement('ALTER TABLE data_ats ALTER COLUMN nik DROP NOT NULL');

        Schema::table('data_ats', function (Blueprint $table) {
            $table->char('jenis_kelamin', 1)->nullable();
            $table->unsignedSmallInteger('usia')->nullable();
            $table->string('desa_kelurahan', 100)->nullable();
            $table->string('tingkat_pendidikan', 60)->nullable();
            $table->text('keterangan_alasan')->nullable();
            $table->string('sumber_data', 40)->nullable()->index();
            $table->boolean('prioritas_diturunkan')->default(false);
            $table->index('nisn');
        });
    }

    public function down(): void
    {
        Schema::table('data_ats', function (Blueprint $table) {
            $table->dropIndex(['nisn']);
            $table->dropColumn([
                'jenis_kelamin', 'usia', 'desa_kelurahan', 'tingkat_pendidikan',
                'keterangan_alasan', 'sumber_data', 'prioritas_diturunkan',
            ]);
        });

        // Baris tanpa NIK tidak bisa dipertahankan bila kolom dikembalikan menjadi wajib.
        DB::table('data_ats')->whereNull('nik')->delete();
        DB::statement('ALTER TABLE data_ats ALTER COLUMN nik SET NOT NULL');
    }
};
