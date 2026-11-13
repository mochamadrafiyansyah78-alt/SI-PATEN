<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Mutasi extends Model
{
    use HasUuids;

    protected $table = 'data_mutasi';

    protected $fillable = [
        'nama_siswa', 'nisn', 'nik', 'kecamatan_domisili_id',
        'sekolah_asal', 'sekolah_tujuan', 'sekolah_pencatat_id',
        'tanggal_mutasi', 'keterangan', 'input_by',
    ];

    protected function casts(): array
    {
        return ['tanggal_mutasi' => 'date'];
    }

    public function kecamatanDomisili(): BelongsTo
    {
        return $this->belongsTo(Kecamatan::class, 'kecamatan_domisili_id');
    }

    public function sekolahPencatat(): BelongsTo
    {
        return $this->belongsTo(Sekolah::class, 'sekolah_pencatat_id');
    }

    public function inputOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'input_by');
    }
}