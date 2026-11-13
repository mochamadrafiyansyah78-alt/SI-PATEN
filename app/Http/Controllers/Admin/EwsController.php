<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DataAts;
use App\Models\Kecamatan;
use App\Models\User;
use App\Observers\DataAtsObserver;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class EwsController extends Controller
{
    protected const STATUS_SELESAI = ['Selesai - Kembali Sekolah', 'Selesai - Tidak Berhasil'];

    public function index(Request $request): View
    {
        $rekapLevel = DataAts::tidakDitolak()
            ->selectRaw('ews_level, count(*) as jumlah')
            ->groupBy('ews_level')
            ->pluck('jumlah', 'ews_level');

        $data = DataAts::tidakDitolak()
            ->with(['sekolah', 'kecamatanDomisili', 'ditanganiOleh'])
            ->when($request->filled('level'), fn ($q) => $q->where('ews_level', $request->level))
            ->when($request->filled('kecamatan_id'), fn ($q) => $q->where('kecamatan_domisili_id', $request->kecamatan_id))
            ->when($request->filled('status_penanganan'), fn ($q) => $q->where('status_penanganan', $request->status_penanganan))
            ->orderByRaw("case ews_level when 'Merah' then 1 when 'Kuning' then 2 else 3 end")
            ->orderByDesc('ews_score')
            ->paginate(20)
            ->withQueryString();

        $kecamatan = Kecamatan::orderBy('nama_kecamatan')->get();
        $penanggungJawab = User::orderBy('nama_lengkap')->get();

        return view('admin.ews.index', compact('data', 'rekapLevel', 'kecamatan', 'penanggungJawab'));
    }

    public function updatePenanganan(Request $request, DataAts $dataAtsItem): RedirectResponse
    {
        $validated = $request->validate([
            'status_penanganan' => ['required', 'in:Baru,Dalam Proses,Selesai - Kembali Sekolah,Selesai - Tidak Berhasil'],
            'catatan_penanganan' => ['nullable', 'string', 'max:1000'],
            'ditangani_oleh' => ['nullable', 'uuid', 'exists:users,id'],
            'lampiran' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ]);

        if ($request->hasFile('lampiran')) {
            DataAtsObserver::$pendingLampiran[$dataAtsItem->id] =
                $request->file('lampiran')->store('bukti-penanganan', 'local');
        }

        $data = [
            'status_penanganan' => $validated['status_penanganan'],
            'catatan_penanganan' => $validated['catatan_penanganan'] ?? null,
            'ditangani_oleh' => $validated['ditangani_oleh'] ?? Auth::id(),
            'status_updated_at' => now(),
        ];

        if (in_array($validated['status_penanganan'], self::STATUS_SELESAI, true)) {
            $data['prioritas_penjangkauan'] = 'Prioritas 3';
        }

        $dataAtsItem->update($data);

        return back()->with('success', "Penanganan {$dataAtsItem->nama_siswa} diperbarui.");
    }
}