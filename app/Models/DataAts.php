<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\Request;

class DataAts extends Model
{
    use HasUuids;

    protected $table = 'data_ats';

    protected $fillable = [
        'sekolah_id', 'kecamatan_domisili_id', 'nik', 'nisn', 'nama_siswa',
        'tempat_lahir', 'tanggal_lahir', 'provinsi', 'kabupaten', 'status',
        'alasan_approval', 'prioritas_penjangkauan', 'input_by',
        'status_penanganan', 'catatan_penanganan', 'ditangani_oleh', 'status_updated_at',
        'status_verifikasi', 'catatan_verifikasi', 'diverifikasi_oleh', 'diverifikasi_at',
        'status_validasi', 'catatan_validasi', 'divalidasi_oleh', 'divalidasi_at', 'jenis_kelamin', 'usia', 'desa_kelurahan', 'tingkat_pendidikan',
        'keterangan_alasan', 'sumber_data', 'prioritas_diturunkan',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_lahir' => 'date',
            'status_updated_at' => 'datetime',
            'diverifikasi_at' => 'datetime',
            'divalidasi_at' => 'datetime',
            'prioritas_diturunkan' => 'boolean',
        ];
    }

    public function sekolah(): BelongsTo
    {
        return $this->belongsTo(Sekolah::class);
    }

    public function kecamatanDomisili(): BelongsTo
    {
        return $this->belongsTo(Kecamatan::class, 'kecamatan_domisili_id');
    }

    public function inputOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'input_by');
    }

    public function ditanganiOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'ditangani_oleh');
    }

    public function riwayatPenanganan(): HasMany
    {
        return $this->hasMany(RiwayatPenanganan::class)->latest('created_at');
    }

    public function skBantuan(): HasMany
    {
        return $this->hasMany(SkBantuan::class)->latest('tanggal_sk');
    }

    public function diverifikasiOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'diverifikasi_oleh');
    }

    public function divalidasiOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'divalidasi_oleh');
    }

    /** Data sah = lolos verifikator DAN divalidasi positif oleh pengawas di lapangan. */
    public function isSah(): bool
    {
        return $this->status_verifikasi === 'Disetujui' && $this->status_validasi === 'Valid';
    }

    // Scope bawaan agar Pengawas hanya melihat data sekolahnya sendiri
    public function scopeMilikSekolah(Builder $query, ?string $sekolahId): Builder
    {
        return $sekolahId ? $query->where('sekolah_id', $sekolahId) : $query;
    }

    // Pencarian + filter yang SAMA dipakai panel admin dan pengawas
    public function scopeFilterData(Builder $query, Request $request): Builder
    {
        return $query
            ->when($request->filled('kecamatan_id'), fn ($q) => $q->where('kecamatan_domisili_id', $request->kecamatan_id))
            ->when($request->filled('sekolah_id'), fn ($q) => $q->where('sekolah_id', $request->sekolah_id))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('prioritas'), fn ($q) => $q->where('prioritas_penjangkauan', $request->prioritas))
            ->when($request->filled('status_penanganan'), fn ($q) => $q->where('status_penanganan', $request->status_penanganan))
            ->when($request->filled('level'), fn ($q) => $q->where('ews_level', $request->level))
            ->when($request->filled('cari'), function ($q) use ($request) {
                $cari = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $request->string('cari')->toString());
                $q->where(fn ($sub) => $sub->where('nama_siswa', 'ilike', "%{$cari}%")->orWhere('nisn', 'ilike', "%{$cari}%"));
            });
    }

    // MASTER DATA ATS: hanya data yang lolos verifikator dan divalidasi positif pengawas.
    // Dipakai oleh Data ATS, statistik, Early Warning, peta, dan laporan.
    public function scopeDisetujui(Builder $query): Builder
    {
        return $query
            ->where('status_verifikasi', 'Disetujui')
            ->where('status_validasi', 'Valid');
    }

    // Lolos verifikator, menunggu pengawas turun ke lapangan.
    public function scopeMenungguValidasiLapangan(Builder $query): Builder
    {
        return $query
            ->where('status_verifikasi', 'Disetujui')
            ->where('status_validasi', 'Belum Divalidasi');
    }

    // Lolos verifikator tetapi dinyatakan tidak valid oleh pengawas: tidak masuk master.
    public function scopeTidakValid(Builder $query): Builder
    {
        return $query
            ->where('status_verifikasi', 'Disetujui')
            ->where('status_validasi', 'Tidak Valid');
    }
}
