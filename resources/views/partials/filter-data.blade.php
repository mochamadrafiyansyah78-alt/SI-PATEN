{{-- Filter & pencarian seragam admin/pengawas. Param: $aksi (url), $kecamatan, $sekolah, $tersembunyi (opsional array name=>value) --}}
@php $kelas = 'w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm focus:border-lebak-blue focus:outline-none focus:ring-1 focus:ring-lebak-blue'; @endphp
<form method="GET" action="{{ $aksi }}" class="mb-5 grid grid-cols-2 gap-3 rounded-xl border border-slate-200 bg-white p-4 md:grid-cols-6">
    @foreach (($tersembunyi ?? []) as $nama => $nilai)
        @if ($nilai) <input type="hidden" name="{{ $nama }}" value="{{ $nilai }}"> @endif
    @endforeach
    <div class="col-span-2"><input type="text" name="cari" value="{{ request('cari') }}" maxlength="100" placeholder="Cari nama atau NISN…" class="{{ $kelas }}"></div>
    <div>
        <select name="kecamatan_id" class="{{ $kelas }}">
            <option value="">Semua Kecamatan</option>
            @foreach ($kecamatan as $k)<option value="{{ $k->id }}" @selected(request('kecamatan_id') === $k->id)>{{ $k->nama_kecamatan }}</option>@endforeach
        </select>
    </div>
    <div>
        <select name="sekolah_id" class="{{ $kelas }}">
            <option value="">Semua Sekolah</option>
            @foreach ($sekolah as $s)<option value="{{ $s->id }}" @selected(request('sekolah_id') === $s->id)>{{ $s->nama_sekolah }}</option>@endforeach
        </select>
    </div>
    <div>
        <select name="status" class="{{ $kelas }}">
            <option value="">Semua Status</option>
            @foreach (['LTM', 'DO'] as $o)<option value="{{ $o }}" @selected(request('status') === $o)>{{ $o }}</option>@endforeach
        </select>
    </div>
    <div>
        <select name="prioritas" class="{{ $kelas }}">
            <option value="">Semua Prioritas</option>
            @foreach (['Prioritas 1', 'Prioritas 2', 'Prioritas 3'] as $o)<option value="{{ $o }}" @selected(request('prioritas') === $o)>{{ $o }}</option>@endforeach
        </select>
    </div>
    <div>
        <select name="level" class="{{ $kelas }}">
            <option value="">Semua Level</option>
            @foreach (['Merah', 'Kuning', 'Hijau'] as $o)<option value="{{ $o }}" @selected(request('level') === $o)>{{ $o }}</option>@endforeach
        </select>
    </div>
    <div>
        <select name="status_penanganan" class="{{ $kelas }}">
            <option value="">Semua Penanganan</option>
            @foreach (['Baru', 'Dalam Proses', 'Selesai - Kembali Sekolah', 'Selesai - Tidak Berhasil'] as $o)<option value="{{ $o }}" @selected(request('status_penanganan') === $o)>{{ $o }}</option>@endforeach
        </select>
    </div>
    <div class="col-span-2 flex gap-2 md:col-span-1">
        <button type="submit" class="flex-1 rounded-lg bg-lebak-blue px-4 py-2 text-sm font-bold text-white transition hover:bg-lebak-dark">Filter</button>
        @if (request()->anyFilled(['cari', 'kecamatan_id', 'sekolah_id', 'status', 'prioritas', 'level', 'status_penanganan']))
            <a href="{{ $aksi }}" class="flex items-center px-2 text-sm text-slate-500 hover:text-slate-700">Reset</a>
        @endif
    </div>
</form>
