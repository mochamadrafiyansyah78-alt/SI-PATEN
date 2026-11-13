@extends('layouts.app')
@section('title', 'Catat Mutasi Siswa')

@section('content')
<h1 class="text-2xl font-semibold text-slate-900 mb-6">Catat Mutasi Siswa</h1>

<div class="bg-white rounded-xl border border-slate-200 p-5 max-w-xl">
    @if ($errors->any())
        <div class="mb-4 rounded-lg bg-rose-50 border border-rose-200 text-rose-700 text-sm px-4 py-3 space-y-1">
            @foreach ($errors->all() as $e) <p>{{ $e }}</p> @endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('pengawas.mutasi.store') }}" class="space-y-4">
        @csrf
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Nama Siswa</label>
            <input type="text" name="nama_siswa" value="{{ old('nama_siswa') }}" required class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm">
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">NISN</label>
            <input type="text" name="nisn" value="{{ old('nisn') }}" required maxlength="10" inputmode="numeric" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm">
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Kecamatan Domisili</label>
            <select name="kecamatan_domisili_id" required class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm bg-white">
                <option value="" disabled selected>Pilih kecamatan</option>
                @foreach ($kecamatan as $k)
                    <option value="{{ $k->id }}" @selected(old('kecamatan_domisili_id') === $k->id)>{{ $k->nama_kecamatan }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Sekolah Asal</label>
            <input type="text" name="sekolah_asal" value="{{ old('sekolah_asal') }}" required placeholder="Ketik nama sekolah asal"
                   class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm">
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Sekolah Tujuan</label>
            <input type="text" name="sekolah_tujuan" value="{{ old('sekolah_tujuan') }}" required placeholder="Ketik nama sekolah tujuan"
                   class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm">
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Tanggal Mutasi</label>
            <input type="date" name="tanggal_mutasi" value="{{ old('tanggal_mutasi', date('Y-m-d')) }}" required class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm">
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Keterangan (opsional)</label>
            <textarea name="keterangan" rows="2" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm">{{ old('keterangan') }}</textarea>
        </div>
        <button type="submit" class="bg-teal-700 hover:bg-teal-800 text-white rounded-lg px-5 py-2 text-sm font-medium">Simpan</button>
    </form>
</div>
@endsection