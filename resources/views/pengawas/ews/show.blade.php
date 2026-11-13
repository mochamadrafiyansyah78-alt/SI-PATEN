@extends('layouts.app')
@section('title', 'Tindakan — ' . $item->nama_siswa)

@section('content')
<a href="{{ url()->previous() === url()->current() ? route('pengawas.data-ats.index') : url()->previous() }}" class="mb-4 inline-flex items-center gap-1 text-sm text-slate-500 hover:text-slate-700">&larr; Kembali</a>

<form method="POST" action="{{ route('pengawas.ews.update-status', $item) }}" enctype="multipart/form-data" class="mb-5 rounded-xl border border-lebak-blue/30 bg-white p-5">
    @csrf @method('PUT')
    <h2 class="mb-3 text-base font-bold text-slate-900">Tindakan Penanganan</h2>
    @if ($errors->any())<div class="mb-3 rounded bg-rose-50 px-3 py-2 text-sm text-rose-700">{{ $errors->first() }}</div>@endif
    <div class="grid gap-3 md:grid-cols-3">
        <select name="status_penanganan" class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm">
            @foreach (['Baru', 'Dalam Proses', 'Selesai - Kembali Sekolah', 'Selesai - Tidak Berhasil'] as $o)
                <option value="{{ $o }}" @selected(($item->status_penanganan ?? 'Baru') === $o)>{{ $o }}</option>
            @endforeach
        </select>
        <input type="text" name="catatan_penanganan" maxlength="1000" placeholder="Catatan (opsional)" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
        <input type="file" name="lampiran" accept=".jpg,.jpeg,.png,.pdf" class="text-sm">
    </div>
    <button type="submit" class="mt-3 rounded-lg bg-lebak-blue px-5 py-2 text-sm font-bold text-white hover:bg-lebak-dark">Simpan Tindakan</button>
</form>

@include('partials.data-ats-detail')
@endsection
