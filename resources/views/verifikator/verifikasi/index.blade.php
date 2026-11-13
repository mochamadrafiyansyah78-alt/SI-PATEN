@extends('layouts.app')
@section('title', 'Verifikasi Laporan Masuk')

@section('content')
<div class="flex items-center gap-3 mb-6">
    <div class="w-11 h-11 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center shrink-0">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
    </div>
    <div>
        <h1 class="text-2xl font-semibold text-slate-900">Verifikasi Data Masuk ({{ $total }})</h1>
        <p class="text-sm text-slate-500 mt-1">Centang beberapa data lalu setujui atau tolak sekaligus. Data yang disetujui diteruskan ke pengawas untuk validasi lapangan.</p>
    </div>
</div>

@include('partials.filter-data', ['aksi' => route('verifikator.verifikasi.index'), 'kecamatan' => \App\Models\Kecamatan::orderBy('nama_kecamatan')->get(), 'sekolah' => \App\Models\Sekolah::orderBy('nama_sekolah')->get()])

{{-- Form kosong: checkbox, catatan, dan aksi dikaitkan lewat atribut form="form-massal" supaya tidak ada form bersarang --}}
<form id="form-massal" method="POST" action="{{ route('verifikator.verifikasi.aksi-massal') }}">
    @csrf
    <input type="hidden" name="aksi" id="aksi-massal" value="">
</form>

<div class="overflow-hidden rounded-xl border border-slate-200 bg-white">
  <div class="overflow-x-auto">
    <table class="w-full text-sm">
        <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
            <tr>
                <th class="w-12 px-4 py-3">
                    <input type="checkbox" id="pilih-semua" class="h-4 w-4 rounded border-slate-300" aria-label="Pilih semua data di halaman ini">
                </th>
                <th class="px-4 py-3">Siswa</th>
                <th class="px-4 py-3">Sekolah / Kecamatan</th>
                <th class="px-4 py-3">Status</th>
                <th class="px-4 py-3">Prioritas</th>
                <th class="px-4 py-3">Alasan</th>
                <th class="px-4 py-3">Dilaporkan</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @forelse ($data as $item)
                <tr class="transition hover:bg-slate-50/70">
                    <td class="px-4 py-3">
                        <input type="checkbox" name="ids[]" value="{{ $item->id }}" form="form-massal"
                               class="pilih-baris h-4 w-4 rounded border-slate-300" aria-label="Pilih {{ $item->nama_siswa }}">
                    </td>
                    <td class="px-4 py-3">
                        <a href="{{ route('verifikator.verifikasi.show', $item) }}" class="font-semibold text-slate-900 hover:text-teal-700 hover:underline">{{ $item->nama_siswa }}</a>
                        <p class="text-xs text-slate-400">NISN: {{ $item->nisn ?? '-' }}</p>
                    </td>
                    <td class="px-4 py-3 text-slate-600">
                        {{ $item->sekolah->nama_sekolah ?? '—' }}
                        <p class="text-xs text-slate-400">{{ $item->kecamatanDomisili->nama_kecamatan ?? '—' }}</p>
                    </td>
                    <td class="px-4 py-3">
                        <span class="chip {{ $item->status === 'DO' ? 'bg-rose-50 text-rose-700' : 'bg-amber-50 text-amber-700' }}">{{ $item->status }}</span>
                    </td>
                    <td class="px-4 py-3 text-slate-600">{{ $item->prioritas_penjangkauan }}</td>
                    <td class="px-4 py-3 text-slate-600">{{ $item->alasan_approval ?? '—' }}</td>
                    <td class="px-4 py-3 text-xs text-slate-500">
                        {{ $item->inputOleh->nama_lengkap ?? '—' }}
                        <p>{{ $item->created_at->translatedFormat('d M Y, H:i') }}</p>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="px-5 py-12 text-center text-slate-400">Tidak ada laporan yang menunggu verifikasi saat ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
  </div>
</div>

<div class="mt-4">{{ $data->links() }}</div>

{{-- Bar aksi massal: muncul saat ada baris terpilih --}}
<div id="bar-massal" class="sticky bottom-4 z-20 mt-4 hidden rounded-xl border border-slate-200 bg-white p-4 shadow-lg">
    <div class="flex flex-wrap items-center gap-3">
        <p class="text-sm text-slate-700"><span id="jumlah-dipilih" class="font-bold">0</span> data dipilih</p>
        <div class="ml-auto flex gap-2">
            <button type="button" id="btn-setujui-massal" class="rounded-lg bg-teal-700 px-4 py-2 text-sm font-medium text-white hover:bg-teal-800">Setujui Terpilih</button>
            <button type="button" id="btn-tolak-massal" class="rounded-lg border border-rose-300 px-4 py-2 text-sm font-medium text-rose-600 hover:bg-rose-50">Tolak Terpilih</button>
        </div>
    </div>
    <div id="panel-tolak" class="mt-3 hidden gap-2">
        <input type="text" id="catatan-massal" name="catatan_verifikasi" form="form-massal" maxlength="500"
               placeholder="Alasan penolakan (wajib, berlaku untuk semua data terpilih)"
               class="flex-1 rounded-lg border border-slate-300 px-3 py-2 text-sm">
        <button type="button" id="btn-konfirmasi-tolak" class="rounded-lg bg-rose-600 px-4 py-2 text-sm font-medium text-white hover:bg-rose-700">Kirim Penolakan</button>
    </div>
</div>

<script>
(() => {
    const formMassal = document.getElementById('form-massal');
    const inputAksi = document.getElementById('aksi-massal');
    const kotakSemua = document.getElementById('pilih-semua');
    const barMassal = document.getElementById('bar-massal');
    const penghitung = document.getElementById('jumlah-dipilih');
    const panelTolak = document.getElementById('panel-tolak');
    const inputCatatan = document.getElementById('catatan-massal');

    const semuaBaris = () => Array.from(document.querySelectorAll('.pilih-baris'));
    const barisTerpilih = () => semuaBaris().filter((kotak) => kotak.checked);

    function tutupPanelTolak() {
        panelTolak.classList.add('hidden');
        panelTolak.classList.remove('flex');
        inputCatatan.value = '';
    }

    function sinkronkanTampilan() {
        const jumlahTerpilih = barisTerpilih().length;
        const jumlahTotal = semuaBaris().length;

        penghitung.textContent = jumlahTerpilih;
        barMassal.classList.toggle('hidden', jumlahTerpilih === 0);
        kotakSemua.checked = jumlahTotal > 0 && jumlahTerpilih === jumlahTotal;
        kotakSemua.indeterminate = jumlahTerpilih > 0 && jumlahTerpilih < jumlahTotal;

        if (jumlahTerpilih === 0) tutupPanelTolak();
    }

    function kirimAksi(nilaiAksi) {
        inputAksi.value = nilaiAksi;
        formMassal.requestSubmit();
    }

    kotakSemua.addEventListener('change', () => {
        semuaBaris().forEach((kotak) => { kotak.checked = kotakSemua.checked; });
        sinkronkanTampilan();
    });

    document.addEventListener('change', (event) => {
        if (event.target.classList.contains('pilih-baris')) sinkronkanTampilan();
    });

    document.getElementById('btn-setujui-massal').addEventListener('click', () => {
        const jumlah = barisTerpilih().length;
        if (jumlah && confirm(`Setujui ${jumlah} data terpilih? Data akan diteruskan ke pengawas untuk validasi lapangan.`)) {
            kirimAksi('setujui');
        }
    });

    document.getElementById('btn-tolak-massal').addEventListener('click', () => {
        panelTolak.classList.remove('hidden');
        panelTolak.classList.add('flex');
        inputCatatan.focus();
    });

    document.getElementById('btn-konfirmasi-tolak').addEventListener('click', () => {
        if (!inputCatatan.value.trim()) {
            inputCatatan.focus();
            return;
        }
        const jumlah = barisTerpilih().length;
        if (jumlah && confirm(`Tolak ${jumlah} data terpilih dengan alasan tersebut?`)) {
            kirimAksi('tolak');
        }
    });

    window.addEventListener('pageshow', sinkronkanTampilan);
    sinkronkanTampilan();
})();
</script>
@endsection
