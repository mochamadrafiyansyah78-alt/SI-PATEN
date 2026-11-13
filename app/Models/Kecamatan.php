<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Kecamatan extends Model
{
    use HasUuids;

    protected $fillable = ['nama_kecamatan', 'latitude', 'longitude', 'geocoded_at'];

    public function sekolah(): HasMany
    {
        return $this->hasMany(Sekolah::class);
    }

    public function dataAts(): HasMany
    {
        return $this->hasMany(DataAts::class, 'kecamatan_domisili_id')->disetujui();
    }
}
