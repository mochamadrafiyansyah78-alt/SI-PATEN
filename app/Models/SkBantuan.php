<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SkBantuan extends Model
{
    use HasUuids;

    protected $table = 'sk_bantuan';

    protected $fillable = [
        'data_ats_id', 'nomor_sk', 'tanggal_sk', 'jenis_bantuan',
        'jumlah_bantuan', 'keterangan', 'dibuat_oleh',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_sk' => 'date',
            'jumlah_bantuan' => 'decimal:2',
        ];
    }

    public function dataAts(): BelongsTo
    {
        return $this->belongsTo(DataAts::class);
    }

    public function dibuatOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dibuat_oleh');
    }
}
