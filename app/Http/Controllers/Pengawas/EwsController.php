<?php

namespace App\Http\Controllers\Pengawas;

use App\Http\Controllers\Controller;
use App\Models\DataAts;
use App\Observers\DataAtsObserver;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

// Early Warning khusus pengawas: dibuka dari tombol "Tindakan" di Data ATS.
class EwsController extends Controller
{
    public function index(Request $request): View
    {
        $level = in_array($request->level, ['Merah', 'Kuning', 'Hijau'], true) ? $request->level : null;

        $daftarKasus = DataAts::with(['sekolah', 'kecamatanDomisili'])
            ->disetujui()
            ->filterData($request)
            ->when($level, fn ($q) => $q->where('ews_level', $level))
            ->orderByDesc('ews_score')
            ->paginate(15)
            ->withQueryString();

        $ringkasan = DataAts::disetujui()
            ->selectRaw('ews_level, count(*) as total')
            ->groupBy('ews_level')
            ->pluck('total', 'ews_level');

        return view('pengawas.ews.index', compact('daftarKasus', 'level', 'ringkasan'));
    }

    public function show(DataAts $dataAtsItem): View
    {
        abort_unless($dataAtsItem->isSah(), 404);

        $dataAtsItem->load(['sekolah', 'kecamatanDomisili', 'inputOleh', 'ditanganiOleh', 'diverifikasiOleh', 'divalidasiOleh', 'riwayatPenanganan.diubahOleh', 'skBantuan']);

        return view('pengawas.ews.show', ['item' => $dataAtsItem, 'bisaKelolaSk' => false]);
    }

    public function updateStatus(Request $request, DataAts $dataAtsItem): RedirectResponse
    {
        abort_unless($dataAtsItem->isSah(), 404);

        $validated = $request->validate([
            'status_penanganan' => ['required', 'in:Baru,Dalam Proses,Selesai - Kembali Sekolah,Selesai - Tidak Berhasil'],
            'catatan_penanganan' => ['nullable', 'string', 'max:1000'],
            'lampiran' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ]);

        if ($request->hasFile('lampiran')) {
            DataAtsObserver::$pendingLampiran[$dataAtsItem->id] =
                $request->file('lampiran')->store('bukti-penanganan', 'local');
        }

        $dataAtsItem->update([
            'status_penanganan' => $validated['status_penanganan'],
            'catatan_penanganan' => $validated['catatan_penanganan'] ?? null,
            'ditangani_oleh' => Auth::id(),
            'status_updated_at' => now(),
        ]);

        return back()->with('success', "Penanganan {$dataAtsItem->nama_siswa} diperbarui.");
    }
}
