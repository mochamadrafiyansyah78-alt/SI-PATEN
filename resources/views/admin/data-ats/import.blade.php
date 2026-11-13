@extends('layouts.app')
@section('title', 'Import Data ATS')

@section('content')
<a href="{{ route('admin.data-ats.index') }}" class="text-sm text-slate-500 hover:text-slate-700 inline-flex items-center gap-1 mb-4">&larr; Kembali ke Data ATS</a>
<h1 class="text-2xl font-semibold text-slate-900 mb-1">Import Data ATS</h1>
<p class="text-sm text-slate-500 mb-6">Unggah file Excel (.xlsx/.xls) atau CSV. Setiap baris ditempatkan ke sekolah lewat NPSN dan masuk antrean verifikasi; baru tampil di Data ATS setelah disetujui verifikator.</p>

<div class="grid gap-6 lg:grid-cols-2">
    <form method="POST" action="{{ route('admin.data-ats.import.store') }}" enctype="multipart/form-data" class="bg-white rounded-xl border border-slate-200 p-6">
        @csrf
        <label for="berkas" class="block text-sm font-medium text-slate-700 mb-2">Pilih file</label>
        <input type="file" id="berkas" name="berkas" accept=".xlsx,.xls,.csv,.txt" required class="block w-full text-sm text-slate-600 file:mr-3 file:rounded-lg file:border-0 file:bg-lebak-blue/10 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-lebak-blue">
        @error('berkas')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
        <div class="mt-5 flex flex-wrap gap-2">
            <button type="submit" class="rounded-lg bg-lebak-blue px-4 py-2.5 text-sm font-bold text-white transition hover:bg-lebak-dark">Import Sekarang</button>
            <a href="{{ route('admin.data-ats.import.template') }}" class="rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">Unduh Template</a>
        </div>
    </form>

    <div class="bg-white rounded-xl border border-slate-200 p-6 text-sm text-slate-600">
        <p class="font-semibold text-slate-900 mb-2">Aturan kolom</p>
        <ul class="list-disc space-y-1 pl-5">
            <li><b>npsn</b> (wajib): NPSN sekolah, lihat sheet "Daftar Sekolah" di template.</li>
            <li><b>nisn</b> (wajib): 10 digit, tidak boleh sama dengan data yang sudah ada.</li>
            <li><b>nama_siswa</b>, <b>kecamatan_domisili</b> (wajib): ejaan kecamatan seperti di sheet "Daftar Kecamatan".</li>
            <li><b>status</b> (wajib): LTM atau DO.</li>
            <li><b>prioritas_penjangkauan</b> (wajib): 1, 2, 3 atau "Prioritas 1/2/3".</li>
            <li>alasan_approval, tempat_lahir, tanggal_lahir (yyyy-mm-dd): opsional.</li>
            <li>Maksimal 2.000 baris dan 5 MB. Baris yang salah dilewati, baris benar tetap masuk.</li>
        </ul>
    </div>
</div>

@if (session('import_galat'))
    <div class="mt-6 overflow-hidden rounded-xl border border-rose-200 bg-white">
        <p class="bg-rose-50 px-5 py-3 text-sm font-semibold text-rose-700">{{ count(session('import_galat')) }} baris dilewati, perbaiki lalu unggah ulang baris tersebut:</p>
        <div class="max-h-96 overflow-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500"><tr><th class="px-5 py-2">Baris</th><th class="px-5 py-2">NISN</th><th class="px-5 py-2">Masalah</th></tr></thead>
                <tbody class="divide-y divide-slate-100">
                @foreach (session('import_galat') as $g)
                    <tr><td class="px-5 py-2">{{ $g['baris'] }}</td><td class="px-5 py-2">{{ $g['nisn'] ?: '-' }}</td><td class="px-5 py-2 text-slate-600">{{ $g['alasan'] }}</td></tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endif
@endsection