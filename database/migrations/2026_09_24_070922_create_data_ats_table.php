<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('data_ats', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));

            $table->foreignUuid('sekolah_id')->constrained('sekolahs')->restrictOnDelete();
            $table->foreignUuid('kecamatan_domisili_id')->constrained('kecamatans')->restrictOnDelete();

            $table->char('nik', 16)->unique();
            $table->string('nisn', 15);
            $table->string('nama_siswa', 150);
            $table->string('tempat_lahir', 100)->nullable();
            $table->date('tanggal_lahir')->nullable();
            $table->string('provinsi', 100)->default('Prov. Banten');
            $table->string('kabupaten', 100)->default('Kab. Lebak');

            $table->enum('status', ['LTM', 'DO']);
            $table->string('alasan_approval', 150)->nullable();
            $table->enum('prioritas_penjangkauan', ['Prioritas 1', 'Prioritas 2', 'Prioritas 3']);

            $table->foreignUuid('input_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        DB::statement("alter table data_ats add constraint chk_nik_format check (nik ~ '^[0-9]{16}$')");
    }

    public function down(): void
    {
        Schema::dropIfExists('data_ats');
    }
};
