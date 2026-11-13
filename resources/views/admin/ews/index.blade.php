@extends('layouts.app')
@section('title', 'Early Warning System — Kabupaten')

@section('content')
<div class="flex items-center gap-3 mb-6">
    <div class="w-11 h-11 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" /></svg>
    </div>
    <div>
        <h1 class="text-2xl font-semibold text-slate-900">Early Warning System — Kabupaten Lebak</h1>
        <p class="text-sm text-slate-500 mt-1">Rekap kasus berdasarkan tingkat risiko, lintas seluruh sekolah.</p>
    </div>
</div>

@if ($errors->any())
    <div class="mb-4 rounded-lg bg-rose-50 border border-rose-200 text-rose-700 text-sm px-4 py-3">{{ $errors->first() }}</div>
@endif

<div class="grid grid-cols-3 gap-4 mb-6">
    <a href="{{ route('admin.ews.index', ['level' => 'Merah']) }}" class="bg-white rounded-xl border {{ request('level') === 'Merah' ? 'border-rose-400 ring-2 ring-rose-100' : 'border-slate-200' }} p-5">
        <p class="text-sm text-rose-600 font-medium">Merah · Kritis</p>
        <p class="text-3xl font-semibold text-slate-900 mt-1">{{ $rekapLevel['Merah'] ?? 0 }}</p>
    </a>
    <a href="{{ route('admin.ews.index', ['level' => 'Kuning']) }}" class="bg-white rounded-xl border {{ request('level') === 'Kuning' ? 'border-amber-400 ring-2 ring-amber-100' : 'border-slate-200' }} p-5">
        <p class="text-sm text-amber-600 font-medium">Kuning · Waspada</p>
        <p class="text-3xl font-semibold text-slate-900 mt-1">{{ $rekapLevel['Kuning'] ?? 0 }}</p>
    </a>
    <a href="{{ route('admin.ews.index', ['level' => 'Hijau']) }}" class="bg-white rounded-xl border {{ request('level') === 'Hijau' ? 'border-emerald-400 ring-2 ring-emerald-100' : 'border-slate-200' }} p-5">
        <p class="text-sm text-emerald-600 font-medium">Hijau · Terpantau</p>
        <p class="text-3xl font-semibold text-slate-900 mt-1">{{ $rekapLevel['Hijau'] ?? 0 }}</p>
    </a>
</div>

<form method="GET" action="{{ route('admin.ews.index') }}" class="bg-white rounded-xl border border-slate-200 p-4 mb-5 flex flex-wrap gap-3 items-center">
    @if (request('level'))
        <input type="hidden" name="level" value="{{ request('level') }}">
        <span class="text-sm text-slate-500">Filter level: <strong>{{ request('level') }}</strong>
            <a href="{{ route('admin.ews.index', request()->except('level')) }}" class="text-rose-600 underline ml-1">hapus</a>
        </span>
    @endif
    <select name="kecamatan_id" class="border border-slate-300 rounded-lg px-3 py-2 text-sm bg-white" onchange="this.form.submit()">
        <option value="">Semua Kecamatan</option>
        @foreach ($kecamatan as $k)
            <option value="{{ $k->id }}" @selected(request('kecamatan_id') === $k->id)>{{ $k->nama_kecamatan }}</option>
        @endforeach
    </select>
    <select name="status_penanganan" class="border border-slate-300 rounded-lg px-3 py-2 text-sm bg-white" onchange="this.form.submit()">
        <option value="">Semua Status Penanganan</option>
        <option value="Baru" @selected(request('status_penanganan') === 'Baru')>Baru</option>
        <option value="Dalam Proses" @selected(request('status_penanganan') === 'Dalam Proses')>Dalam Proses</option>
        <option value="Selesai - Kembali Sekolah" @selected(request('status_penanganan') === 'Selesai - Kembali Sekolah')>Selesai - Kembali Sekolah</option>
        <option value="Selesai - Tidak Berhasil" @selected(request('status_penanganan') === 'Selesai - Tidak Berhasil')>Selesai - Tidak Berhasil</option>
    </select>
</form>

<div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
    <table class="w-full text-sm">
        <thead class="text-left text-slate-500 border-b border-slate-100">
            <tr>
                <th class="px-5 py-3 font-medium">Nama Siswa</th>
                <th class="px-5 py-3 font-medium">Sekolah</th>
                <th class="px-5 py-3 font-medium">Kecamatan</th>
                <th class="px-5 py-3 font-medium">Level</th>
                <th class="px-5 py-3 font-medium">Prioritas</th>
                <th class="px-5 py-3 font-medium">Kelola Penanganan</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($data as $item)
                @php
                    $warnaLevel = match ($item->ews_level) {
                        'Merah' => 'bg-rose-50 text-rose-700',
                        'Kuning' => 'bg-amber-50 text-amber-700',
                        default => 'bg-emerald-50 text-emerald-700',
                    };
                @endphp
                <tr class="border-b border-slate-50 last:border-0 hover:bg-slate-50/60 align-top">
                    <td class="px-5 py-3">
                        <a href="{{ route('admin.data-ats.show', $item) }}" class="font-medium text-slate-900 hover:text-teal-700 hover:underline">{{ $item->nama_siswa }}</a>
                    </td>
                    <td class="px-5 py-3 text-slate-600">{{ $item->sekolah->nama_sekolah ?? '—' }}</td>
                    <td class="px-5 py-3 text-slate-600">{{ $item->kecamatanDomisili->nama_kecamatan ?? '—' }}</td>
                    <td class="px-5 py-3">
                        <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium {{ $warnaLevel }}">{{ $item->ews_level }} ({{ $item->ews_score }})</span>
                    </td>
                    <td class="px-5 py-3 text-slate-600">{{ $item->prioritas_penjangkauan }}</td>
                    <td class="px-5 py-3">
                        <form method="POST" action="{{ route('admin.ews.update-status', $item) }}" enctype="multipart/form-data" class="flex flex-col gap-1 max-w-xs">
                            @csrf @method('PUT')
                            <select name="status_penanganan" class="border border-slate-300 rounded-lg px-2 py-1 text-xs">
                                @foreach (['Baru', 'Dalam Proses', 'Selesai - Kembali Sekolah', 'Selesai - Tidak Berhasil'] as $opsi)
                                    <option value="{{ $opsi }}" @selected($item->status_penanganan === $opsi)>{{ $opsi }}</option>
                                @endforeach
                            </select>
                            <select name="ditangani_oleh" class="border border-slate-300 rounded-lg px-2 py-1 text-xs">
                                <option value="">Belum ditentukan</option>
                                @foreach ($penanggungJawab as $pj)
                                    <option value="{{ $pj->id }}" @selected($item->ditangani_oleh === $pj->id)>{{ $pj->nama_lengkap }}</option>
                                @endforeach
                            </select>
                            <input type="text" name="catatan_penanganan" placeholder="Catatan (opsional)" class="border border-slate-300 rounded-lg px-2 py-1 text-xs">
                            <input type="file" name="lampiran" accept=".jpg,.jpeg,.png,.pdf" class="text-xs">
                            <button class="bg-teal-700 hover:bg-teal-800 text-white text-xs px-3 py-1 rounded-lg">Simpan</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-5 py-10 text-center text-slate-400">Tidak ada kasus yang cocok dengan filter.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">
    {{ $data->links() }}
</div>
@endsection