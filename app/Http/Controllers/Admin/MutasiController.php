<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Mutasi;
use Illuminate\View\View;

class MutasiController extends Controller
{
    public function index(): View
    {
        $data = Mutasi::with(['kecamatanDomisili', 'sekolahPencatat', 'inputOleh'])
            ->latest()
            ->paginate(20);

        return view('admin.mutasi.index', compact('data'));
    }
}