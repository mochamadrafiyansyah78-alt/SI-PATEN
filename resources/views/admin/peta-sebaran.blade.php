@extends('layouts.app')
@section('title', 'Peta Sebaran ATS')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-semibold text-slate-900">Peta Sebaran ATS — Kabupaten Lebak</h1>
    <p class="text-sm text-slate-500 mt-1">Ukuran dan warna titik menunjukkan tingkat kepadatan & urgensi kasus per kecamatan.</p>
</div>

@if ($jumlahBelumBerkoordinat > 0)
    <div class="mb-4 rounded-lg bg-amber-50 border border-amber-200 text-amber-800 px-4 py-3 text-sm">
        {{ $jumlahBelumBerkoordinat }} kecamatan belum memiliki koordinat.
        Jalankan <code class="bg-white px-1.5 py-0.5 rounded border border-amber-200">php artisan geo:kecamatan</code> di server untuk mengisi otomatis.
    </div>
@endif

<div class="grid grid-cols-3 gap-4 mb-6">
    <div class="bg-white rounded-2xl border border-slate-200 p-5">
        <div class="flex items-center gap-3 mb-2">
            <div class="w-10 h-10 rounded-xl bg-teal-50 text-teal-600 flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9 6.75V15m6-6v8.25m.503 3.498l4.875-2.437c.381-.19.622-.58.622-1.006V4.82c0-.836-.88-1.38-1.628-1.006l-3.869 1.934c-.317.159-.69.159-1.006 0L9.503 3.252a1.125 1.125 0 00-1.006 0L3.622 5.689C3.24 5.88 3 6.27 3 6.695V19.18c0 .836.88 1.38 1.628 1.006l3.869-1.934c.317-.159.69-.159 1.006 0l4.994 2.497c.317.158.69.158 1.006 0z" /></svg>
            </div>
            <p class="text-sm text-slate-500 font-medium">Titik Kecamatan</p>
        </div>
        <p class="text-3xl font-bold text-slate-900">{{ $titikSebaran->count() }}</p>
    </div>
    <div class="bg-white rounded-2xl border border-slate-200 p-5">
        <div class="flex items-center gap-3 mb-2">
            <div class="w-10 h-10 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" /></svg>
            </div>
            <p class="text-sm text-slate-500 font-medium">Total Prioritas 1</p>
        </div>
        <p class="text-3xl font-bold text-rose-600">{{ $titikSebaran->sum('prioritas1') }}</p>
    </div>
    <div class="bg-white rounded-2xl border border-slate-200 p-5">
        <div class="flex items-center gap-3 mb-2">
            <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
            </div>
            <p class="text-sm text-slate-500 font-medium">Total DO</p>
        </div>
        <p class="text-3xl font-bold text-amber-600">{{ $titikSebaran->sum('do') }}</p>
    </div>
</div>

<form method="GET" class="bg-white rounded-xl border border-slate-200 p-4 mb-5 flex flex-wrap items-center gap-3">
    <select name="prioritas" onchange="this.form.submit()" class="border border-slate-300 rounded-lg px-3 py-2 text-sm bg-white">
        <option value="">Semua Prioritas</option>
        @foreach (['Prioritas 1', 'Prioritas 2', 'Prioritas 3'] as $p)
            <option value="{{ $p }}" @selected($prioritas === $p)>{{ $p }}</option>
        @endforeach
    </select>

    <select name="status" onchange="this.form.submit()" class="border border-slate-300 rounded-lg px-3 py-2 text-sm bg-white">
        <option value="">Semua Status</option>
        <option value="LTM" @selected($status === 'LTM')>LTM</option>
        <option value="DO" @selected($status === 'DO')>DO</option>
    </select>

    @if ($prioritas || $status)
        <a href="{{ route('admin.peta-sebaran') }}" class="text-sm text-rose-600 hover:underline">Reset filter</a>
    @endif
</form>

<div class="bg-white rounded-2xl border border-slate-200 p-3">
    <div id="peta-sebaran" class="rounded-xl overflow-hidden" style="height: 560px;"></div>
    <div class="flex flex-wrap gap-4 px-2 pt-3 text-xs text-slate-500">
        <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-red-500 inline-block"></span> Ada kasus Prioritas 1</span>
        <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-orange-500 inline-block"></span> Lebih dari 20 kasus</span>
        <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-green-500 inline-block"></span> Kasus relatif sedikit</span>
        <span class="text-slate-400">· Ukuran titik mengikuti jumlah total kasus</span>
    </div>
</div>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.min.css" />
<script src="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.min.js"></script>
<script>
    const map = L.map('peta-sebaran').setView([-6.75, 106.20], 10); // centroid Kab. Lebak

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors',
        maxZoom: 18,
    }).addTo(map);

    const titikSebaran = @json($titikSebaran);

    titikSebaran.forEach(function (titik) {
        const radius = 6 + Math.min(titik.total, 50) * 0.6;

        L.circleMarker([titik.lat, titik.lng], {
            radius: radius,
            color: titik.warna,
            fillColor: titik.warna,
            fillOpacity: 0.6,
        })
        .bindPopup(
            `<strong>${titik.nama}</strong><br>` +
            `Total ATS: ${titik.total}<br>` +
            `Prioritas 1: ${titik.prioritas1}<br>` +
            `Putus Sekolah (DO): ${titik.do}`
        )
        .addTo(map);
    });
</script>
@endsection