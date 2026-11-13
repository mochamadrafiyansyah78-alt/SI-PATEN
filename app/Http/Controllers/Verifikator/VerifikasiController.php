<?php

namespace App\Http\Controllers\Verifikator;

use App\Http\Controllers\Controller;
use App\Models\DataAts;
use App\Models\User;
use App\Notifications\LaporanDiverifikasiNotification;
use App\Notifications\RingkasanVerifikasiNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class VerifikasiController extends Controller
{
    private const STATUS_ANTREAN = 'Menunggu Verifikasi';

    public function index(Request $request): View
    {
        $data = DataAts::with(['sekolah', 'kecamatanDomisili', 'inputOleh'])
            ->where('status_verifikasi', self::STATUS_ANTREAN)
            ->filterData($request)
            ->oldest()
            ->paginate(50)
            ->withQueryString();

        $total = DataAts::where('status_verifikasi', self::STATUS_ANTREAN)->count();

        return view('verifikator.verifikasi.index', compact('data', 'total'));
    }

    public function show(DataAts $dataAtsItem): View
    {
        $dataAtsItem->load(['sekolah', 'kecamatanDomisili', 'inputOleh', 'diverifikasiOleh', 'divalidasiOleh', 'ditanganiOleh', 'riwayatPenanganan.diubahOleh', 'skBantuan']);

        return view('verifikator.verifikasi.show', ['item' => $dataAtsItem, 'bisaKelolaSk' => false]);
    }

    public function approve(DataAts $dataAtsItem): RedirectResponse
    {
        abort_unless($dataAtsItem->status_verifikasi === self::STATUS_ANTREAN, 409, 'Data ini sudah diproses.');

        $dataAtsItem->update($this->kolomKeputusan(disetujui: true, catatan: null));

        if ($dataAtsItem->inputOleh) {
            $dataAtsItem->inputOleh->notify(new LaporanDiverifikasiNotification($dataAtsItem, true));
        }

        return back()->with('success', "Data {$dataAtsItem->nama_siswa} disetujui dan diteruskan ke pengawas untuk validasi lapangan.");
    }

    public function reject(Request $request, DataAts $dataAtsItem): RedirectResponse
    {
        abort_unless($dataAtsItem->status_verifikasi === self::STATUS_ANTREAN, 409, 'Data ini sudah diproses.');

        $validated = $request->validate([
            'catatan_verifikasi' => ['required', 'string', 'max:500'],
        ]);

        $dataAtsItem->update($this->kolomKeputusan(disetujui: false, catatan: $validated['catatan_verifikasi']));

        if ($dataAtsItem->inputOleh) {
            $dataAtsItem->inputOleh->notify(new LaporanDiverifikasiNotification($dataAtsItem, false));
        }

        return back()->with('success', "Data {$dataAtsItem->nama_siswa} ditolak.");
    }

    /**
     * Setujui / tolak banyak data sekaligus.
     * Jumlah query tetap (kunci baris, 1x UPDATE, notifikasi per penginput) berapa pun jumlah baris,
     * supaya aman di koneksi database yang latensinya tinggi.
     */
    public function aksiMassal(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'aksi' => ['required', 'in:setujui,tolak'],
            'ids' => ['required', 'array', 'min:1', 'max:100'],
            'ids.*' => ['required', 'uuid'],
            'catatan_verifikasi' => ['required_if:aksi,tolak', 'nullable', 'string', 'max:500'],
        ], [
            'ids.required' => 'Pilih minimal satu data.',
            'ids.max' => 'Maksimal 100 data per aksi massal.',
            'catatan_verifikasi.required_if' => 'Alasan penolakan wajib diisi.',
        ]);

        $disetujui = $validated['aksi'] === 'setujui';
        $catatan = $disetujui ? null : $validated['catatan_verifikasi'];
        $idDipilih = array_values(array_unique($validated['ids']));

        $diproses = DB::transaction(function () use ($idDipilih, $disetujui, $catatan) {
            $antrean = DataAts::whereIn('id', $idDipilih)
                ->where('status_verifikasi', self::STATUS_ANTREAN)
                ->lockForUpdate()
                ->get(['id', 'input_by']);

            if ($antrean->isNotEmpty()) {
                DataAts::whereIn('id', $antrean->pluck('id'))
                    ->update($this->kolomKeputusan($disetujui, $catatan));
            }

            return $antrean;
        });

        if ($diproses->isEmpty()) {
            return back()->withErrors(['ids' => 'Data yang dipilih sudah diproses sebelumnya.']);
        }

        $this->kirimRingkasan($diproses, $disetujui, $catatan);

        $dilewati = count($idDipilih) - $diproses->count();
        $pesan = $diproses->count() . ($disetujui
            ? ' data disetujui dan diteruskan ke pengawas untuk validasi lapangan.'
            : ' data ditolak.');
        if ($dilewati > 0) {
            $pesan .= " {$dilewati} data dilewati karena sudah diproses.";
        }

        return back()->with('success', $pesan);
    }

    private function kolomKeputusan(bool $disetujui, ?string $catatan): array
    {
        return [
            'status_verifikasi' => $disetujui ? 'Disetujui' : 'Ditolak',
            'catatan_verifikasi' => $disetujui ? null : $catatan,
            'diverifikasi_oleh' => Auth::id(),
            'diverifikasi_at' => now(),
        ];
    }

    private function kirimRingkasan($diproses, bool $disetujui, ?string $catatan): void
    {
        $jumlahPerPenginput = $diproses->whereNotNull('input_by')->countBy('input_by');

        if ($jumlahPerPenginput->isEmpty()) {
            return;
        }

        User::whereIn('id', $jumlahPerPenginput->keys())->get()->each(
            fn (User $penginput) => $penginput->notify(
                new RingkasanVerifikasiNotification($jumlahPerPenginput[$penginput->id], $disetujui, $catatan)
            )
        );
    }
}
