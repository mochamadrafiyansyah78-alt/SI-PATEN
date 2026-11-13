@extends('layouts.app')
@section('title', 'Edit Sekolah')

@section('content')
<a href="{{ route('admin.pengaturan.sekolah') }}" class="text-sm text-slate-500 hover:text-slate-700 inline-flex items-center gap-1 mb-4">&larr; Kembali</a>

<div class="bg-white rounded-xl border border-slate-200 p-5 max-w-xl">
    <h1 class="text-lg font-semibold text-slate-900 mb-4">{{ $sekolah->nama_sekolah }} <span class="text-slate-400 text-sm font-normal">· NPSN {{ $sekolah->npsn }}</span></h1>

    @if ($errors->any())
        <div class="mb-4 rounded-lg bg-rose-50 border border-rose-200 text-rose-700 text-sm px-4 py-3 space-y-1">
            @foreach ($errors->all() as $error) <p>{{ $error }}</p> @endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('admin.pengaturan.sekolah.update', $sekolah) }}" class="space-y-4">
        @csrf @method('PUT')

        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Nama Sekolah</label>
            <input type="text" name="nama_sekolah" value="{{ old('nama_sekolah', $sekolah->nama_sekolah) }}" required
                   class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm">
        </div>

        <div class="grid grid-cols-2 gap-3">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Status Sekolah</label>
                <select name="status_sekolah" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm bg-white">
                    <option value="NEGERI" @selected(old('status_sekolah', $sekolah->status_sekolah) === 'NEGERI')>NEGERI</option>
                    <option value="SWASTA" @selected(old('status_sekolah', $sekolah->status_sekolah) === 'SWASTA')>SWASTA</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Bentuk Pendidikan</label>
                <input type="text" name="bentuk_pendidikan" value="{{ old('bentuk_pendidikan', $sekolah->bentuk_pendidikan) }}" required maxlength="10"
                       class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm">
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700 mb-2">Lokasi Sekolah</label>
            <div class="flex gap-4 text-sm mb-3">
                <label class="flex items-center gap-1.5">
                    <input type="radio" name="lokasi" value="lebak" onchange="document.getElementById('blok-lebak').classList.remove('hidden'); document.getElementById('blok-luar').classList.add('hidden')" @checked(old('lokasi', $sekolah->kecamatan_id ? 'lebak' : 'luar') === 'lebak')>
                    Dalam Kabupaten Lebak
                </label>
                <label class="flex items-center gap-1.5">
                    <input type="radio" name="lokasi" value="luar" onchange="document.getElementById('blok-luar').classList.remove('hidden'); document.getElementById('blok-lebak').classList.add('hidden')" @checked(old('lokasi', $sekolah->kecamatan_id ? 'lebak' : 'luar') === 'luar')>
                    Luar Kabupaten Lebak
                </label>
            </div>

            <div id="blok-lebak" class="{{ old('lokasi', $sekolah->kecamatan_id ? 'lebak' : 'luar') === 'luar' ? 'hidden' : '' }}">
                <select name="kecamatan_id" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm bg-white">
                    <option value="" disabled>Pilih kecamatan</option>
                    @foreach ($kecamatan as $k)
                        <option value="{{ $k->id }}" @selected(old('kecamatan_id', $sekolah->kecamatan_id) === $k->id)>{{ $k->nama_kecamatan }}</option>
                    @endforeach
                </select>
            </div>

            <div id="blok-luar" class="space-y-2 {{ old('lokasi', $sekolah->kecamatan_id ? 'lebak' : 'luar') === 'lebak' ? 'hidden' : '' }}">
                <input type="text" name="kecamatan_luar_nama" value="{{ old('kecamatan_luar_nama', $sekolah->kecamatan_luar_nama) }}" placeholder="Nama kecamatan"
                       class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm">
                <input type="text" name="kabupaten_sekolah" value="{{ old('kabupaten_sekolah', $sekolah->kabupaten_sekolah) }}" placeholder="Nama kabupaten"
                       class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm">
            </div>
        </div>

        <button type="submit" class="bg-teal-700 hover:bg-teal-800 text-white rounded-lg px-5 py-2 text-sm font-medium">Simpan</button>
    </form>
</div>
@endsection
