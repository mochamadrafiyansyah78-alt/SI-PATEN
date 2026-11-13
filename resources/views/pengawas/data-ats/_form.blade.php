@php
    $item = $item ?? null;
    $daftarAlasan = app(\App\Services\EwsService::class)->daftarAlasan();
    $inp = 'w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-lebak-blue focus:outline-none focus:ring-1 focus:ring-lebak-blue';
    $lbl = 'block text-sm font-medium text-slate-700 mb-1';
    $err = 'mt-1 text-xs text-rose-600';
@endphp
@csrf
<div class="grid gap-5 sm:grid-cols-2">
    @isset($sekolah)
    <div class="sm:col-span-2">
        <label for="sekolah_id" class="{{ $lbl }}">Sekolah <span class="text-rose-500">*</span></label>
        <select id="sekolah_id" name="sekolah_id" required class="{{ $inp }}">
            <option value="">— Pilih sekolah —</option>
            @foreach ($sekolah as $s)<option value="{{ $s->id }}" @selected(old('sekolah_id') === $s->id)>{{ $s->nama_sekolah }}</option>@endforeach
        </select>
        @error('sekolah_id')<p class="{{ $err }}">{{ $message }}</p>@enderror
    </div>
    @endisset
    <div>
        <label for="nisn" class="{{ $lbl }}">NISN <span class="text-rose-500">*</span></label>
        <input type="text" id="nisn" name="nisn" value="{{ old('nisn', $item?->nisn) }}" maxlength="10" inputmode="numeric" required class="{{ $inp }}">
        @error('nisn')<p class="{{ $err }}">{{ $message }}</p>@enderror
    </div>
    <div>
        <label for="nama_siswa" class="{{ $lbl }}">Nama Siswa <span class="text-rose-500">*</span></label>
        <input type="text" id="nama_siswa" name="nama_siswa" value="{{ old('nama_siswa', $item?->nama_siswa) }}" maxlength="150" required class="{{ $inp }}">
        @error('nama_siswa')<p class="{{ $err }}">{{ $message }}</p>@enderror
    </div>
    <div>
        <label for="tempat_lahir" class="{{ $lbl }}">Tempat Lahir</label>
        <input type="text" id="tempat_lahir" name="tempat_lahir" value="{{ old('tempat_lahir', $item?->tempat_lahir) }}" maxlength="100" class="{{ $inp }}">
        @error('tempat_lahir')<p class="{{ $err }}">{{ $message }}</p>@enderror
    </div>
    <div>
        <label for="tanggal_lahir" class="{{ $lbl }}">Tanggal Lahir</label>
        <input type="date" id="tanggal_lahir" name="tanggal_lahir" value="{{ old('tanggal_lahir', $item?->tanggal_lahir?->format('Y-m-d')) }}" class="{{ $inp }}">
        @error('tanggal_lahir')<p class="{{ $err }}">{{ $message }}</p>@enderror
    </div>
    <div class="sm:col-span-2">
        <label for="kecamatan_domisili_id" class="{{ $lbl }}">Kecamatan Domisili <span class="text-rose-500">*</span></label>
        <select id="kecamatan_domisili_id" name="kecamatan_domisili_id" required class="{{ $inp }}">
            <option value="">— Pilih kecamatan —</option>
            @foreach ($kecamatan as $k)
                <option value="{{ $k->id }}" @selected(old('kecamatan_domisili_id', $item?->kecamatan_domisili_id) === $k->id)>{{ $k->nama_kecamatan }}</option>
            @endforeach
        </select>
        @error('kecamatan_domisili_id')<p class="{{ $err }}">{{ $message }}</p>@enderror
    </div>
    <div>
        <label for="status" class="{{ $lbl }}">Status <span class="text-rose-500">*</span></label>
        <select id="status" name="status" required class="{{ $inp }}">
            @foreach (['LTM', 'DO'] as $s)
                <option value="{{ $s }}" @selected(old('status', $item?->status) === $s)>{{ $s }}</option>
            @endforeach
        </select>
        @error('status')<p class="{{ $err }}">{{ $message }}</p>@enderror
    </div>
    <div>
        <label for="prioritas_penjangkauan" class="{{ $lbl }}">Prioritas Penjangkauan <span class="text-rose-500">*</span></label>
        <select id="prioritas_penjangkauan" name="prioritas_penjangkauan" required class="{{ $inp }}">
            @foreach (['Prioritas 1', 'Prioritas 2', 'Prioritas 3'] as $p)
                <option value="{{ $p }}" @selected(old('prioritas_penjangkauan', $item?->prioritas_penjangkauan) === $p)>{{ $p }}</option>
            @endforeach
        </select>
        @error('prioritas_penjangkauan')<p class="{{ $err }}">{{ $message }}</p>@enderror
    </div>
    <div class="sm:col-span-2">
        <label for="alasan_approval" class="{{ $lbl }}">Alasan Tidak Sekolah</label>
        @if ($daftarAlasan)
            <select id="alasan_approval" name="alasan_approval" class="{{ $inp }}">
                <option value="">— Pilih alasan —</option>
                @foreach ($daftarAlasan as $a)
                    <option value="{{ $a }}" @selected(old('alasan_approval', $item?->alasan_approval) === $a)>{{ $a }}</option>
                @endforeach
            </select>
        @else
            <input type="text" id="alasan_approval" name="alasan_approval" value="{{ old('alasan_approval', $item?->alasan_approval) }}" maxlength="255" class="{{ $inp }}">
        @endif
        @error('alasan_approval')<p class="{{ $err }}">{{ $message }}</p>@enderror
    </div>
</div>

<div class="mt-6 flex flex-wrap gap-2">
    <button type="submit" class="rounded-lg bg-lebak-blue px-5 py-2.5 text-sm font-bold text-white transition hover:bg-lebak-dark">Simpan</button>
    <a href="{{ $batal ?? route('pengawas.data-ats.index') }}" class="btn-muted">Batal</a>
</div>