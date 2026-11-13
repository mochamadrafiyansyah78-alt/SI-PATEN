<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sekolah extends Model
{
    use HasUuids;

    protected $table = 'sekolahs';

    protected $fillable = [
    'npsn', 'nama_sekolah', 'status_sekolah', 'bentuk_pendidikan', 'kecamatan_id',
    'kecamatan_luar_nama', 'kabupaten_sekolah',
    'latitude', 'longitude', 'alamat',
];
    public function kecamatan(): BelongsTo
    {
        return $this->belongsTo(Kecamatan::class);
    }

    public function dataAts(): HasMany
    {
        return $this->hasMany(DataAts::class)->disetujui();
    }

    public function pengawas(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
