@extends('layouts.app')
@section('title', 'Verifikasi Laporan Masuk')

@section('content')
<div class="flex items-center gap-3 mb-6">
    <div class="w-11 h-11 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center shrink-0">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
    </div>
    <div>
        <h1 class="text-2xl font-semibold text-slate-900">Verifikasi Laporan Masuk</h1>
        <p class="text-sm text-slate-500 mt-1">Laporan data ATS baru dari pengawas sekolah yang menunggu persetujuan Dinas.</p>
    </div>
</div>

<div class="space-y-4">
    @forelse ($data as $item)
        <div class="bg-white rounded-xl border border-slate-200 p-5">
            <div class="flex items-start justify-between gap-4 mb-3">
                <div>
                    <a href="{{ route('admin.data-ats.show', $item) }}" class="font-semibold text-slate-900 hover:text-teal-700 hover:underline">{{ $item->nama_siswa }}</a>
                    <p class="text-sm text-slate-500">{{ $item->sekolah->nama_sekolah ?? '—' }} · {{ $item->kecamatanDomisili->nama_kecamatan ?? '—' }}</p>
                    <p class="text-xs text-slate-400 mt-1">
                        Dilaporkan oleh {{ $item->inputOleh->nama_lengkap ?? '—' }} · {{ $item->created_at->translatedFormat('d F Y, H:i') }}
                    </p>
                </div>
                <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium {{ $item->status === 'DO' ? 'bg-rose-50 text-rose-700' : 'bg-amber-50 text-amber-700' }}">
                    {{ $item->status }}
                </span>
            </div>

            <dl class="grid grid-cols-2 gap-x-4 gap-y-1 text-sm mb-4">
                <div><dt class="inline text-slate-400">NIK:</dt> <dd class="inline text-slate-700">{{ $item->nik }}</dd></div>
                <div><dt class="inline text-slate-400">NISN:</dt> <dd class="inline text-slate-700">{{ $item->nisn }}</dd></div>
                <div><dt class="inline text-slate-400">Prioritas:</dt> <dd class="inline text-slate-700">{{ $item->prioritas_penjangkauan }}</dd></div>
                <div><dt class="inline text-slate-400">Alasan:</dt> <dd class="inline text-slate-700">{{ $item->alasan_approval ?? '—' }}</dd></div>
            </dl>

            <div class="flex items-center gap-2">
                <form method="POST" action="{{ route('admin.verifikasi.approve', $item) }}" onsubmit="return confirm('Setujui laporan {{ $item->nama_siswa }}?')">
                    @csrf
                    @method('PUT')
                    <button type="submit" class="bg-teal-700 hover:bg-teal-800 text-white rounded-lg px-4 py-2 text-sm font-medium">Setujui</button>
                </form>

                <button type="button" onclick="document.getElementById('tolak-{{ $item->id }}').classList.toggle('hidden')" class="border border-rose-300 text-rose-600 hover:bg-rose-50 rounded-lg px-4 py-2 text-sm font-medium">
                    Tolak
                </button>
            </div>

            <form id="tolak-{{ $item->id }}" method="POST" action="{{ route('admin.verifikasi.reject', $item) }}" class="hidden mt-3 flex gap-2">
                @csrf
                @method('PUT')
                <input type="text" name="catatan_verifikasi" required placeholder="Alasan penolakan (wajib diisi)"
                       class="flex-1 border border-slate-300 rounded-lg px-3 py-2 text-sm">
                <button type="submit" class="bg-rose-600 hover:bg-rose-700 text-white rounded-lg px-4 py-2 text-sm font-medium">Kirim Penolakan</button>
            </form>
        </div>
    @empty
        <div class="bg-white rounded-xl border border-slate-200 p-10 text-center">
            <div class="w-12 h-12 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center mx-auto mb-3">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            </div>
            <p class="text-slate-500">Tidak ada laporan yang menunggu verifikasi saat ini.</p>
        </div>
    @endforelse
</div>

<div class="mt-4">
    {{ $data->links() }}
</div>
@endsection