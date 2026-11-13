<?php

namespace App\Services;

use App\Models\DataAts;
use App\Models\Sekolah;
use Carbon\Carbon;

class MonitoringService
{
    protected array $statusSelesai = ['Selesai - Kembali Sekolah', 'Selesai - Tidak Berhasil'];

    /**
     * Ranking sekolah berdasarkan performa penanganan dalam periode tertentu.
     * Dipakai di Laporan Bulanan & Monitoring Intervensi.
     */
    public function rankingSekolah(int $bulan, int $tahun)
    {
        return Sekolah::query()
            ->withCount([
                'dataAts as total' => fn ($q) => $q->whereMonth('created_at', $bulan)->whereYear('created_at', $tahun),
                'dataAts as selesai' => fn ($q) => $q->whereMonth('created_at', $bulan)
                    ->whereYear('created_at', $tahun)
                    ->whereIn('status_penanganan', $this->statusSelesai),
                'dataAts as mengendap' => fn ($q) => $q->where('status_penanganan', 'Baru')
                    ->where('created_at', '<=', now()->subDays(14)),
            ])
            ->get()
            ->filter(fn ($sekolah) => $sekolah->total > 0)
            ->map(function ($sekolah) {
                $sekolah->rasio_selesai = $sekolah->total > 0
                    ? round(($sekolah->selesai / $sekolah->total) * 100, 1)
                    : 0;

                return $sekolah;
            })
            ->sortBy('rasio_selesai') // paling lambat di atas, cocok utk perhatian Dinas
            ->values();
    }

    /**
     * Daftar kasus yang sudah lama "Baru" tanpa progres (untuk tabel peringatan).
     */
    public function kasusMengendap(int $ambangHari = 14)
    {
        return DataAts::disetujui()
            ->with(['sekolah', 'kecamatanDomisili'])
            ->where('status_penanganan', 'Baru')
            ->where('created_at', '<=', now()->subDays($ambangHari))
            ->orderBy('created_at')
            ->get()
            ->map(function ($data) {
                $data->hari_mengendap = Carbon::parse($data->created_at)->diffInDays(now());
                return $data;
            });
    }
}