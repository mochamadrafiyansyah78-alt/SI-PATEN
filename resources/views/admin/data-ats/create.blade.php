@extends('layouts.app')
@section('title', 'Input Data ATS')

@section('content')
<a href="{{ route('admin.data-ats.index') }}" class="mb-4 inline-flex items-center gap-1 text-sm text-slate-500 hover:text-slate-700">&larr; Kembali ke Data ATS</a>
<h1 class="mb-1 text-2xl font-semibold text-slate-900">Input Data ATS</h1>
<p class="mb-6 text-sm text-slate-500">Data baru masuk antrean verifikasi dan tampil di Data ATS setelah disetujui verifikator.</p>

<form method="POST" action="{{ route('admin.data-ats.store') }}" class="max-w-3xl rounded-xl border border-slate-200 bg-white p-6">
    @include('pengawas.data-ats._form', ['batal' => route('admin.data-ats.index')])
</form>
@endsection
