@php
    $langkah = [
        ['Admin Input Data', 'Admin dinas menginput atau mengimpor data siswa ATS beserta alasan dan prioritas.', 'bg-lebak-blue text-white'],
        ['Verifikator Memeriksa', 'Data disetujui atau ditolak, bisa massal. Yang ditolak kembali ke admin.', 'bg-lebak-gold text-white'],
        ['Pengawas Validasi Lapangan', 'Pengawas turun ke lapangan. Hanya data yang Valid masuk ke Data ATS.', 'bg-lebak-green text-[#0b3d2a]'],
        ['EWS Hitung Risiko', 'Level Merah / Kuning / Hijau dihitung otomatis oleh sistem.', 'bg-lebak-dark text-white'],
        ['Penanganan & Laporan', 'Status penanganan dicatat, hasilnya masuk laporan bulanan.', 'bg-slate-700 text-white'],
    ];
    $sorot = $sorot ?? 0;
@endphp
<section class="mb-6 rounded-xl border border-slate-200 bg-white p-5">
    <div class="mb-4 flex items-center gap-2">
        <span class="h-5 w-1 rounded bg-lebak-gold"></span>
        <h2 class="text-base font-bold text-slate-900">Alur Kerja Sistem</h2>
    </div>
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
        @foreach ($langkah as $i => [$judul, $desc, $warna])
            <div class="flex gap-3 rounded-lg p-3 {{ $sorot === $i + 1 ? 'bg-lebak-cream ring-1 ring-lebak-gold/40' : '' }}">
                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full text-sm font-bold {{ $warna }}">{{ $i + 1 }}</span>
                <div>
                    <p class="text-sm font-semibold text-slate-900">{{ $judul }}</p>
                    <p class="mt-0.5 text-xs leading-relaxed text-slate-500">{{ $desc }}</p>
                </div>
            </div>
        @endforeach
    </div>
</section>
