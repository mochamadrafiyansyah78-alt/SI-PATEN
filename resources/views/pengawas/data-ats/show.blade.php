@extends('layouts.app')
@section('title', 'Detail Siswa — ' . $item->nama_siswa)

@section('content')
<div class="flex items-center justify-between mb-4">
    <a href="{{ route('pengawas.data-ats.index') }}" class="text-sm text-slate-500 hover:text-slate-700 inline-flex items-center gap-1">
        &larr; Kembali ke Data ATS
    </a>
    <div class="flex gap-2">
        <a href="{{ route('pengawas.data-ats.edit', $item) }}" class="text-sm bg-teal-700 hover:bg-teal-800 text-white rounded-lg px-4 py-2 font-medium">
            Edit Data
        </a>
        <form method="POST" action="{{ route('pengawas.data-ats.destroy', $item) }}" onsubmit="return confirm('Hapus data {{ $item->nama_siswa }}? Tindakan ini tidak bisa dibatalkan.')">
            @csrf
            @method('DELETE')
            <button type="submit" class="text-sm border border-rose-300 text-rose-600 hover:bg-rose-50 rounded-lg px-4 py-2 font-medium">
                Hapus
            </button>
        </form>
    </div>
</div>

@include('partials.data-ats-detail')
@endsection