@extends('layouts.app')
@section('title', 'Data ATS')

@section('content')
@php
    $prioritasAktif = request('prioritas') ?: null;
    $kartuFilter = [
        ['nilai' => null, 'label' => 'Semua Data', 'jumlah' => $jumlahPrioritas->sum(), 'garis' => 'border-t-lebak-blue', 'angka' => 'text-lebak-blue', 'cincin' => 'ring-lebak-blue'],
        ['nilai' => 'Prioritas 1', 'label' => 'Prioritas 1', 'jumlah' => $jumlahPrioritas['Prioritas 1'], 'garis' => 'border-t-rose-500', 'angka' => 'text-rose-600', 'cincin' => 'ring-rose-500'],
        ['nilai' => 'Prioritas 2', 'label' => 'Prioritas 2', 'jumlah' => $jumlahPrioritas['Prioritas 2'], 'garis' => 'border-t-lebak-gold', 'angka' => 'text-lebak-gold', 'cincin' => 'ring-lebak-gold'],
        ['nilai' => 'Prioritas 3', 'label' => 'Prioritas 3', 'jumlah' => $jumlahPrioritas['Prioritas 3'], 'garis' => 'border-t-lebak-green', 'angka' => 'text-[#2f8a62]', 'cincin' => 'ring-lebak-green'],
    ];
@endphp

<div class="mb-6">
    <div class="flex items-center gap-2"><span class="h-6 w-1 rounded bg-lebak-gold"></span><h1 class="text-2xl font-extrabold text-slate-900">Data ATS Kabupaten Lebak</h1></div>
    <p class="mt-1 text-sm text-slate-500">{{ number_format($data->total(), 0, ',', '.') }} data ditemukan dari total {{ number_format($totalKabupaten, 0, ',', '.') }} data master (terverifikasi dan tervalidasi lapangan). Edit/hapus hanya untuk data sekolah Anda.</p>
</div>

@if ($menungguValidasi > 0)
    <a href="{{ route('pengawas.validasi.index') }}" class="mb-5 flex items-center justify-between gap-3 rounded-xl border border-amber-200 bg-amber-50 px-5 py-3 text-sm text-amber-800 transition hover:bg-amber-100">
        <span><strong>{{ $menungguValidasi }}</strong> data siswa sekolah Anda menunggu validasi lapangan.</span>
        <span class="font-bold">Validasi sekarang &rarr;</span>
    </a>
@endif

{{-- Kartu prioritas: klik untuk memfilter tabel, klik lagi untuk menghapus filter --}}
<div class="mb-5 grid grid-cols-2 gap-3 lg:grid-cols-4">
    @foreach ($kartuFilter as $kartu)
        @php
            $aktif = $kartu['nilai'] === $prioritasAktif;
            $tujuan = ($kartu['nilai'] === null || $aktif)
                ? request()->fullUrlWithoutQuery(['prioritas', 'page'])
                : request()->fullUrlWithQuery(['prioritas' => $kartu['nilai'], 'page' => null]);
        @endphp
        <a href="{{ $tujuan }}" aria-pressed="{{ $aktif ? 'true' : 'false' }}"
           class="rounded-xl border border-slate-200 border-t-4 {{ $kartu['garis'] }} bg-white p-4 transition hover:-translate-y-0.5 hover:shadow-md {{ $aktif ? 'ring-2 ' . $kartu['cincin'] : '' }}">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ $kartu['label'] }}</p>
            <p class="mt-1 text-2xl font-extrabold {{ $kartu['angka'] }}">{{ number_format($kartu['jumlah'], 0, ',', '.') }}</p>
            <p class="mt-0.5 text-[11px] text-slate-400">{{ $aktif ? 'Filter aktif · klik untuk hapus' : 'Klik untuk memfilter' }}</p>
        </a>
    @endforeach
</div>

@include('partials.filter-data', ['aksi' => route('pengawas.data-ats.index')])

<div class="overflow-hidden rounded-xl border border-slate-200 bg-white">
  <div class="overflow-x-auto">
    <table class="w-full text-sm">
        <thead class="bg-lebak-blue/5 text-left text-xs font-semibold uppercase tracking-wide text-lebak-blue">
            <tr>
                <th class="px-5 py-3">Nama Siswa</th>
                <th class="px-5 py-3">Sekolah</th>
                <th class="px-5 py-3">Status</th>
                <th class="px-5 py-3">Prioritas</th>
                <th class="px-5 py-3">Level</th>
                <th class="px-5 py-3 text-right">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @forelse ($data as $item)
                @php $milik = $item->sekolah_id === auth()->user()->sekolah_id; @endphp
                <tr class="transition hover:bg-lebak-cream/60">
                    <td class="px-5 py-3">
                        <p class="font-semibold text-slate-900">{{ $item->nama_siswa }}</p>
                        <p class="text-xs text-slate-400">NISN: {{ $item->nisn ?? '-' }}</p>
                    </td>
                    <td class="px-5 py-3 text-slate-600">{{ $item->sekolah->nama_sekolah ?? '—' }}</td>
                    <td class="px-5 py-3"><span class="chip {{ $item->status === 'DO' ? 'bg-rose-50 text-rose-700' : 'bg-lebak-cream text-lebak-gold' }}">{{ $item->status }}</span></td>
                    <td class="px-5 py-3 text-slate-600">{{ $item->prioritas_penjangkauan }}</td>
                    <td class="px-5 py-3">@include('partials.chip-level', ['level' => $item->ews_level])</td>
                    <td class="px-5 py-3">
                        <div class="flex flex-wrap items-center justify-end gap-2">
                            <a href="{{ route('pengawas.ews.show', $item) }}" class="rounded-lg bg-lebak-blue px-3 py-1.5 text-xs font-bold text-white transition hover:bg-lebak-dark">Tindakan</a>
                            @if ($milik)
                                <a href="{{ route('pengawas.data-ats.edit', $item) }}" class="btn-edit">Edit</a>
                                <form method="POST" action="{{ route('pengawas.data-ats.destroy', $item) }}" data-confirm="Hapus data {{ $item->nama_siswa }}? Tindakan ini tidak bisa dibatalkan.">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn-hapus">Hapus</button>
                                </form>
                            @else
                                <span class="text-xs text-slate-400">Sekolah lain</span>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="px-5 py-10 text-center text-slate-400">Tidak ada data yang cocok.</td></tr>
            @endforelse
        </tbody>
    </table>
  </div>
</div>
<div class="mt-4">{{ $data->links() }}</div>
@endsection
