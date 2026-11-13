<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sk_bantuan', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->foreignUuid('data_ats_id')->constrained('data_ats')->cascadeOnDelete();
            $table->string('nomor_sk', 100);
            $table->date('tanggal_sk');
            $table->string('jenis_bantuan', 150);
            $table->decimal('jumlah_bantuan', 14, 2)->nullable();
            $table->text('keterangan')->nullable();
            $table->foreignUuid('dibuat_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sk_bantuan');
    }
};
