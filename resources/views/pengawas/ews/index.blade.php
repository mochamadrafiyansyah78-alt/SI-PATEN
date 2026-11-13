@extends('layouts.app')
@section('title', 'Early Warning System')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-semibold text-slate-900">Early Warning System</h1>
    <p class="mt-1 text-sm text-slate-500">Pantau dan tangani siswa ATS. Urut dari level paling kritis.</p>
</div>

<div class="mb-5 grid gap-3 sm:grid-cols-3">
    @foreach (['Merah' => 'rose', 'Kuning' => 'amber', 'Hijau' => 'emerald'] as $lv => $c)
        <a href="{{ route('pengawas.ews.index', ['level' => $lv]) }}" class="rounded-xl border border-slate-200 bg-white p-4 transition hover:shadow-md">
            @include('partials.chip-level', ['level' => $lv])
            <p class="mt-2 text-2xl font-extrabold text-slate-900">{{ number_format($ringkasan[$lv] ?? 0, 0, ',', '.') }}</p>
        </a>
    @endforeach
</div>

@include('partials.filter-data', ['aksi' => route('pengawas.ews.index'), 'kecamatan' => \App\Models\Kecamatan::orderBy('nama_kecamatan')->get(), 'sekolah' => \App\Models\Sekolah::orderBy('nama_sekolah')->get()])

<div class="overflow-hidden rounded-xl border border-slate-200 bg-white">
  <div class="overflow-x-auto">
    <table class="w-full text-sm">
        <thead class="border-b border-slate-100 text-left text-slate-500">
            <tr><th class="px-5 py-3 font-medium">Nama Siswa</th><th class="px-5 py-3 font-medium">Sekolah</th><th class="px-5 py-3 font-medium">Level</th><th class="px-5 py-3 font-medium">Penanganan</th><th class="px-5 py-3"></th></tr>
        </thead>
        <tbody class="divide-y divide-slate-50">
            @forelse ($daftarKasus as $kasus)
                <tr class="hover:bg-slate-50/60">
                    <td class="px-5 py-3"><p class="font-medium text-slate-900">{{ $kasus->nama_siswa }}</p><p class="text-xs text-slate-400">NISN: {{ $kasus->nisn ?? '-' }}</p></td>
                    <td class="px-5 py-3 text-slate-600">{{ $kasus->sekolah->nama_sekolah ?? '—' }}</td>
                    <td class="px-5 py-3">@include('partials.chip-level', ['level' => $kasus->ews_level])</td>
                    <td class="px-5 py-3 text-slate-600">{{ $kasus->status_penanganan ?? 'Baru' }}</td>
                    <td class="px-5 py-3 text-right"><a href="{{ route('pengawas.ews.show', $kasus) }}" class="rounded-lg bg-lebak-blue px-3 py-1.5 text-xs font-bold text-white hover:bg-lebak-dark">Tindakan</a></td>
                </tr>
            @empty
                <tr><td colspan="5" class="px-5 py-10 text-center text-slate-400">Tidak ada kasus.</td></tr>
            @endforelse
        </tbody>
    </table>
  </div>
</div>
<div class="mt-4">{{ $daftarKasus->links() }}</div>
@endsection
