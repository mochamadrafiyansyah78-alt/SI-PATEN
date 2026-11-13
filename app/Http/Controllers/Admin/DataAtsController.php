<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DataAts;
use App\Models\Kecamatan;
use App\Models\Sekolah;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DataAtsController extends Controller
{
    private const TAB_VALID = ['Menunggu Verifikasi', 'Menunggu Validasi', 'Tidak Valid', 'Ditolak'];

    public function index(Request $request): View
    {
        // Default: master data (sah). Tab lain memantau tiap tahap alur.
        $tabAktif = in_array($request->status_verifikasi, self::TAB_VALID, true)
            ? $request->status_verifikasi
            : 'Disetujui';

        $query = DataAts::with(['sekolah', 'kecamatanDomisili']);

        match ($tabAktif) {
            'Disetujui' => $query->disetujui(),
            'Menunggu Validasi' => $query->menungguValidasiLapangan(),
            'Tidak Valid' => $query->tidakValid(),
            default => $query->where('status_verifikasi', $tabAktif),
        };

        $data = $query->filterData($request)->latest()->paginate(20)->withQueryString();

        $kecamatan = Kecamatan::orderBy('nama_kecamatan')->get();
        $sekolah = Sekolah::orderBy('nama_sekolah')->get();
        $jumlahMenungguVerifikasi = DataAts::where('status_verifikasi', 'Menunggu Verifikasi')->count();
        $jumlahMenungguValidasi = DataAts::menungguValidasiLapangan()->count();

        return view('admin.data-ats.index', compact(
            'data', 'kecamatan', 'sekolah', 'tabAktif', 'jumlahMenungguVerifikasi', 'jumlahMenungguValidasi'
        ));
    }

    // Data yang ditolak verifikator dikembalikan ke antrean.
    public function ajukanUlang(DataAts $dataAtsItem): RedirectResponse
    {
        abort_unless($dataAtsItem->status_verifikasi === 'Ditolak', 409);

        $dataAtsItem->update([
            'status_verifikasi' => 'Menunggu Verifikasi',
            'catatan_verifikasi' => null,
            'diverifikasi_oleh' => null,
            'diverifikasi_at' => null,
        ]);

        return back()->with('success', 'Data diajukan ulang ke antrean verifikasi.');
    }

    public function show(DataAts $dataAtsItem): View
    {
        $dataAtsItem->load(['sekolah', 'kecamatanDomisili', 'inputOleh', 'ditanganiOleh', 'diverifikasiOleh', 'divalidasiOleh', 'riwayatPenanganan.diubahOleh', 'skBantuan.dibuatOleh']);

        return view('admin.data-ats.show', ['item' => $dataAtsItem, 'bisaKelolaSk' => true]);
    }
}
