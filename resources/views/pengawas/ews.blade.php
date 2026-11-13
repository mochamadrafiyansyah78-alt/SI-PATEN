@extends('layouts.app')
@section('title', 'Early Warning System')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-semibold text-slate-900">Early Warning System</h1>
    <p class="text-sm text-slate-500 mt-1">Pantau dan kelola status penanganan siswa ATS di sekolah Anda.</p>
</div>

<form method="GET" class="mb-4">
    <select name="level" onchange="this.form.submit()" class="border border-slate-300 rounded-lg px-3 py-2 text-sm bg-white">
        <option value="">Semua Level</option>
        <option value="Merah" @selected($level === 'Merah')>Merah (Kritis)</option>
        <option value="Kuning" @selected($level === 'Kuning')>Kuning (Waspada)</option>
        <option value="Hijau" @selected($level === 'Hijau')>Hijau (Terpantau)</option>
    </select>
</form>

@if ($errors->any())
    <div class="mb-3 rounded bg-red-50 text-red-700 text-sm px-3 py-2">{{ $errors->first() }}</div>
@endif

<div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
    <table class="w-full text-sm">
        <thead class="text-left text-slate-500 border-b border-slate-100">
            <tr>
                <th class="px-5 py-3 font-medium">Nama Siswa</th>
                <th class="px-5 py-3 font-medium">Level</th>
                <th class="px-5 py-3 font-medium">Skor</th>
                <th class="px-5 py-3 font-medium">Kelola Penanganan</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($daftarKasus as $kasus)
                @php
                    $warnaLevel = match ($kasus->ews_level) {
                        'Merah' => 'bg-rose-50 text-rose-700',
                        'Kuning' => 'bg-amber-50 text-amber-700',
                        default => 'bg-emerald-50 text-emerald-700',
                    };
                @endphp
                <tr class="border-b border-slate-50 last:border-0 align-top">
                    <td class="px-5 py-3">
                        <a href="{{ route('pengawas.data-ats.show', $kasus) }}" class="font-medium text-slate-900 hover:text-teal-700 hover:underline">{{ $kasus->nama_siswa }}</a>
                    </td>
                    <td class="px-5 py-3">
                        <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium {{ $warnaLevel }}">{{ $kasus->ews_level }}</span>
                    </td>
                    <td class="px-5 py-3 text-slate-600">{{ $kasus->ews_score }}</td>
                    <td class="px-5 py-3">
                        <form method="POST" action="{{ route('pengawas.ews.update-status', $kasus) }}" enctype="multipart/form-data" class="flex flex-col gap-1 max-w-xs">
                            @csrf
                            @method('PUT')
                            <select name="status_penanganan" class="border border-slate-300 rounded-lg px-2 py-1 text-xs">
                                @foreach (['Baru', 'Dalam Proses', 'Selesai - Kembali Sekolah', 'Selesai - Tidak Berhasil'] as $opsi)
                                    <option value="{{ $opsi }}" @selected($kasus->status_penanganan === $opsi)>{{ $opsi }}</option>
                                @endforeach
                            </select>
                            <input type="text" name="catatan_penanganan" placeholder="Catatan (opsional)" class="border border-slate-300 rounded-lg px-2 py-1 text-xs">
                            <input type="file" name="lampiran" accept=".jpg,.jpeg,.png,.pdf" class="text-xs">
                            <button class="bg-teal-700 hover:bg-teal-800 text-white text-xs px-3 py-1 rounded-lg">Simpan</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="4" class="px-5 py-10 text-center text-slate-400">Tidak ada kasus.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $daftarKasus->links() }}</div>
@endsection