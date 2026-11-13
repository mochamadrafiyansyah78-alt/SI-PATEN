@extends('layouts.app')
@section('title', 'Data Sekolah')

@section('content')
<div class="mb-6 flex flex-wrap items-center justify-between gap-3">
    <div>
        <div class="flex items-center gap-2"><span class="h-6 w-1 rounded bg-lebak-gold"></span><h1 class="text-2xl font-extrabold text-slate-900">Data Sekolah</h1></div>
        <p class="mt-1 text-sm text-slate-500">{{ $sekolah->total() }} sekolah terdaftar.</p>
    </div>
    <a href="{{ route('admin.pengaturan.akun') }}" class="btn-edit">Kelola Akun Pengawas &rarr;</a>
</div>

<form method="GET" class="mb-5 flex max-w-md overflow-hidden rounded-xl border border-slate-200 bg-white p-1">
    <input type="text" name="cari" value="{{ request('cari') }}" placeholder="Cari nama sekolah atau NPSN..." class="min-w-0 flex-1 px-3 py-2 text-sm focus:outline-none">
    <button class="rounded-lg bg-lebak-blue px-4 text-sm font-bold text-white transition hover:bg-lebak-dark">Cari</button>
</form>

<div class="overflow-hidden rounded-xl border border-slate-200 bg-white">
    <div class="overflow-x-auto">
    <table class="w-full text-sm">
        <thead class="bg-lebak-blue/5 text-left text-xs font-semibold uppercase tracking-wide text-lebak-blue">
            <tr>
                <th class="px-5 py-3">Nama Sekolah</th>
                <th class="px-5 py-3">NPSN</th>
                <th class="px-5 py-3">Kecamatan</th>
                <th class="px-5 py-3 text-right">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @forelse ($sekolah as $s)
                <tr class="transition hover:bg-lebak-cream/60">
                    <td class="px-5 py-3 font-semibold text-slate-900">{{ $s->nama_sekolah }}</td>
                    <td class="px-5 py-3 text-slate-600">{{ $s->npsn }}</td>
                    <td class="px-5 py-3 text-slate-600">
                        @if ($s->kecamatan)
                            {{ $s->kecamatan->nama_kecamatan }}
                        @else
                            <span class="font-medium text-lebak-gold">Luar Lebak · {{ $s->kecamatan_luar_nama }}, {{ $s->kabupaten_sekolah }}</span>
                        @endif
                    </td>
                    <td class="px-5 py-3">
                        <div class="flex justify-end"><a href="{{ route('admin.pengaturan.sekolah.edit', $s) }}" class="btn-edit">Edit</a></div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="4" class="px-5 py-10 text-center text-slate-400">Tidak ada sekolah yang cocok.</td></tr>
            @endforelse
        </tbody>
    </table>
    </div>
</div>

<div class="mt-4">{{ $sekolah->links() }}</div>
@endsection