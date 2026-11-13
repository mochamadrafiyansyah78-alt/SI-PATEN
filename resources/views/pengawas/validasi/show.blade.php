@extends('layouts.app')
@section('title', 'Validasi Data ATS')

@section('content')
<a href="{{ route('pengawas.validasi.index') }}" class="mb-4 inline-flex items-center gap-1 text-sm text-slate-500 hover:text-slate-700">&larr; Kembali ke Validasi Lapangan</a>

<div class="mb-5 flex flex-wrap items-center justify-between gap-3">
    <h1 class="text-2xl font-semibold text-slate-900">Validasi Lapangan: {{ $item->nama_siswa }}</h1>
    <a href="{{ route('pengawas.data-ats.edit', $item) }}" class="rounded-lg bg-lebak-blue px-4 py-2 text-sm font-bold text-white transition hover:bg-lebak-dark">Edit Data</a>
</div>

@include('partials.data-ats-detail', ['item' => $item, 'bisaKelolaSk' => false, 'bisaValidasi' => true])
@endsection
