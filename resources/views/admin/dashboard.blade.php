@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')
@php
    $jam = now()->hour;
    $sapaan = $jam < 11 ? 'Selamat pagi' : ($jam < 15 ? 'Selamat siang' : ($jam < 18 ? 'Selamat sore' : 'Selamat malam'));
    $totalLtm = $rekapKecamatan->sum('jumlah_ltm');
    $totalDo = $rekapKecamatan->sum('jumlah_do');

    // Disiapkan di sini karena @json() tidak boleh menerima array literal yang mengandung koma.
    $dataTahap = [
        $tahap['menunggu_verifikasi'],
        $tahap['menunggu_validasi'],
        $tahap['valid'],
        $tahap['tidak_valid'],
        $tahap['ditolak'],
    ];
@endphp

<section class="relative mb-6 overflow-hidden rounded-2xl bg-lebak-blue text-white">
    @include('partials.kawung')
    <div class="relative grid gap-8 p-8 lg:grid-cols-5">
        <div class="lg:col-span-3">
            <p class="text-sm text-white/70">{{ $sapaan }}, {{ auth()->user()->nama_lengkap }} · {{ now()->translatedFormat('l, d F Y') }}</p>
            <h1 class="mt-2 text-3xl font-extrabold leading-tight">Portal Data Anak Tidak Sekolah<br><span class="text-[#e3c97a]">Kabupaten Lebak</span></h1>
            <p class="mt-3 max-w-xl text-sm leading-relaxed text-white/80">{{ number_format($totalKeseluruhan, 0, ',', '.') }} data master tercatat dari seluruh kecamatan. Cari siswa atau pantau kasus yang butuh penanganan.</p>
            <form method="GET" action="{{ route('admin.data-ats.index') }}" class="mt-5 flex max-w-lg overflow-hidden rounded-xl bg-white p-1 shadow-lg">
                <input type="text" name="cari" placeholder="Cari nama atau NISN siswa..." class="min-w-0 flex-1 px-4 py-2.5 text-sm text-slate-800 focus:outline-none">
                <button class="rounded-lg bg-lebak-gold px-5 text-sm font-bold text-white transition hover:bg-[#5f4d21]">Cari</button>
            </form>
        </div>
        <div class="grid grid-cols-2 gap-3 self-end lg:col-span-2">
            <a href="{{ route('admin.data-ats.index') }}" class="rounded-xl bg-white/10 p-4 ring-1 ring-white/15 transition hover:bg-white/20"><p class="text-sm font-bold">Data ATS</p><p class="mt-0.5 text-xs text-white/70">Seluruh data siswa</p></a>
            <a href="{{ route('admin.peta-sebaran') }}" class="rounded-xl bg-white/10 p-4 ring-1 ring-white/15 transition hover:bg-white/20"><p class="text-sm font-bold">Peta Sebaran</p><p class="mt-0.5 text-xs text-white/70">Per kecamatan</p></a>
            <a href="{{ route('admin.laporan.index') }}" class="rounded-xl bg-white/10 p-4 ring-1 ring-white/15 transition hover:bg-white/20"><p class="text-sm font-bold">Laporan Bulanan</p><p class="mt-0.5 text-xs text-white/70">Rekap dan PDF</p></a>
        </div>
    </div>
    <div class="flex h-1.5"><div class="flex-1 bg-lebak-green"></div><div class="flex-1 bg-lebak-gold"></div><div class="flex-1 bg-white/40"></div></div>
</section>

<div class="mb-6 grid grid-cols-2 gap-4 lg:grid-cols-5">
    <div class="rounded-xl border border-slate-200 border-t-4 border-t-lebak-blue bg-white p-5">
        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Total Data ATS</p>
        <p class="mt-2 text-3xl font-extrabold text-lebak-blue">{{ number_format($totalKeseluruhan, 0, ',', '.') }}</p>
        <p class="mt-1 text-xs text-slate-400">Terverifikasi dan tervalidasi</p>
    </div>
    <div class="rounded-xl border border-slate-200 border-t-4 border-t-lebak-gold bg-white p-5">
        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Lulus Tidak Melanjutkan (LTM)</p>
        <p class="mt-2 text-3xl font-extrabold text-lebak-gold">{{ number_format($totalLtm, 0, ',', '.') }}</p>
        <p class="mt-1 text-xs text-slate-400">Selesai sekolah, tidak lanjut</p>
    </div>
    <div class="rounded-xl border border-slate-200 border-t-4 border-t-rose-500 bg-white p-5">
        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Putus Sekolah (DO)</p>
        <p class="mt-2 text-3xl font-extrabold text-rose-600">{{ number_format($totalDo, 0, ',', '.') }}</p>
        <p class="mt-1 text-xs text-slate-400">Berhenti di tengah jalan</p>
    </div>
    <a href="{{ route('admin.data-ats.index', ['status_verifikasi' => 'Menunggu Verifikasi']) }}" class="rounded-xl border border-slate-200 border-t-4 border-t-slate-400 bg-white p-5 transition hover:shadow-md">
        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Menunggu Verifikasi</p>
        <p class="mt-2 text-3xl font-extrabold text-slate-700">{{ number_format($tahap['menunggu_verifikasi'], 0, ',', '.') }}</p>
        <p class="mt-1 text-xs font-semibold text-lebak-blue">Lihat daftar &rarr;</p>
    </a>
    <a href="{{ route('admin.data-ats.index', ['status_verifikasi' => 'Menunggu Validasi']) }}" class="rounded-xl border border-slate-200 border-t-4 border-t-lebak-green bg-white p-5 transition hover:shadow-md">
        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Validasi Lapangan</p>
        <p class="mt-2 text-3xl font-extrabold text-[#2f8a62]">{{ number_format($tahap['menunggu_validasi'], 0, ',', '.') }}</p>
        <p class="mt-1 text-xs font-semibold text-lebak-blue">Menunggu pengawas &rarr;</p>
    </a>
</div>

@include('partials.alur-sistem', ['sorot' => 1])

<div class="mb-6 grid gap-5 lg:grid-cols-3">
    <section class="rounded-xl border border-slate-200 bg-white p-5 lg:col-span-2">
        <div class="mb-1 flex items-center gap-2"><span class="h-5 w-1 rounded bg-lebak-gold"></span><h2 class="text-base font-bold text-slate-900">Tren Data per Bulan</h2></div>
        <p class="mb-4 text-xs text-slate-500">12 bulan terakhir, data master (LTM dan DO), untuk melihat kenaikan atau penurunan kasus.</p>
        <div class="relative h-72"><canvas id="grafik-tren" role="img" aria-label="Diagram garis tren data LTM dan DO per bulan"></canvas></div>
    </section>

    <section class="rounded-xl border border-slate-200 bg-white p-5">
        <div class="mb-1 flex items-center gap-2"><span class="h-5 w-1 rounded bg-lebak-gold"></span><h2 class="text-base font-bold text-slate-900">Posisi Data dalam Alur</h2></div>
        <p class="mb-4 text-xs text-slate-500">Seluruh data menurut tahap verifikasi dan validasi.</p>
        <div class="relative h-72"><canvas id="grafik-tahap" role="img" aria-label="Diagram distribusi data menurut tahap alur"></canvas></div>
    </section>
</div>

<div class="grid gap-5 lg:grid-cols-5">
    <div class="space-y-5 lg:col-span-2">
        <section class="rounded-xl border border-slate-200 bg-white p-5">
            <div class="mb-4 flex items-center gap-2"><span class="h-5 w-1 rounded bg-lebak-gold"></span><h2 class="text-base font-bold text-slate-900">Prioritas Penjangkauan</h2></div>
            <div class="space-y-4">
                @foreach ($rekapPrioritas as $p)
                    @php
                        $warna = str_contains($p->prioritas_penjangkauan, '1') ? ['bg-rose-500', 'text-rose-600', 'bg-rose-100']
                            : (str_contains($p->prioritas_penjangkauan, '2') ? ['bg-lebak-gold', 'text-lebak-gold', 'bg-lebak-cream'] : ['bg-lebak-green', 'text-[#2f8a62]', 'bg-lebak-green/20']);
                        $persen = $totalKeseluruhan > 0 ? round($p->jumlah / $totalKeseluruhan * 100) : 0;
                    @endphp
                    <a href="{{ route('admin.data-ats.index', ['prioritas' => $p->prioritas_penjangkauan]) }}" class="block rounded-lg transition hover:bg-slate-50">
                        <div class="mb-1.5 flex items-center justify-between">
                            <span class="text-sm font-semibold text-slate-700">{{ $p->prioritas_penjangkauan }}</span>
                            <span class="text-sm font-extrabold {{ $warna[1] }}">{{ number_format($p->jumlah, 0, ',', '.') }} <span class="text-xs font-medium text-slate-400">({{ $persen }}%)</span></span>
                        </div>
                        <div class="h-2.5 w-full rounded-full {{ $warna[2] }}"><div class="h-2.5 rounded-full {{ $warna[0] }}" style="width: {{ $persen }}%"></div></div>
                    </a>
                @endforeach
            </div>
        </section>

        <section class="rounded-xl border border-slate-200 bg-white p-5">
            <div class="mb-4 flex items-center gap-2"><span class="h-5 w-1 rounded bg-lebak-gold"></span><h2 class="text-base font-bold text-slate-900">Ringkasan Early Warning</h2></div>
            <div class="grid grid-cols-3 gap-3 text-center">
                <a href="{{ route('admin.data-ats.index', ['level' => 'Merah']) }}" class="rounded-lg bg-rose-50 p-3 transition hover:bg-rose-100"><p class="text-2xl font-extrabold text-rose-600">{{ $ews['Merah'] ?? 0 }}</p><p class="text-xs font-semibold text-rose-700">Merah</p></a>
                <a href="{{ route('admin.data-ats.index', ['level' => 'Kuning']) }}" class="rounded-lg bg-amber-50 p-3 transition hover:bg-amber-100"><p class="text-2xl font-extrabold text-amber-600">{{ $ews['Kuning'] ?? 0 }}</p><p class="text-xs font-semibold text-amber-700">Kuning</p></a>
                <a href="{{ route('admin.data-ats.index', ['level' => 'Hijau']) }}" class="rounded-lg bg-lebak-green/15 p-3 transition hover:bg-lebak-green/30"><p class="text-2xl font-extrabold text-[#2f8a62]">{{ $ews['Hijau'] ?? 0 }}</p><p class="text-xs font-semibold text-[#1f6b48]">Hijau</p></a>
            </div>
            <p class="mt-4 text-xs leading-relaxed text-slate-500">Merah berarti perlu ditangani segera. Level dihitung otomatis dari status, prioritas, dan lama kasus belum ditangani.</p>
        </section>
    </div>

    <section class="rounded-xl border border-slate-200 bg-white p-5 lg:col-span-3">
        <div class="mb-1 flex items-center justify-between">
            <div class="flex items-center gap-2"><span class="h-5 w-1 rounded bg-lebak-gold"></span><h2 class="text-base font-bold text-slate-900">Penanganan per Kecamatan</h2></div>
            <a href="{{ route('admin.peta-sebaran') }}" class="text-xs font-semibold text-lebak-blue hover:underline">Lihat peta &rarr;</a>
        </div>
        <p class="mb-4 text-xs text-slate-500">10 kecamatan dengan data terbanyak, dipecah menurut status penanganan.</p>
        <div class="relative h-[26rem]"><canvas id="grafik-penanganan-kecamatan" role="img" aria-label="Diagram batang status penanganan per kecamatan"></canvas></div>
    </section>
</div>

@include('partials.chart-dasar')
<script>
(() => {
    // 1. Tren LTM vs DO
    const kanvasTren = ambilKanvas('grafik-tren');
    if (kanvasTren) {
        new Chart(kanvasTren, {
            type: 'line',
            data: {
                labels: @json($trenLabel),
                datasets: [
                    { label: 'LTM', data: @json($trenLtm), borderColor: '#77612A', backgroundColor: 'rgba(119,97,42,.12)', fill: true, tension: .3, pointRadius: 3 },
                    { label: 'DO', data: @json($trenDo), borderColor: '#e11d48', backgroundColor: 'rgba(225,29,72,.10)', fill: true, tension: .3, pointRadius: 3 },
                ],
            },
            options: {
                interaction: { mode: 'index', intersect: false },
                plugins: { legend: { position: 'bottom' } },
                scales: {
                    y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: '#eef2f0' } },
                    x: { grid: { display: false } },
                },
            },
        });
    }

    // 2. Posisi data dalam alur
    gambarDonat(
        'grafik-tahap',
        ['Menunggu verifikasi', 'Validasi lapangan', 'Valid (master)', 'Tidak valid', 'Ditolak verifikator'],
        @json($dataTahap),
        ['#94a3b8', '#C9A646', '#62B38B', '#e11d48', '#475569']
    );

    // 3. Penanganan per kecamatan (batang bertumpuk)
    const kanvasKecamatan = ambilKanvas('grafik-penanganan-kecamatan');
    if (kanvasKecamatan) {
        const labelKecamatan = @json($labelKecamatan);
        const dataPenanganan = @json($penangananKecamatan);
        const warnaPenanganan = {
            'Baru': '#94a3b8',
            'Dalam Proses': '#00508F',
            'Selesai - Kembali Sekolah': '#62B38B',
            'Selesai - Tidak Berhasil': '#e11d48',
        };

        if (labelKecamatan.length === 0) {
            kanvasKecamatan.parentElement.innerHTML = '<p class="flex h-full items-center justify-center text-sm text-slate-400">Belum ada data.</p>';
        } else {
            new Chart(kanvasKecamatan, {
                type: 'bar',
                data: {
                    labels: labelKecamatan,
                    datasets: Object.entries(dataPenanganan).map(([tahap, nilai]) => ({
                        label: tahap,
                        data: nilai,
                        backgroundColor: warnaPenanganan[tahap],
                        borderRadius: 3,
                    })),
                },
                options: {
                    indexAxis: 'y',
                    plugins: { legend: { position: 'bottom' } },
                    scales: {
                        x: { stacked: true, beginAtZero: true, ticks: { precision: 0 }, grid: { color: '#eef2f0' } },
                        y: { stacked: true, grid: { display: false } },
                    },
                },
            });
        }
    }
})();
</script>
@endsection
