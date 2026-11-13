@extends('layouts.app')
@section('title', 'Edit Data ATS')

@section('content')
<a href="{{ route('pengawas.data-ats.index') }}" class="text-sm text-slate-500 hover:text-slate-700 inline-flex items-center gap-1 mb-4">
    &larr; Kembali ke Data ATS
</a>

<h1 class="text-2xl font-semibold text-slate-900 mb-6">Edit Data ATS — {{ $item->nama_siswa }}</h1>

<form method="POST" action="{{ route('pengawas.data-ats.update', $item) }}" class="bg-white rounded-xl border border-slate-200 p-6 max-w-3xl">
    @method('PUT')
    @include('pengawas.data-ats._form')
</form>
@endsection