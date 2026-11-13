@extends('layouts.app')
@section('title', 'Dashboard Pengawas')

@section('content')
@php
    $jam = now()->hour;
    $sapaan = $jam < 11 ? 'Selamat pagi' : ($jam < 15 ? 'Selamat siang' : ($jam < 18 ? 'Selamat sore' : 'Selamat malam'));

    $kartu = [
        ['label' => 'Total Data ATS', 'jumlah' => $totalSiswa, 'tautan' => $parameterSekolah,
         'garis' => 'border-t-lebak-blue', 'angka' => 'text-lebak-blue',
         'catatan' => "LTM {$totalLtm} · DO {$totalDo} · Se-Kabupaten " . number_format($totalKabupaten, 0, ',', '.')],
        ['label' => 'Prioritas 1', 'jumlah' => $jumlahPrioritas['Prioritas 1'], 'tautan' => $parameterSekolah + ['prioritas' => 'Prioritas 1'],
         'garis' => 'border-t-rose-500', 'angka' => 'text-rose-600', 'catatan' => 'Perlu segera ditangani'],
        ['label' => 'Prioritas 2', 'jumlah' => $jumlahPrioritas['Prioritas 2'], 'tautan' => $parameterSekolah + ['prioritas' => 'Prioritas 2'],
         'garis' => 'border-t-lebak-gold', 'angka' => 'text-lebak-gold', 'catatan' => 'Penanganan menengah'],
        ['label' => 'Prioritas 3', 'jumlah' => $jumlahPrioritas['Prioritas 3'], 'tautan' => $parameterSekolah + ['prioritas' => 'Prioritas 3'],
         'garis' => 'border-t-lebak-green', 'angka' => 'text-[#2f8a62]', 'catatan' => 'Pemantauan berkala'],
    ];
@endphp

<section class="relative mb-6 overflow-hidden rounded-2xl bg-lebak-blue text-white">
    @include('partials.kawung')
    <div class="relative flex flex-wrap items-end justify-between gap-5 p-8">
        <div>
            <p class="text-sm text-white/70">{{ $sapaan }}, {{ auth()->user()->nama_lengkap }} · {{ now()->translatedFormat('l, d F Y') }}</p>
            <h1 class="mt-2 text-3xl font-extrabold leading-tight">Dashboard Pengawas Sekolah</h1>
            <p class="mt-2 inline-block rounded-md bg-white/10 px-3 py-1 text-sm font-medium text-[#e3c97a]">{{ auth()->user()->sekolah->nama_sekolah ?? 'Sekolah belum ditentukan' }}</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('pengawas.validasi.index') }}" class="rounded-lg bg-white px-4 py-2.5 text-sm font-bold text-lebak-blue transition hover:bg-lebak-cream">Validasi Lapangan</a>
            <a href="{{ route('pengawas.data-ats.index') }}" class="rounded-lg px-4 py-2.5 text-sm font-bold text-white ring-1 ring-white/40 transition hover:bg-white/10">Data ATS Kabupaten</a>
            <a href="{{ route('pengawas.mutasi.create') }}" class="rounded-lg px-4 py-2.5 text-sm font-bold text-white ring-1 ring-white/40 transition hover:bg-white/10">+ Catat Mutasi</a>
        </div>
    </div>
    <div class="flex h-1.5"><div class="flex-1 bg-lebak-green"></div><div class="flex-1 bg-lebak-gold"></div><div class="flex-1 bg-white/40"></div></div>
</section>

@if ($menungguValidasi > 0)
    <a href="{{ route('pengawas.validasi.index') }}" class="mb-6 flex items-center justify-between gap-3 rounded-xl border border-amber-200 bg-amber-50 px-5 py-3 text-sm text-amber-800 transition hover:bg-amber-100">
        <span><strong>{{ $menungguValidasi }}</strong> data siswa sekolah Anda menunggu validasi lapangan.</span>
        <span class="font-bold">Validasi sekarang &rarr;</span>
    </a>
@endif

<div class="mb-6 grid grid-cols-2 gap-4 lg:grid-cols-4">
    @foreach ($kartu as $k)
        <a href="{{ route('pengawas.data-ats.index', $k['tautan']) }}"
           class="group rounded-xl border border-slate-200 border-t-4 {{ $k['garis'] }} bg-white p-5 transition hover:-translate-y-0.5 hover:shadow-md">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ $k['label'] }}</p>
            <p class="mt-2 text-3xl font-extrabold {{ $k['angka'] }}">{{ number_format($k['jumlah'], 0, ',', '.') }}</p>
            <p class="mt-1 text-xs text-slate-500">{{ $k['catatan'] }}</p>
            <p class="mt-2 text-xs font-semibold text-lebak-blue opacity-0 transition group-hover:opacity-100">Lihat daftar &rarr;</p>
        </a>
    @endforeach
</div>

@include('partials.alur-sistem', ['sorot' => 3])

<div class="mb-6 grid gap-5 lg:grid-cols-5">
    <section class="rounded-xl border border-slate-200 bg-white p-5 lg:col-span-2">
        <div class="mb-1 flex items-center gap-2"><span class="h-5 w-1 rounded bg-lebak-gold"></span><h2 class="text-base font-bold text-slate-900">Distribusi Validasi</h2></div>
        <p class="mb-3 text-xs text-slate-500">Data sekolah Anda yang sudah lolos verifikator.</p>
        <div class="relative h-64"><canvas id="grafik-validasi" role="img" aria-label="Diagram distribusi validasi data sekolah"></canvas></div>
    </section>

    <section class="rounded-xl border border-slate-200 bg-white p-5 lg:col-span-3">
        <div class="mb-1 flex items-center gap-2"><span class="h-5 w-1 rounded bg-lebak-gold"></span><h2 class="text-base font-bold text-slate-900">Status Penanganan</h2></div>
        <p class="mb-3 text-xs text-slate-500">Siswa master sekolah Anda menurut tahap penanganan.</p>
        <div class="relative h-64"><canvas id="grafik-penanganan" role="img" aria-label="Diagram status penanganan siswa"></canvas></div>
    </section>
</div>

<section class="overflow-hidden rounded-xl border border-slate-200 bg-white">
    <div class="flex items-center justify-between border-b border-slate-100 p-5">
        <div>
            <div class="flex items-center gap-2"><span class="h-5 w-1 rounded bg-rose-500"></span><h2 class="text-base font-bold text-slate-900">Prioritas 1: Perlu Segera Ditangani</h2></div>
            <p class="mt-1 text-xs text-slate-500">10 data terbaru. Klik nama siswa untuk menindaklanjuti.</p>
        </div>
        <div class="flex shrink-0 gap-3 text-xs font-semibold text-lebak-blue">
            <a href="{{ route('pengawas.data-ats.index', $parameterSekolah + ['prioritas' => 'Prioritas 1']) }}" class="hover:underline">Lihat semua &rarr;</a>
            <a href="{{ route('pengawas.ews.index') }}" class="hover:underline">Early Warning &rarr;</a>
        </div>
    </div>
    <div class="divide-y divide-slate-100">
        @forelse ($siswaPrioritas1 as $siswa)
            <a href="{{ route('pengawas.ews.show', $siswa) }}" class="flex items-center justify-between gap-4 px-5 py-3.5 transition hover:bg-lebak-cream">
                <div>
                    <p class="text-sm font-semibold text-slate-900">{{ $siswa->nama_siswa }}</p>
                    <p class="text-xs text-slate-500">{{ $siswa->alasan_approval ?? 'Alasan belum dicatat' }}</p>
                </div>
                <span class="chip shrink-0 {{ $siswa->status === 'DO' ? 'bg-rose-50 text-rose-700' : 'bg-amber-50 text-amber-700' }}">{{ $siswa->status }}</span>
            </a>
        @empty
            <p class="px-5 py-10 text-center text-sm text-slate-400">Tidak ada siswa Prioritas 1 saat ini.</p>
        @endforelse
    </div>
</section>

@include('partials.chart-dasar')
<script>
(() => {
    gambarDonat(
        'grafik-validasi',
        ['Valid (masuk master)', 'Menunggu validasi', 'Tidak valid'],
        @json($dataValidasi),
        ['#62B38B', '#C9A646', '#e11d48']
    );

    const kanvasPenanganan = ambilKanvas('grafik-penanganan');
    if (kanvasPenanganan) {
        const nilaiPenanganan = @json($dataPenanganan);
        const totalPenanganan = nilaiPenanganan.reduce((jumlah, nilai) => jumlah + nilai, 0);

        if (totalPenanganan === 0) {
            kanvasPenanganan.parentElement.innerHTML = '<p class="flex h-full items-center justify-center text-sm text-slate-400">Belum ada data.</p>';
        } else {
            new Chart(kanvasPenanganan, {
                type: 'bar',
                data: {
                    labels: @json($labelPenanganan),
                    datasets: [{
                        data: nilaiPenanganan,
                        backgroundColor: ['#94a3b8', '#00508F', '#62B38B', '#e11d48'],
                        borderRadius: 6,
                        maxBarThickness: 28,
                    }],
                },
                options: {
                    indexAxis: 'y',
                    plugins: { legend: { display: false } },
                    scales: {
                        x: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: '#eef2f0' } },
                        y: { grid: { display: false } },
                    },
                },
            });
        }
    }
})();
</script>
@endsection
