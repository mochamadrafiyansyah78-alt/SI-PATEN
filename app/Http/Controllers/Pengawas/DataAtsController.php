<?php

namespace App\Http\Controllers\Pengawas;

use App\Http\Controllers\Controller;
use App\Models\DataAts;
use App\Models\Kecamatan;
use App\Models\Sekolah;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class DataAtsController extends Controller
{
    private const DAFTAR_PRIORITAS = ['Prioritas 1', 'Prioritas 2', 'Prioritas 3'];

    // Master Data ATS (sah) seluruh kabupaten. Edit/hapus hanya untuk data sekolah sendiri.
    public function index(Request $request): View
    {
        $data = DataAts::with(['sekolah', 'kecamatanDomisili'])
            ->disetujui()
            ->filterData($request)
            ->latest()
            ->paginate(20)
            ->withQueryString();

        // Angka kartu prioritas mengikuti filter lain, tetapi TIDAK ikut filter prioritas itu sendiri,
        // supaya ketiga kartu selalu menampilkan perbandingan yang utuh.
        $requestTanpaPrioritas = $request->duplicate($request->except('prioritas'));
        $perPrioritas = DataAts::disetujui()
            ->filterData($requestTanpaPrioritas)
            ->selectRaw('prioritas_penjangkauan, count(*) as jumlah')
            ->groupBy('prioritas_penjangkauan')
            ->pluck('jumlah', 'prioritas_penjangkauan');

        $jumlahPrioritas = collect(self::DAFTAR_PRIORITAS)
            ->mapWithKeys(fn (string $prioritas) => [$prioritas => (int) $perPrioritas->get($prioritas, 0)]);

        $kecamatan = Kecamatan::orderBy('nama_kecamatan')->get();
        $sekolah = Sekolah::orderBy('nama_sekolah')->get();
        $totalKabupaten = DataAts::disetujui()->count();

        $menungguValidasi = DataAts::menungguValidasiLapangan()
            ->where('sekolah_id', Auth::user()->sekolah_id)
            ->count();

        return view('pengawas.data-ats.index', compact(
            'data', 'kecamatan', 'sekolah', 'totalKabupaten', 'menungguValidasi', 'jumlahPrioritas'
        ));
    }

    public function edit(DataAts $dataAtsItem): View
    {
        $this->pastikanMilikSekolahSendiri($dataAtsItem);

        $kecamatan = Kecamatan::orderBy('nama_kecamatan')->get();

        return view('pengawas.data-ats.edit', [
            'item' => $dataAtsItem,
            'kecamatan' => $kecamatan,
        ]);
    }

    public function update(Request $request, DataAts $dataAtsItem): RedirectResponse
    {
        $this->pastikanMilikSekolahSendiri($dataAtsItem);

        $validated = $this->validasi($request, $dataAtsItem->id);

        $diajukanUlang = $dataAtsItem->status_verifikasi === 'Ditolak';
        if ($diajukanUlang) {
            $validated['status_verifikasi'] = 'Menunggu Verifikasi';
            $validated['catatan_verifikasi'] = null;
            $validated['diverifikasi_oleh'] = null;
            $validated['diverifikasi_at'] = null;
        }

        // Data yang sudah diperbaiki setelah dinyatakan tidak valid kembali ke antrean validasi lapangan.
        $kembaliKeAntreanValidasi = $dataAtsItem->status_validasi === 'Tidak Valid';
        if ($kembaliKeAntreanValidasi) {
            $validated['status_validasi'] = 'Belum Divalidasi';
            $validated['divalidasi_oleh'] = null;
            $validated['divalidasi_at'] = null;
        }

        $dataAtsItem->update($validated);

        $tujuan = $dataAtsItem->fresh()->isSah()
            ? route('pengawas.data-ats.index')
            : route('pengawas.validasi.index');

        return redirect($tujuan)->with('success', match (true) {
            $diajukanUlang => 'Data ATS diperbarui dan diajukan ulang untuk verifikasi.',
            $kembaliKeAntreanValidasi => 'Data ATS diperbarui dan kembali ke antrean validasi lapangan.',
            default => 'Data ATS berhasil diperbarui.',
        });
    }

    public function destroy(DataAts $dataAtsItem): RedirectResponse
    {
        $this->pastikanMilikSekolahSendiri($dataAtsItem);

        $namaSiswa = $dataAtsItem->nama_siswa;
        $dataAtsItem->delete();

        return back()->with('success', "Data {$namaSiswa} berhasil dihapus.");
    }

    private function validasi(Request $request, ?string $ignoreId = null): array
    {
        return $request->validate([
            'kecamatan_domisili_id' => ['required', 'uuid', 'exists:kecamatans,id'],
            'nisn' => ['required', 'digits:10', Rule::unique('data_ats', 'nisn')->ignore($ignoreId)],
            'nama_siswa' => ['required', 'string', 'max:150'],
            'tempat_lahir' => ['nullable', 'string', 'max:100'],
            'tanggal_lahir' => ['nullable', 'date'],
            'status' => ['required', 'in:LTM,DO'],
            'alasan_approval' => ['nullable', 'string', 'max:255'],
            'prioritas_penjangkauan' => ['required', 'in:Prioritas 1,Prioritas 2,Prioritas 3'],
        ]);
    }

    private function pastikanMilikSekolahSendiri(DataAts $dataAtsItem): void
    {
        $sekolahId = Auth::user()->sekolah_id;

        abort_if(blank($sekolahId), 403, 'Akun pengawas belum terhubung ke sekolah.');
        abort_unless($dataAtsItem->sekolah_id === $sekolahId, 403);
    }
}
