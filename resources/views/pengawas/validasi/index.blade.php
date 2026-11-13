@extends('layouts.app')
@section('title', 'Validasi Lapangan')

@section('content')
<div class="mb-6">
    <div class="flex items-center gap-2"><span class="h-6 w-1 rounded bg-lebak-gold"></span><h1 class="text-2xl font-extrabold text-slate-900">Validasi Lapangan</h1></div>
    <p class="mt-1 text-sm text-slate-500">Klik nama siswa untuk membuka detail dan memvalidasi hasil investigasi lapangan. Data baru masuk master Data ATS setelah dinyatakan Valid.</p>
</div>

<div class="mb-4 flex flex-wrap items-center gap-2 text-sm">
    <a href="{{ route('pengawas.validasi.index') }}"
       class="rounded-full px-4 py-1.5 font-semibold {{ $tabAktif === 'Belum Divalidasi' ? 'bg-lebak-blue text-white' : 'bg-white text-slate-600 ring-1 ring-slate-200 hover:bg-slate-50' }}">Perlu Divalidasi ({{ $jumlahBelum }})</a>
    <a href="{{ route('pengawas.validasi.index', ['tab' => 'tidak-valid']) }}"
       class="rounded-full px-4 py-1.5 font-semibold {{ $tabAktif === 'Tidak Valid' ? 'bg-lebak-blue text-white' : 'bg-white text-slate-600 ring-1 ring-slate-200 hover:bg-slate-50' }}">Tidak Valid ({{ $jumlahTidakValid }})</a>

    <form method="GET" action="{{ route('pengawas.validasi.index') }}" class="ml-auto flex gap-2">
        @if ($tabAktif === 'Tidak Valid') <input type="hidden" name="tab" value="tidak-valid"> @endif
        <input type="text" name="cari" value="{{ request('cari') }}" maxlength="100" placeholder="Cari nama atau NISN…"
               class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-sm focus:border-lebak-blue focus:outline-none focus:ring-1 focus:ring-lebak-blue">
        <button type="submit" class="rounded-lg bg-lebak-blue px-4 py-1.5 text-sm font-bold text-white hover:bg-lebak-dark">Cari</button>
    </form>
</div>

<div class="overflow-hidden rounded-xl border border-slate-200 bg-white">
  <div class="overflow-x-auto">
    <table class="w-full text-sm">
        <thead class="bg-lebak-blue/5 text-left text-xs font-semibold uppercase tracking-wide text-lebak-blue">
            <tr>
                <th class="px-5 py-3">Nama Siswa</th>
                <th class="px-5 py-3">Domisili</th>
                <th class="px-5 py-3">Status</th>
                <th class="px-5 py-3">Prioritas</th>
                <th class="px-5 py-3">{{ $tabAktif === 'Tidak Valid' ? 'Catatan Lapangan' : 'Alasan' }}</th>
                <th class="px-5 py-3 text-right">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @forelse ($data as $item)
                <tr class="transition hover:bg-lebak-cream/60">
                    <td class="px-5 py-3">
                        <a href="{{ route('pengawas.validasi.show', $item) }}" class="font-semibold text-slate-900 hover:text-lebak-blue hover:underline">{{ $item->nama_siswa }}</a>
                        <p class="text-xs text-slate-400">NISN: {{ $item->nisn ?? '-' }}</p>
                    </td>
                    <td class="px-5 py-3 text-slate-600">{{ $item->kecamatanDomisili->nama_kecamatan ?? '—' }}</td>
                    <td class="px-5 py-3"><span class="chip {{ $item->status === 'DO' ? 'bg-rose-50 text-rose-700' : 'bg-lebak-cream text-lebak-gold' }}">{{ $item->status }}</span></td>
                    <td class="px-5 py-3 text-slate-600">{{ $item->prioritas_penjangkauan }}</td>
                    <td class="max-w-xs px-5 py-3 text-slate-600">
                        {{ $tabAktif === 'Tidak Valid' ? ($item->catatan_validasi ?? '—') : ($item->alasan_approval ?? '—') }}
                    </td>
                    <td class="px-5 py-3">
                        <div class="flex flex-wrap justify-end gap-2">
                            <a href="{{ route('pengawas.validasi.show', $item) }}" class="btn-ok">Validasi</a>
                            <a href="{{ route('pengawas.data-ats.edit', $item) }}" class="btn-edit">Edit</a>
                            <form method="POST" action="{{ route('pengawas.data-ats.destroy', $item) }}" data-confirm="Hapus data {{ $item->nama_siswa }}? Tindakan ini tidak bisa dibatalkan.">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn-hapus">Hapus</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="px-5 py-12 text-center text-slate-400">
                    {{ $tabAktif === 'Tidak Valid' ? 'Tidak ada data yang dinyatakan tidak valid.' : 'Tidak ada data yang perlu divalidasi. Semua sudah dikerjakan.' }}
                </td></tr>
            @endforelse
        </tbody>
    </table>
  </div>
</div>
<div class="mt-4">{{ $data->links() }}</div>
@endsection
