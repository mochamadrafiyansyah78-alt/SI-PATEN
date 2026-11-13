<?php

namespace App\Http\Controllers\Pengawas;

use App\Http\Controllers\Controller;
use App\Models\DataAts;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardPengawasController extends Controller
{
    private const DAFTAR_PRIORITAS = ['Prioritas 1', 'Prioritas 2', 'Prioritas 3'];
    private const DAFTAR_PENANGANAN = ['Baru', 'Dalam Proses', 'Selesai - Kembali Sekolah', 'Selesai - Tidak Berhasil'];

    public function __invoke(): View
    {
        $sekolahId = Auth::user()->sekolah_id;
        $masterSekolah = fn () => DataAts::milikSekolah($sekolahId)->disetujui();

        $perPrioritas = $masterSekolah()
            ->selectRaw('prioritas_penjangkauan, count(*) as jumlah')
            ->groupBy('prioritas_penjangkauan')
            ->pluck('jumlah', 'prioritas_penjangkauan');

        $perJenis = $masterSekolah()
            ->selectRaw('status, count(*) as jumlah')
            ->groupBy('status')
            ->pluck('jumlah', 'status');

        $perPenanganan = $masterSekolah()
            ->selectRaw("coalesce(status_penanganan, 'Baru') as tahap, count(*) as jumlah")
            ->groupBy('tahap')
            ->pluck('jumlah', 'tahap');

        // Distribusi validasi mencakup semua data sekolah yang sudah lolos verifikator.
        $perValidasi = DataAts::milikSekolah($sekolahId)
            ->where('status_verifikasi', 'Disetujui')
            ->selectRaw('status_validasi, count(*) as jumlah')
            ->groupBy('status_validasi')
            ->pluck('jumlah', 'status_validasi');

        $jumlahPrioritas = collect(self::DAFTAR_PRIORITAS)
            ->mapWithKeys(fn (string $prioritas) => [$prioritas => (int) $perPrioritas->get($prioritas, 0)]);

        $totalSiswa = $jumlahPrioritas->sum();
        $totalLtm = (int) $perJenis->get('LTM', 0);
        $totalDo = (int) $perJenis->get('DO', 0);
        $totalKabupaten = DataAts::disetujui()->count();

        $dataValidasi = [
            (int) $perValidasi->get('Valid', 0),
            (int) $perValidasi->get('Belum Divalidasi', 0),
            (int) $perValidasi->get('Tidak Valid', 0),
        ];
        $menungguValidasi = $dataValidasi[1];

        $labelPenanganan = self::DAFTAR_PENANGANAN;
        $dataPenanganan = collect(self::DAFTAR_PENANGANAN)
            ->map(fn (string $tahap) => (int) $perPenanganan->get($tahap, 0))
            ->all();

        $siswaPrioritas1 = $masterSekolah()
            ->where('prioritas_penjangkauan', 'Prioritas 1')
            ->latest()
            ->limit(10)
            ->get();

        // Parameter dasar tautan kartu: buka tabel Data ATS yang sudah difilter ke sekolah ini.
        $parameterSekolah = $sekolahId ? ['sekolah_id' => $sekolahId] : [];

        return view('pengawas.dashboard', compact(
            'totalSiswa', 'totalLtm', 'totalDo', 'totalKabupaten', 'jumlahPrioritas',
            'dataValidasi', 'menungguValidasi', 'labelPenanganan', 'dataPenanganan',
            'siswaPrioritas1', 'parameterSekolah'
        ));
    }
}
