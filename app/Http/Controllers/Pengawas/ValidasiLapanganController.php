<?php

namespace App\Http\Controllers\Pengawas;

use App\Http\Controllers\Controller;
use App\Models\DataAts;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

// Validasi lapangan: pengawas memastikan fisik siswa ATS di sekolahnya sebelum data masuk master.
class ValidasiLapanganController extends Controller
{
    public function index(Request $request): View
    {
        $sekolahId = $this->sekolahPengawas();
        $tabAktif = $request->query('tab') === 'tidak-valid' ? 'Tidak Valid' : 'Belum Divalidasi';

        $lolosVerifikator = fn () => DataAts::where('sekolah_id', $sekolahId)->where('status_verifikasi', 'Disetujui');

        $data = $lolosVerifikator()
            ->with('kecamatanDomisili')
            ->where('status_validasi', $tabAktif)
            ->when($request->filled('cari'), function ($query) use ($request) {
                $cari = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $request->string('cari')->toString());
                $query->where(fn ($sub) => $sub->where('nama_siswa', 'ilike', "%{$cari}%")->orWhere('nisn', 'ilike', "%{$cari}%"));
            })
            ->oldest()
            ->paginate(20)
            ->withQueryString();

        $jumlahPerStatus = $lolosVerifikator()
            ->selectRaw('status_validasi, count(*) as total')
            ->groupBy('status_validasi')
            ->pluck('total', 'status_validasi');

        return view('pengawas.validasi.index', [
            'data' => $data,
            'tabAktif' => $tabAktif,
            'jumlahBelum' => $jumlahPerStatus['Belum Divalidasi'] ?? 0,
            'jumlahTidakValid' => $jumlahPerStatus['Tidak Valid'] ?? 0,
        ]);
    }

    // Halaman detail siswa + form validasi (dibuka dari klik nama siswa).
    public function show(DataAts $dataAtsItem): View
    {
        $this->pastikanBisaDivalidasi($dataAtsItem);

        $dataAtsItem->load(['sekolah', 'kecamatanDomisili', 'inputOleh', 'ditanganiOleh', 'diverifikasiOleh', 'divalidasiOleh', 'riwayatPenanganan.diubahOleh', 'skBantuan']);

        return view('pengawas.validasi.show', ['item' => $dataAtsItem]);
    }

    public function validasi(Request $request, DataAts $dataAtsItem): RedirectResponse
    {
        $this->pastikanBisaDivalidasi($dataAtsItem);

        $validated = $request->validate([
            'hasil' => ['required', 'in:Valid,Tidak Valid'],
            'catatan_validasi' => ['required_if:hasil,Tidak Valid', 'nullable', 'string', 'max:1000'],
        ], [
            'hasil.required' => 'Pilih hasil validasi: Valid atau Tidak Valid.',
            'catatan_validasi.required_if' => 'Catatan hasil investigasi lapangan wajib diisi untuk data yang tidak valid.',
        ]);

        $dataAtsItem->update([
            'status_validasi' => $validated['hasil'],
            'catatan_validasi' => $validated['catatan_validasi'] ?? null,
            'divalidasi_oleh' => Auth::id(),
            'divalidasi_at' => now(),
        ]);

        return back()->with('success', $validated['hasil'] === 'Valid'
            ? "{$dataAtsItem->nama_siswa} valid dan masuk ke master Data ATS."
            : "{$dataAtsItem->nama_siswa} dinyatakan tidak valid dan tidak masuk master Data ATS.");
    }

    private function sekolahPengawas(): string
    {
        $sekolahId = Auth::user()->sekolah_id;
        abort_if(blank($sekolahId), 403, 'Akun pengawas belum terhubung ke sekolah.');

        return $sekolahId;
    }

    // Hanya siswa sekolah sendiri yang sudah lolos verifikator.
    private function pastikanBisaDivalidasi(DataAts $dataAts): void
    {
        abort_unless($dataAts->sekolah_id === $this->sekolahPengawas(), 403);
        abort_unless($dataAts->status_verifikasi === 'Disetujui', 404);
    }
}
