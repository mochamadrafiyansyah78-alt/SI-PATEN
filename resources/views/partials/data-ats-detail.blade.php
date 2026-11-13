@php
    $warnaLevel = match ($item->ews_level) {
        'Merah' => 'bg-rose-50 text-rose-700 border-rose-200',
        'Kuning' => 'bg-amber-50 text-amber-700 border-amber-200',
        default => 'bg-lebak-green/20 text-[#1f6b48] border-lebak-green/40',
    };
    $warnaVerifikasi = match ($item->status_verifikasi) {
        'Menunggu Verifikasi' => 'bg-lebak-cream text-lebak-gold border-lebak-gold/30',
        'Ditolak' => 'bg-rose-50 text-rose-700 border-rose-200',
        default => 'bg-slate-50 text-slate-600 border-slate-200',
    };
    $warnaValidasi = match ($item->status_validasi) {
        'Valid' => 'bg-lebak-green/20 text-[#1f6b48] border-lebak-green/40',
        'Tidak Valid' => 'bg-rose-50 text-rose-700 border-rose-200',
        default => 'bg-slate-50 text-slate-500 border-slate-200',
    };
    $bisaKelolaSk = $bisaKelolaSk ?? false;
    $bisaValidasi = $bisaValidasi ?? false;
@endphp

<div class="grid grid-cols-1 gap-5 lg:grid-cols-3">
    <div class="space-y-5 lg:col-span-2">
        <div class="rounded-xl border border-slate-200 bg-white p-5">
            <div class="mb-4 flex items-start justify-between gap-3">
                <div>
                    <h2 class="text-lg font-extrabold text-slate-900">{{ $item->nama_siswa }}</h2>
                    <p class="text-sm text-slate-500">NISN {{ $item->nisn ?? '—' }}</p>
                </div>
                <div class="flex shrink-0 flex-col items-end gap-1.5">
                    <span class="chip border {{ $warnaLevel }}">{{ $item->ews_level ?? 'Hijau' }}</span>
                    <span class="chip border {{ $warnaVerifikasi }}">{{ $item->status_verifikasi }}</span>
                    <span class="chip border {{ $warnaValidasi }}">{{ $item->status_validasi }}</span>
                </div>
            </div>

            @if ($item->status_verifikasi === 'Ditolak' && $item->catatan_verifikasi)
                <div class="mb-4 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                    <strong>Alasan ditolak verifikator:</strong> {{ $item->catatan_verifikasi }}
                </div>
            @endif

            @if ($item->status_verifikasi === 'Disetujui' && $item->status_validasi === 'Tidak Valid' && ! $bisaValidasi)
                <div class="mb-4 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                    <strong>Tidak valid menurut investigasi lapangan:</strong> {{ $item->catatan_validasi ?? 'Tanpa catatan.' }}
                    <p class="mt-1 text-xs text-rose-600">Data ini tidak masuk master Data ATS.</p>
                </div>
            @endif

            @if ($item->status_verifikasi === 'Disetujui' && $item->status_validasi === 'Belum Divalidasi' && ! $bisaValidasi)
                <div class="mb-4 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-700">
                    Lolos verifikator, menunggu validasi lapangan oleh pengawas sekolah. Belum masuk master Data ATS.
                </div>
            @endif

            @if ($item->prioritas_diturunkan)
                <div class="mb-4 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-700">
                    Prioritas data ini diturunkan otomatis dari alasan saat impor, bukan dari sumber data. Mohon dicek saat verifikasi.
                </div>
            @endif

            @if ($bisaValidasi)
                <form method="POST" action="{{ route('pengawas.validasi.update', $item) }}" class="mb-4 flex flex-wrap items-end gap-2 rounded-lg border border-slate-200 bg-lebak-mist p-3">
                    @csrf @method('PUT')
                    <div>
                        <label class="mb-1 block text-xs font-semibold text-slate-600">Status Validasi Data</label>
                        <select name="hasil" required class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-sm">
                            @if ($item->status_validasi === 'Belum Divalidasi')
                                <option value="" disabled @selected(! old('hasil'))>Pilih hasil…</option>
                            @endif
                            <option value="Valid" @selected(old('hasil', $item->status_validasi) === 'Valid')>Valid</option>
                            <option value="Tidak Valid" @selected(old('hasil', $item->status_validasi) === 'Tidak Valid')>Tidak Valid</option>
                        </select>
                    </div>
                    <div class="min-w-[200px] flex-1">
                        <label class="mb-1 block text-xs font-semibold text-slate-600">Catatan investigasi lapangan (wajib jika Tidak Valid)</label>
                        <input type="text" name="catatan_validasi" maxlength="1000" value="{{ old('catatan_validasi', $item->catatan_validasi) }}" class="w-full rounded-lg border border-slate-300 px-3 py-1.5 text-sm">
                    </div>
                    <button type="submit" class="rounded-lg bg-lebak-blue px-4 py-1.5 text-sm font-bold text-white transition hover:bg-lebak-dark">Simpan Validasi</button>
                </form>
            @endif

            <dl class="grid grid-cols-2 gap-x-4 gap-y-3 text-sm">
                <div><dt class="text-slate-400">Sekolah</dt><dd class="font-semibold text-slate-800">{{ $item->sekolah->nama_sekolah ?? '—' }}</dd></div>
                <div><dt class="text-slate-400">Kecamatan Domisili</dt><dd class="font-semibold text-slate-800">{{ $item->kecamatanDomisili->nama_kecamatan ?? '—' }}</dd></div>
                <div><dt class="text-slate-400">Tempat, Tanggal Lahir</dt><dd class="text-slate-800">{{ $item->tempat_lahir ?? '—' }}, {{ optional($item->tanggal_lahir)->translatedFormat('d F Y') ?? '—' }}</dd></div>
                <div><dt class="text-slate-400">Status</dt><dd class="text-slate-800">{{ $item->status }}</dd></div>
                @if ($item->jenis_kelamin)
                    <div><dt class="text-slate-400">Jenis Kelamin</dt><dd class="text-slate-800">{{ $item->jenis_kelamin === 'L' ? 'Laki-laki' : ($item->jenis_kelamin === 'P' ? 'Perempuan' : $item->jenis_kelamin) }}</dd></div>
                @endif
                @if ($item->usia)
                    <div><dt class="text-slate-400">Usia</dt><dd class="text-slate-800">{{ $item->usia }} tahun</dd></div>
                @endif
                @if ($item->desa_kelurahan)
                    <div><dt class="text-slate-400">Desa/Kelurahan</dt><dd class="text-slate-800">{{ $item->desa_kelurahan }}</dd></div>
                @endif
                @if ($item->tingkat_pendidikan)
                    <div><dt class="text-slate-400">Tingkat Pendidikan</dt><dd class="text-slate-800">{{ $item->tingkat_pendidikan }}</dd></div>
                @endif
                <div><dt class="text-slate-400">Prioritas Penjangkauan</dt><dd class="text-slate-800">{{ $item->prioritas_penjangkauan }}</dd></div>
                <div><dt class="text-slate-400">Alasan</dt><dd class="text-slate-800">{{ $item->alasan_approval ?? '—' }}</dd></div>
                @if ($item->keterangan_alasan)
                    <div class="col-span-2"><dt class="text-slate-400">Keterangan Alasan</dt><dd class="text-slate-800">{{ $item->keterangan_alasan }}</dd></div>
                @endif
                <div><dt class="text-slate-400">Status Penanganan</dt><dd class="font-semibold text-slate-800">{{ $item->status_penanganan ?? 'Baru' }}</dd></div>
                <div><dt class="text-slate-400">Ditangani Oleh</dt><dd class="text-slate-800">{{ $item->ditanganiOleh->nama_lengkap ?? '—' }}</dd></div>
            </dl>
        </div>

        <div class="rounded-xl border border-slate-200 bg-white p-5">
            <div class="mb-4 flex items-center gap-2"><span class="h-5 w-1 rounded bg-lebak-gold"></span><h3 class="text-base font-bold text-slate-900">Riwayat Penanganan</h3></div>

            @forelse ($item->riwayatPenanganan as $riwayat)
                <div class="mb-4 flex gap-3 border-b border-slate-100 pb-4 last:mb-0 last:border-0 last:pb-0">
                    <div class="mt-1.5 h-2.5 w-2.5 shrink-0 rounded-full bg-lebak-blue"></div>
                    <div>
                        <p class="text-sm font-semibold text-slate-800">{{ $riwayat->status_penanganan }}</p>
                        @if ($riwayat->catatan)
                            <p class="mt-0.5 text-sm text-slate-500">{{ $riwayat->catatan }}</p>
                        @endif
                        @if ($riwayat->lampiran_path)
                            <a href="{{ route('lampiran.show', $riwayat) }}" target="_blank" class="btn-edit mt-1.5">Lihat bukti dukung</a>
                        @endif
                        <p class="mt-1 text-xs text-slate-400">
                            {{ $riwayat->created_at->translatedFormat('d F Y, H:i') }}
                            @if ($riwayat->diubahOleh) · {{ $riwayat->diubahOleh->nama_lengkap }} @endif
                        </p>
                    </div>
                </div>
            @empty
                <p class="text-sm text-slate-400">Belum ada riwayat penanganan.</p>
            @endforelse
        </div>
    </div>

    <div class="space-y-5">
        <div class="rounded-xl border border-slate-200 bg-white p-5">
            <div class="mb-3 flex items-center gap-2"><span class="h-5 w-1 rounded bg-lebak-gold"></span><h3 class="text-sm font-bold text-slate-900">Info Pencatatan</h3></div>
            <dl class="space-y-2 text-sm">
                <div><dt class="text-slate-400">Diinput Oleh</dt><dd class="text-slate-800">{{ $item->inputOleh->nama_lengkap ?? '—' }}</dd></div>
                <div><dt class="text-slate-400">Tanggal Input</dt><dd class="text-slate-800">{{ $item->created_at->translatedFormat('d F Y') }}</dd></div>
                <div><dt class="text-slate-400">Terakhir Diperbarui</dt><dd class="text-slate-800">{{ optional($item->status_updated_at)->translatedFormat('d F Y, H:i') ?? '—' }}</dd></div>
                @if ($item->sumber_data)
                    <div><dt class="text-slate-400">Sumber Data</dt><dd class="text-slate-800">{{ $item->sumber_data }}</dd></div>
                @endif
                @if ($item->diverifikasiOleh)
                    <div><dt class="text-slate-400">Diverifikasi Oleh</dt><dd class="text-slate-800">{{ $item->diverifikasiOleh->nama_lengkap }} · {{ optional($item->diverifikasi_at)->translatedFormat('d F Y') }}</dd></div>
                @endif
                @if ($item->divalidasiOleh)
                    <div><dt class="text-slate-400">Divalidasi Lapangan Oleh</dt><dd class="text-slate-800">{{ $item->divalidasiOleh->nama_lengkap }} · {{ optional($item->divalidasi_at)->translatedFormat('d F Y') }}</dd></div>
                @endif
            </dl>
        </div>

        <div class="rounded-xl border border-slate-200 bg-white p-5">
            <div class="mb-3 flex items-center gap-2"><span class="h-5 w-1 rounded bg-lebak-gold"></span><h3 class="text-sm font-bold text-slate-900">SK Bantuan</h3></div>

            @forelse ($item->skBantuan as $sk)
                <div class="mb-3 border-b border-slate-100 pb-3 last:mb-0 last:border-0 last:pb-0">
                    <p class="text-sm font-semibold text-slate-800">{{ $sk->jenis_bantuan }}</p>
                    <p class="text-xs text-slate-500">No. {{ $sk->nomor_sk }} · {{ $sk->tanggal_sk->translatedFormat('d F Y') }}</p>
                    @if ($sk->jumlah_bantuan)
                        <p class="text-xs text-slate-500">Rp {{ number_format($sk->jumlah_bantuan, 0, ',', '.') }}</p>
                    @endif
                    @if ($bisaKelolaSk)
                        <form method="POST" action="{{ route('admin.sk-bantuan.destroy', $sk) }}" onsubmit="return confirm('Hapus SK bantuan ini?')" class="mt-2">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn-hapus">Hapus</button>
                        </form>
                    @endif
                </div>
            @empty
                <p class="mb-3 text-sm text-slate-400">Belum ada SK bantuan tercatat.</p>
            @endforelse

            @if ($bisaKelolaSk)
                <form method="POST" action="{{ route('admin.sk-bantuan.store', $item) }}" class="mt-3 space-y-2 border-t border-slate-100 pt-3">
                    @csrf
                    <input type="text" name="nomor_sk" placeholder="Nomor SK" required class="w-full rounded-lg border border-slate-300 px-3 py-1.5 text-xs">
                    <input type="date" name="tanggal_sk" required class="w-full rounded-lg border border-slate-300 px-3 py-1.5 text-xs">
                    <input type="text" name="jenis_bantuan" placeholder="Jenis bantuan" required class="w-full rounded-lg border border-slate-300 px-3 py-1.5 text-xs">
                    <input type="number" step="0.01" name="jumlah_bantuan" placeholder="Jumlah (Rp, opsional)" class="w-full rounded-lg border border-slate-300 px-3 py-1.5 text-xs">
                    <textarea name="keterangan" placeholder="Keterangan (opsional)" rows="2" class="w-full rounded-lg border border-slate-300 px-3 py-1.5 text-xs"></textarea>
                    <button type="submit" class="w-full rounded-lg bg-lebak-blue py-2 text-xs font-bold text-white transition hover:bg-lebak-dark">Tambah SK Bantuan</button>
                </form>
            @endif
        </div>
    </div>
</div>