@extends('layouts.app')
@section('title', 'Detail Siswa — ' . $item->nama_siswa)

@section('content')
<a href="{{ route('admin.data-ats.index') }}" class="text-sm text-slate-500 hover:text-slate-700 inline-flex items-center gap-1 mb-4">
    &larr; Kembali ke Data Master ATS
</a>

@include('partials.data-ats-detail')
@endsection
