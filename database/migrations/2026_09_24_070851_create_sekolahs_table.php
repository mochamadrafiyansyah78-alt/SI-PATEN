<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sekolahs', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->string('npsn', 15)->unique();
            $table->string('nama_sekolah', 200);
            $table->enum('status_sekolah', ['NEGERI', 'SWASTA']);
            $table->string('bentuk_pendidikan', 10);
            $table->foreignUuid('kecamatan_id')
                ->constrained('kecamatans')
                ->restrictOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sekolahs');
    }
};
