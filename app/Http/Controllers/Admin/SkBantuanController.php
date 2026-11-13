<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DataAts;
use App\Models\SkBantuan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SkBantuanController extends Controller
{
    public function store(Request $request, DataAts $dataAtsItem): RedirectResponse
    {
        $validated = $request->validate([
            'nomor_sk' => ['required', 'string', 'max:100'],
            'tanggal_sk' => ['required', 'date'],
            'jenis_bantuan' => ['required', 'string', 'max:150'],
            'jumlah_bantuan' => ['nullable', 'numeric', 'min:0'],
            'keterangan' => ['nullable', 'string', 'max:1000'],
        ]);

        $validated['data_ats_id'] = $dataAtsItem->id;
        $validated['dibuat_oleh'] = Auth::id();

        SkBantuan::create($validated);

        return back()->with('success', 'SK bantuan berhasil ditambahkan.');
    }

    public function destroy(SkBantuan $skBantuan): RedirectResponse
    {
        $skBantuan->delete();

        return back()->with('success', 'SK bantuan berhasil dihapus.');
    }
}
