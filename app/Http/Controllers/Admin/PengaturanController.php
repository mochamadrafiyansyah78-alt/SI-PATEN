<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kecamatan;
use App\Models\Sekolah;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PengaturanController extends Controller
{
    public function akunIndex(): View
    {
        $akun = User::where('role', 'pengawas_sekolah')
            ->with('sekolah')
            ->orderBy('nama_lengkap')
            ->paginate(20);

        return view('admin.pengaturan.akun', compact('akun'));
    }

    public function akunToggleActive(User $user): RedirectResponse
    {
        $user->update(['is_active' => ! $user->is_active]);

        return back()->with('success', $user->nama_lengkap . ' sekarang ' . ($user->is_active ? 'aktif' : 'nonaktif') . '.');
    }

    public function akunResetPassword(User $user): RedirectResponse
    {
        $passwordBaru = Str::password(10, symbols: false);

        $user->update(['password' => Hash::make($passwordBaru)]);

        return back()->with([
            'success' => "Password {$user->nama_lengkap} berhasil direset.",
            'password_baru' => $passwordBaru,
            'akun_direset' => $user->email,
        ]);
    }

    public function sekolahIndex(Request $request): View
    {
        $sekolah = Sekolah::with('kecamatan')
            ->when($request->filled('cari'), function ($q) use ($request) {
                $cari = $request->cari;
                $q->where(function ($sub) use ($cari) {
                    $sub->where('nama_sekolah', 'ilike', "%{$cari}%")
                        ->orWhere('npsn', 'ilike', "%{$cari}%");
                });
            })
            ->orderBy('nama_sekolah')
            ->paginate(20)
            ->withQueryString();

        return view('admin.pengaturan.sekolah', compact('sekolah'));
    }

    public function sekolahEdit(Sekolah $sekolah): View
    {
        $kecamatan = Kecamatan::orderBy('nama_kecamatan')->get();

        return view('admin.pengaturan.sekolah-edit', compact('sekolah', 'kecamatan'));
    }

    public function sekolahUpdate(Request $request, Sekolah $sekolah): RedirectResponse
    {
        $validated = $request->validate([
            'nama_sekolah' => ['required', 'string', 'max:200'],
            'status_sekolah' => ['required', 'in:NEGERI,SWASTA'],
            'bentuk_pendidikan' => ['required', 'string', 'max:10'],
            'lokasi' => ['required', 'in:lebak,luar'],
            'kecamatan_id' => ['required_if:lokasi,lebak', 'nullable', 'uuid', 'exists:kecamatans,id'],
            'kecamatan_luar_nama' => ['required_if:lokasi,luar', 'nullable', 'string', 'max:100'],
            'kabupaten_sekolah' => ['required_if:lokasi,luar', 'nullable', 'string', 'max:100'],
        ]);

        $sekolah->update([
            'nama_sekolah' => $validated['nama_sekolah'],
            'status_sekolah' => $validated['status_sekolah'],
            'bentuk_pendidikan' => $validated['bentuk_pendidikan'],
            'kecamatan_id' => $validated['lokasi'] === 'lebak' ? $validated['kecamatan_id'] : null,
            'kecamatan_luar_nama' => $validated['lokasi'] === 'luar' ? $validated['kecamatan_luar_nama'] : null,
            'kabupaten_sekolah' => $validated['lokasi'] === 'luar' ? $validated['kabupaten_sekolah'] : 'Kab. Lebak',
        ]);

        return redirect()->route('admin.pengaturan.sekolah')->with('success', 'Data sekolah diperbarui.');
    }
}
