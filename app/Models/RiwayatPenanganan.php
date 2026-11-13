<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RiwayatPenanganan extends Model
{
    use HasUuids;

    public $timestamps = false;

    protected $table = 'riwayat_penanganan';

    protected $fillable = ['data_ats_id', 'status_penanganan', 'catatan', 'lampiran_path', 'diubah_oleh', 'created_at'];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    public function dataAts(): BelongsTo
    {
        return $this->belongsTo(DataAts::class);
    }

    public function diubahOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'diubah_oleh');
    }
}
