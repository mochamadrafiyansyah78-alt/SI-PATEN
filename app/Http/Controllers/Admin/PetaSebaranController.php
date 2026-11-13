<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kecamatan;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PetaSebaranController extends Controller
{
    public function __invoke(Request $request): View
    {
        $prioritas = $request->query('prioritas'); // Prioritas 1/2/3 atau null
        $status    = $request->query('status');    // LTM/DO atau null

        $filter = function ($query) use ($prioritas, $status) {
            return $query
                ->when($prioritas, fn ($q) => $q->where('prioritas_penjangkauan', $prioritas))
                ->when($status, fn ($q) => $q->where('status', $status));
        };

        $titikSebaran = Kecamatan::query()
            ->whereNotNull('latitude')
            ->withCount([
                'dataAts as total'          => $filter,
                'dataAts as total_prioritas1' => fn ($q) => $filter($q)->where('prioritas_penjangkauan', 'Prioritas 1'),
                'dataAts as total_do'         => fn ($q) => $filter($q)->where('status', 'DO'),
            ])
            ->get()
            ->map(fn ($kec) => [
                'nama'       => $kec->nama_kecamatan,
                'lat'        => (float) $kec->latitude,
                'lng'        => (float) $kec->longitude,
                'total'      => $kec->total,
                'prioritas1' => $kec->total_prioritas1,
                'do'         => $kec->total_do,
                // Merah jika ada kasus Prioritas 1, oranye jika sedang, hijau jika minim
                'warna'      => $kec->total_prioritas1 > 0 ? 'red' : ($kec->total > 20 ? 'orange' : 'green'),
            ]);

        $jumlahBelumBerkoordinat = Kecamatan::whereNull('latitude')->count();

        return view('admin.peta-sebaran', compact('titikSebaran', 'prioritas', 'status', 'jumlahBelumBerkoordinat'));
    }
}
