@extends('layouts.app')
@section('title', 'Akun Pengawas')

@section('content')
<div class="mb-6 flex flex-wrap items-center justify-between gap-3">
    <div>
        <div class="flex items-center gap-2"><span class="h-6 w-1 rounded bg-lebak-gold"></span><h1 class="text-2xl font-extrabold text-slate-900">Akun Pengawas</h1></div>
        <p class="mt-1 text-sm text-slate-500">Aktifkan atau nonaktifkan akun, dan reset password pengawas sekolah.</p>
    </div>
    <a href="{{ route('admin.pengaturan.sekolah') }}" class="btn-edit">Kelola Data Sekolah &rarr;</a>
</div>

@if (session('password_baru'))
    <div class="mb-5 rounded-xl border border-lebak-gold/40 bg-lebak-cream px-4 py-3 text-sm text-lebak-gold">
        Password baru untuk <strong>{{ session('akun_direset') }}</strong>:
        <code class="rounded border border-lebak-gold/30 bg-white px-2 py-0.5 font-mono text-slate-800">{{ session('password_baru') }}</code>
        Catat sekarang, tidak akan ditampilkan lagi.
    </div>
@endif

<div class="overflow-hidden rounded-xl border border-slate-200 bg-white">
    <div class="overflow-x-auto">
    <table class="w-full text-sm">
        <thead class="bg-lebak-blue/5 text-left text-xs font-semibold uppercase tracking-wide text-lebak-blue">
            <tr>
                <th class="px-5 py-3">Nama</th>
                <th class="px-5 py-3">Email</th>
                <th class="px-5 py-3">Sekolah</th>
                <th class="px-5 py-3">Status</th>
                <th class="px-5 py-3">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @forelse ($akun as $user)
                <tr class="transition hover:bg-lebak-cream/60">
                    <td class="px-5 py-3 font-semibold text-slate-900">{{ $user->nama_lengkap }}</td>
                    <td class="px-5 py-3 text-slate-600">{{ $user->email }}</td>
                    <td class="px-5 py-3 text-slate-600">{{ $user->sekolah->nama_sekolah ?? '—' }}</td>
                    <td class="px-5 py-3">
                        <span class="chip {{ $user->is_active ? 'bg-lebak-green/20 text-[#1f6b48]' : 'bg-slate-100 text-slate-500' }}">
                            {{ $user->is_active ? 'Aktif' : 'Nonaktif' }}
                        </span>
                    </td>
                    <td class="px-5 py-3">
                        <div class="flex gap-2">
                            <form method="POST" action="{{ route('admin.pengaturan.akun.toggle', $user) }}"
                                  @if ($user->is_active) onsubmit="return confirm('Nonaktifkan akun {{ $user->nama_lengkap }}? Pengguna tidak akan bisa masuk sampai diaktifkan lagi.')" @endif>
                                @csrf @method('PATCH')
                                <button type="submit" class="{{ $user->is_active ? 'btn-warn' : 'btn-ok' }}">{{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</button>
                            </form>
                            <form method="POST" action="{{ route('admin.pengaturan.akun.reset', $user) }}" onsubmit="return confirm('Reset password {{ $user->nama_lengkap }}? Password lama tidak berlaku lagi.')">
                                @csrf
                                <button type="submit" class="btn-muted">Reset Password</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="px-5 py-10 text-center text-slate-400">Belum ada akun pengawas.</td></tr>
            @endforelse
        </tbody>
    </table>
    </div>
</div>

<div class="mt-4">{{ $akun->links() }}</div>
@endsection