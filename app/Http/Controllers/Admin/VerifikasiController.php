<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DataAts;
use App\Notifications\LaporanDiverifikasiNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class VerifikasiController extends Controller
{
    public function index(): View
    {
        $data = DataAts::with(['sekolah', 'kecamatanDomisili', 'inputOleh'])
            ->where('status_verifikasi', 'Menunggu Verifikasi')
            ->oldest()
            ->paginate(20);

        return view('admin.verifikasi.index', compact('data'));
    }

    public function approve(DataAts $dataAtsItem): RedirectResponse
    {
        $dataAtsItem->update([
            'status_verifikasi' => 'Disetujui',
            'catatan_verifikasi' => null,
            'diverifikasi_oleh' => Auth::id(),
            'diverifikasi_at' => now(),
        ]);

        $this->kirimNotifikasi($dataAtsItem, disetujui: true);

        return back()->with('success', "Laporan {$dataAtsItem->nama_siswa} disetujui.");
    }

    public function reject(Request $request, DataAts $dataAtsItem): RedirectResponse
    {
        $validated = $request->validate([
            'catatan_verifikasi' => ['required', 'string', 'max:500'],
        ]);

        $dataAtsItem->update([
            'status_verifikasi' => 'Ditolak',
            'catatan_verifikasi' => $validated['catatan_verifikasi'],
            'diverifikasi_oleh' => Auth::id(),
            'diverifikasi_at' => now(),
        ]);

        $this->kirimNotifikasi($dataAtsItem, disetujui: false);

        return back()->with('success', "Laporan {$dataAtsItem->nama_siswa} ditolak.");
    }

    private function kirimNotifikasi(DataAts $dataAtsItem, bool $disetujui): void
    {
        if ($dataAtsItem->inputOleh) {
            $dataAtsItem->inputOleh->notify(new LaporanDiverifikasiNotification($dataAtsItem, $disetujui));
        }
    }
}
