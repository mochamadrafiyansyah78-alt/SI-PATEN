@extends('layouts.app')
@section('title', 'Data Mutasi')

@section('content')
<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-semibold text-slate-900">Data Mutasi</h1>
    <a href="{{ route('pengawas.mutasi.create') }}" class="bg-teal-700 hover:bg-teal-800 text-white text-sm px-4 py-2 rounded-lg">+ Catat Mutasi</a>
</div>

<div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
    <table class="w-full text-sm">
        <thead class="text-left text-slate-500 border-b border-slate-100">
            <tr>
                <th class="px-5 py-3 font-medium">Nama Siswa</th>
                <th class="px-5 py-3 font-medium">NISN</th>
                <th class="px-5 py-3 font-medium">Sekolah Asal</th>
                <th class="px-5 py-3 font-medium">Sekolah Tujuan</th>
                <th class="px-5 py-3 font-medium">Tanggal</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($data as $m)
                <tr class="border-b border-slate-50 last:border-0">
                    <td class="px-5 py-3 font-medium text-slate-900">{{ $m->nama_siswa }}</td>
                    <td class="px-5 py-3 text-slate-600">{{ $m->nisn ?? $m->nik ?? '—' }}</td>
                    <td class="px-5 py-3 text-slate-600">{{ $m->sekolah_asal }}</td>
                    <td class="px-5 py-3 text-slate-600">{{ $m->sekolah_tujuan }}</td>
                    <td class="px-5 py-3 text-slate-600">{{ $m->tanggal_mutasi->translatedFormat('d F Y') }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="px-5 py-10 text-center text-slate-400">Belum ada data mutasi.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $data->links() }}</div>
@endsection