@extends('layouts.app')
@section('title', 'Laporan Bulanan')

@section('content')
<div class="flex items-center justify-between mb-4">
    <h1 class="text-2xl font-bold">Laporan Bulanan</h1>
    <a href="{{ route('admin.laporan.export-pdf', ['bulan' => $bulan, 'tahun' => $tahun]) }}"
       class="bg-red-700 text-white text-sm px-4 py-2 rounded">
        Export PDF
    </a>
</div>

<form method="GET" class="flex items-center gap-3 mb-6">
    <select name="bulan" onchange="this.form.submit()" class="border rounded px-3 py-2 text-sm">
        @foreach (range(1, 12) as $b)
            <option value="{{ $b }}" @selected($bulan === $b)>{{ \Carbon\Carbon::create()->month($b)->translatedFormat('F') }}</option>
        @endforeach
    </select>
    <select name="tahun" onchange="this.form.submit()" class="border rounded px-3 py-2 text-sm">
        @foreach (range(now()->year, now()->year - 3) as $t)
            <option value="{{ $t }}" @selected($tahun === $t)>{{ $t }}</option>
        @endforeach
    </select>
</form>

<div class="grid grid-cols-3 gap-4 mb-6">
    <div class="bg-white rounded-xl border border-slate-200 p-4">
        <p class="text-sm text-slate-500">Total Data (bulan ini)</p>
        <p class="text-3xl font-bold text-slate-900">{{ $rekapKecamatan->sum('total') }}</p>
    </div>
    <div class="bg-white rounded-xl border border-slate-200 p-4">
        <p class="text-sm text-slate-500">LTM</p>
        <p class="text-3xl font-bold text-amber-600">{{ $rekapKecamatan->sum('jumlah_ltm') }}</p>
    </div>
    <div class="bg-white rounded-xl border border-slate-200 p-4">
        <p class="text-sm text-slate-500">DO</p>
        <p class="text-3xl font-bold text-rose-600">{{ $rekapKecamatan->sum('jumlah_do') }}</p>
    </div>
</div>

@php
    $top10Kecamatan = $rekapKecamatan->sortByDesc('total')->take(10)->values();
    $maxTotalKecamatan = $top10Kecamatan->max('total') ?: 1;

    $totalPenanganan = $rekapStatusPenanganan->sum('jumlah') ?: 1;
    $warnaStatus = [
        'Baru' => '#94a3b8',
        'Dalam Proses' => '#fbbf24',
        'Selesai - Kembali Sekolah' => '#34d399',
        'Selesai - Tidak Berhasil' => '#f87171',
    ];
    $radius = 60;
    $lingkar = 2 * M_PI * $radius;
    $offsetSegmen = 0;
@endphp

<div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-6">
    <div class="lg:col-span-2 bg-white rounded-xl border border-slate-200 p-5">
        <h2 class="text-base font-semibold text-slate-900 mb-4">Data ATS per Kecamatan (10 Tertinggi)</h2>
        <div class="space-y-3">
            @forelse ($top10Kecamatan as $k)
                <div>
                    <div class="flex justify-between text-xs text-slate-600 mb-1">
                        <span>{{ $k->nama_kecamatan }}</span>
                        <span class="font-medium">{{ $k->total }}</span>
                    </div>
                    <div class="w-full bg-slate-100 rounded-full h-3 flex overflow-hidden">
                        <div style="width: {{ $k->total > 0 ? ($k->jumlah_ltm / $maxTotalKecamatan * 100) : 0 }}%" class="bg-amber-500 h-3"></div>
                        <div style="width: {{ $k->total > 0 ? ($k->jumlah_do / $maxTotalKecamatan * 100) : 0 }}%" class="bg-rose-500 h-3"></div>
                    </div>
                </div>
            @empty
                <p class="text-sm text-slate-400">Belum ada data bulan ini.</p>
            @endforelse
        </div>
        <div class="flex gap-4 mt-4 text-xs text-slate-500">
            <span class="flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-full bg-amber-500 inline-block"></span> LTM</span>
            <span class="flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-full bg-rose-500 inline-block"></span> DO</span>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 p-5">
        <h2 class="text-base font-semibold text-slate-900 mb-4">Status Penanganan</h2>
        <div class="flex items-center justify-center">
            <svg viewBox="0 0 160 160" class="w-40 h-40 -rotate-90">
                <circle cx="80" cy="80" r="{{ $radius }}" fill="none" stroke="#e2e8f0" stroke-width="20" />
                @foreach ($rekapStatusPenanganan as $s)
                    @php
                        $persen = $s->jumlah / $totalPenanganan;
                        $panjang = $persen * $lingkar;
                        $warna = $warnaStatus[$s->status_penanganan] ?? '#cbd5e1';
                    @endphp
                    <circle cx="80" cy="80" r="{{ $radius }}" fill="none" stroke="{{ $warna }}" stroke-width="20"
                            stroke-dasharray="{{ $panjang }} {{ $lingkar - $panjang }}"
                            stroke-dashoffset="{{ -$offsetSegmen }}" />
                    @php $offsetSegmen += $panjang; @endphp
                @endforeach
            </svg>
        </div>
        <div class="flex flex-col gap-1.5 mt-4 text-xs text-slate-500">
            @forelse ($rekapStatusPenanganan as $s)
                <span class="flex items-center gap-1.5">
                    <span class="w-2.5 h-2.5 rounded-full inline-block" style="background: {{ $warnaStatus[$s->status_penanganan] ?? '#cbd5e1' }}"></span>
                    {{ $s->status_penanganan ?? 'Belum Ditangani' }} ({{ $s->jumlah }})
                </span>
            @empty
                <p class="text-slate-400">Belum ada data.</p>
            @endforelse
        </div>
    </div>
</div>

<h2 class="text-lg font-semibold mb-2">Kasus Mengendap (&gt;14 hari belum ditangani)</h2>
<table class="w-full bg-white rounded shadow text-sm">
    <thead class="bg-yellow-100 text-left">
        <tr><th class="p-2">Nama Siswa</th><th class="p-2">Sekolah</th><th class="p-2">Kecamatan</th><th class="p-2">Hari Mengendap</th></tr>
    </thead>
    <tbody>
        @forelse ($kasusMengendap as $k)
            <tr class="border-t">
                <td class="p-2">{{ $k->nama_siswa }}</td>
                <td class="p-2">{{ $k->sekolah->nama_sekolah }}</td>
                <td class="p-2">{{ $k->kecamatanDomisili->nama_kecamatan }}</td>
                <td class="p-2 font-semibold text-red-600">{{ $k->hari_mengendap }} hari</td>
            </tr>
        @empty
            <tr><td class="p-2 text-gray-400" colspan="4">Tidak ada kasus mengendap.</td></tr>
        @endforelse
    </tbody>
</table>
@endsection