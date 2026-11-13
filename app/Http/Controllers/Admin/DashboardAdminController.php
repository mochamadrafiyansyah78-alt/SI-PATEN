<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DataAts;
use App\Models\Kecamatan;
use Illuminate\View\View;

class DashboardAdminController extends Controller
{
    private const DAFTAR_PENANGANAN = ['Baru', 'Dalam Proses', 'Selesai - Kembali Sekolah', 'Selesai - Tidak Berhasil'];

    public function __invoke(): View
    {
        $rekapKecamatan = Kecamatan::withCount(['dataAts as jumlah_ltm' => function ($q) {
                $q->where('status', 'LTM');
            }])
            ->withCount(['dataAts as jumlah_do' => function ($q) {
                $q->where('status', 'DO');
            }])
            ->withCount('dataAts as total')
            ->orderByDesc('total')
            ->get();

        $rekapPrioritas = DataAts::disetujui()
            ->selectRaw('prioritas_penjangkauan, count(*) as jumlah')
            ->groupBy('prioritas_penjangkauan')
            ->orderBy('prioritas_penjangkauan')
            ->get();

        $totalKeseluruhan = DataAts::disetujui()->count();

        $ews = DataAts::disetujui()
            ->selectRaw('ews_level, count(*) as jumlah')
            ->groupBy('ews_level')
            ->pluck('jumlah', 'ews_level');

        $tahap = $this->rekapTahapAlur();
        [$trenLabel, $trenLtm, $trenDo] = $this->trenBulanan();
        [$labelKecamatan, $penangananKecamatan] = $this->penangananPerKecamatan();

        return view('admin.dashboard', compact(
            'rekapKecamatan', 'rekapPrioritas', 'totalKeseluruhan', 'ews', 'tahap',
            'trenLabel', 'trenLtm', 'trenDo', 'labelKecamatan', 'penangananKecamatan'
        ));
    }

    /**
     * Posisi seluruh data di dalam alur: verifikasi -> validasi lapangan -> master.
     * Satu query agregat supaya ringan di koneksi database yang latensinya tinggi.
     */
    private function rekapTahapAlur(): array
    {
        $baris = DataAts::selectRaw("
            count(*) filter (where status_verifikasi = 'Menunggu Verifikasi') as menunggu_verifikasi,
            count(*) filter (where status_verifikasi = 'Disetujui' and status_validasi = 'Belum Divalidasi') as menunggu_validasi,
            count(*) filter (where status_verifikasi = 'Disetujui' and status_validasi = 'Valid') as valid,
            count(*) filter (where status_verifikasi = 'Disetujui' and status_validasi = 'Tidak Valid') as tidak_valid,
            count(*) filter (where status_verifikasi = 'Ditolak') as ditolak
        ")->first();

        return [
            'menunggu_verifikasi' => (int) $baris->menunggu_verifikasi,
            'menunggu_validasi' => (int) $baris->menunggu_validasi,
            'valid' => (int) $baris->valid,
            'tidak_valid' => (int) $baris->tidak_valid,
            'ditolak' => (int) $baris->ditolak,
        ];
    }

    /**
     * Rekap jumlah data ATS (LTM & DO) per bulan, 12 bulan terakhir.
     */
    private function trenBulanan(): array
    {
        $bulanMulai = now()->subMonths(11)->startOfMonth();

        $label = collect(range(0, 11))->map(fn ($i) => $bulanMulai->copy()->addMonths($i)->format('Y-m'));

        $rawTren = DataAts::disetujui()
            ->selectRaw("to_char(created_at, 'YYYY-MM') as bulan, status, count(*) as jumlah")
            ->where('created_at', '>=', $bulanMulai)
            ->groupBy('bulan', 'status')
            ->get()
            ->groupBy('bulan');

        $ambilJumlah = function (string $bulan, string $status) use ($rawTren) {
            $baris = $rawTren->get($bulan, collect())->firstWhere('status', $status);

            return (int) ($baris->jumlah ?? 0);
        };

        $trenLtm = $label->map(fn ($b) => $ambilJumlah($b, 'LTM'))->values();
        $trenDo = $label->map(fn ($b) => $ambilJumlah($b, 'DO'))->values();

        $trenLabel = $label->map(fn ($b) => \Carbon\Carbon::createFromFormat('Y-m', $b)->translatedFormat('M Y'))->values();

        return [$trenLabel, $trenLtm, $trenDo];
    }

    /**
     * Status penanganan siswa master per kecamatan domisili, 10 kecamatan dengan data terbanyak.
     * Hasil: [daftar nama kecamatan, [status penanganan => jumlah per kecamatan]].
     */
    private function penangananPerKecamatan(): array
    {
        $baris = DataAts::disetujui()
            ->join('kecamatans', 'kecamatans.id', '=', 'data_ats.kecamatan_domisili_id')
            ->selectRaw("kecamatans.nama_kecamatan as kecamatan, coalesce(data_ats.status_penanganan, 'Baru') as tahap_penanganan, count(*) as jumlah")
            ->groupBy('kecamatans.nama_kecamatan', 'tahap_penanganan')
            ->get();

        $kecamatanTeratas = $baris->groupBy('kecamatan')
            ->map(fn ($kelompok) => $kelompok->sum('jumlah'))
            ->sortDesc()
            ->take(10)
            ->keys()
            ->values();

        $dataset = collect(self::DAFTAR_PENANGANAN)->mapWithKeys(function (string $tahap) use ($baris, $kecamatanTeratas) {
            $jumlahPerKecamatan = $kecamatanTeratas->map(fn (string $kecamatan) => (int) $baris
                ->where('kecamatan', $kecamatan)
                ->where('tahap_penanganan', $tahap)
                ->sum('jumlah'))->all();

            return [$tahap => $jumlahPerKecamatan];
        })->all();

        return [$kecamatanTeratas->all(), $dataset];
    }
}
