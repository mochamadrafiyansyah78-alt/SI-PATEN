@extends('layouts.app')
@section('title', 'Detail — ' . $item->nama_siswa)

@section('content')
<a href="{{ route('verifikator.verifikasi.index') }}" class="mb-4 inline-flex items-center gap-1 text-sm text-slate-500 hover:text-slate-700">&larr; Kembali ke Verifikasi</a>
@include('partials.data-ats-detail')
@endsection
