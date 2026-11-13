<?php

namespace App\Http\Controllers;

use App\Models\RiwayatPenanganan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LampiranController extends Controller
{
    public function show(RiwayatPenanganan $riwayat): StreamedResponse
    {
        $user = Auth::user();
        $riwayat->load('dataAts');

        abort_unless($riwayat->lampiran_path, 404);
        abort_unless($user->isAdminDinas() || $user->role === 'pengawas_sekolah', 403);
        abort_unless(Storage::disk('local')->exists($riwayat->lampiran_path), 404);

        return Storage::disk('local')->response($riwayat->lampiran_path);
    }
}
