@extends('layouts.app')
@section('title', 'Data Master ATS')

@section('content')
<div class="mb-6 flex flex-wrap items-center justify-between gap-3">
    <div>
        <h1 class="text-2xl font-semibold text-slate-900">Data Master ATS</h1>
        <p class="mt-1 text-sm text-slate-500">
            @if ($tabAktif === 'Disetujui') Master data (terverifikasi dan tervalidasi lapangan) se-Kabupaten Lebak: {{ number_format($data->total(), 0, ',', '.') }} data.
            @else {{ number_format($data->total(), 0, ',', '.') }} data pada tahap "{{ $tabAktif }}". @endif
        </p>
    </div>
    <div class="flex flex-wrap gap-2">
        <a href="{{ route('admin.data-ats.import') }}" class="rounded-lg border border-lebak-blue px-4 py-2.5 text-sm font-bold text-lebak-blue transition hover:bg-lebak-blue/5">Import Excel/CSV</a>
        <a href="{{ route('admin.data-ats.create') }}" class="rounded-lg bg-lebak-blue px-4 py-2.5 text-sm font-bold text-white transition hover:bg-lebak-dark">+ Input Data</a>
    </div>
</div>

<div class="mb-4 flex flex-wrap gap-2 text-sm">
    @foreach ([
        'Disetujui' => 'Master Data',
        'Menunggu Verifikasi' => 'Menunggu Verifikasi (' . $jumlahMenungguVerifikasi . ')',
        'Menunggu Validasi' => 'Validasi Lapangan (' . $jumlahMenungguValidasi . ')',
        'Tidak Valid' => 'Tidak Valid',
        'Ditolak' => 'Ditolak Verifikator',
    ] as $nilai => $label)
        <a href="{{ route('admin.data-ats.index', $nilai === 'Disetujui' ? [] : ['status_verifikasi' => $nilai]) }}"
           class="rounded-full px-4 py-1.5 font-semibold {{ $tabAktif === $nilai ? 'bg-lebak-blue text-white' : 'bg-white text-slate-600 ring-1 ring-slate-200 hover:bg-slate-50' }}">{{ $label }}</a>
    @endforeach
</div>

@include('partials.filter-data', ['aksi' => route('admin.data-ats.index'), 'tersembunyi' => ['status_verifikasi' => $tabAktif === 'Disetujui' ? null : $tabAktif]])

<div class="overflow-hidden rounded-xl border border-slate-200 bg-white">
  <div class="overflow-x-auto">
    <table class="w-full text-sm">
        <thead class="border-b border-slate-100 text-left text-slate-500">
            <tr>
                <th class="px-5 py-3 font-medium">Nama Siswa</th>
                <th class="px-5 py-3 font-medium">Sekolah</th>
                <th class="px-5 py-3 font-medium">Kecamatan</th>
                <th class="px-5 py-3 font-medium">Status</th>
                <th class="px-5 py-3 font-medium">Prioritas</th>
                <th class="px-5 py-3 font-medium">Penanganan</th>
                <th class="px-5 py-3 font-medium">Level</th>
                <th class="px-5 py-3 font-medium">{{ $tabAktif === 'Ditolak' ? 'Aksi' : 'Tahap' }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($data as $item)
                <tr class="border-b border-slate-50 last:border-0 hover:bg-slate-50/60">
                    <td class="px-5 py-3">
                        <a href="{{ route('admin.data-ats.show', $item) }}" class="font-medium text-slate-900 hover:text-lebak-blue hover:underline">{{ $item->nama_siswa }}</a>
                        <p class="text-xs text-slate-400">NISN: {{ $item->nisn ?? '-' }}</p>
                    </td>
                    <td class="px-5 py-3 text-slate-600">{{ $item->sekolah->nama_sekolah ?? '—' }}</td>
                    <td class="px-5 py-3 text-slate-600">{{ $item->kecamatanDomisili->nama_kecamatan ?? '—' }}</td>
                    <td class="px-5 py-3"><span class="chip {{ $item->status === 'DO' ? 'bg-rose-50 text-rose-700' : 'bg-amber-50 text-amber-700' }}">{{ $item->status }}</span></td>
                    <td class="px-5 py-3 text-slate-600">{{ $item->prioritas_penjangkauan }}</td>
                    <td class="px-5 py-3 text-slate-600">{{ $item->status_penanganan ?? 'Baru' }}</td>
                    <td class="px-5 py-3">@include('partials.chip-level', ['level' => $item->ews_level])</td>
                    <td class="px-5 py-3">
                        @if ($item->status_verifikasi === 'Ditolak')
                            <form method="POST" action="{{ route('admin.data-ats.ajukan-ulang', $item) }}">@csrf @method('PUT')
                                <button type="submit" class="btn-edit" title="{{ $item->catatan_verifikasi }}">Ajukan Ulang</button>
                            </form>
                        @elseif ($item->status_verifikasi === 'Menunggu Verifikasi')
                            <span class="chip bg-lebak-cream text-lebak-gold">Menunggu verifikator</span>
                        @elseif ($item->status_validasi === 'Valid')
                            <span class="chip bg-emerald-50 text-emerald-700">Valid</span>
                        @elseif ($item->status_validasi === 'Tidak Valid')
                            <span class="chip bg-rose-50 text-rose-700" title="{{ $item->catatan_validasi }}">Tidak Valid</span>
                        @else
                            <span class="chip bg-slate-100 text-slate-500">Menunggu pengawas</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="8" class="px-5 py-10 text-center text-slate-400">Tidak ada data yang cocok dengan filter.</td></tr>
            @endforelse
        </tbody>
    </table>
  </div>
</div>
<div class="mt-4">{{ $data->links() }}</div>
@endsection
