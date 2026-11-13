<?php

namespace App\Http\Controllers\Pengawas;

use App\Http\Controllers\Controller;
use App\Models\Kecamatan;
use App\Models\Mutasi;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class MutasiController extends Controller
{
    public function index(): View
    {
        $data = Mutasi::with('kecamatanDomisili')
            ->where('sekolah_pencatat_id', Auth::user()->sekolah_id)
            ->latest()
            ->paginate(20);

        return view('pengawas.mutasi.index', compact('data'));
    }

    public function create(): View
    {
        $kecamatan = Kecamatan::orderBy('nama_kecamatan')->get();

        return view('pengawas.mutasi.create', compact('kecamatan'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nama_siswa' => ['required', 'string', 'max:150'],
            'nisn' => ['required', 'digits:10'],
            'kecamatan_domisili_id' => ['required', 'uuid', 'exists:kecamatans,id'],
            'sekolah_asal' => ['required', 'string', 'max:200'],
            'sekolah_tujuan' => ['required', 'string', 'max:200'],
            'tanggal_mutasi' => ['required', 'date'],
            'keterangan' => ['nullable', 'string', 'max:1000'],
        ]);

        $validated['input_by'] = Auth::id();
        $validated['sekolah_pencatat_id'] = Auth::user()->sekolah_id;

        Mutasi::create($validated);

        return redirect()->route('pengawas.mutasi.index')->with('success', 'Data mutasi berhasil dicatat.');
    }
}