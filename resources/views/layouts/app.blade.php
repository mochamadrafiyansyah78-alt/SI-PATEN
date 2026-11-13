<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'SI-PATEN') · SI-PATEN</title>
    @include('partials.head-assets')
</head>
<body class="bg-lebak-mist text-slate-800 antialiased">
@auth
@php
    $ikon = [
        'dashboard' => 'M2.25 12l8.954-8.955a1.126 1.126 0 011.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25',
        'list' => 'M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5',
        'warning' => 'M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z',
        'check' => 'M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
        'swap' => 'M7.5 21L3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5',
        'map' => 'M9 6.75V15m6-6v8.25m.503 3.498l4.875-2.437c.381-.19.622-.58.622-1.006V4.82c0-.836-.88-1.38-1.628-1.006l-3.869 1.934c-.317.159-.69.159-1.006 0L9.503 3.252a1.125 1.125 0 00-1.006 0L3.622 5.689C3.24 5.88 3 6.27 3 6.695V19.18c0 .836.88 1.38 1.628 1.006l3.869-1.934c.317-.159.69-.159 1.006 0l4.994 2.497c.317.158.69.158 1.006 0z',
        'doc' => 'M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z',
        'gear' => 'M10.5 6h9.75M10.5 6a1.5 1.5 0 11-3 0m3 0a1.5 1.5 0 10-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-9.75 0h9.75',
    ];
    $icon = fn ($k) => '<svg xmlns="http://www.w3.org/2000/svg" class="h-[18px] w-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="' . ($ikon[$k] ?? '') . '"/></svg>';

    $menuAdmin = [
        ['dashboard', 'admin.dashboard', 'Dashboard', 'admin.dashboard'],
        ['list', 'admin.data-ats.*', 'Data ATS', 'admin.data-ats.index'],
        ['swap', 'admin.mutasi.*', 'Mutasi', 'admin.mutasi.index'],
        ['map', 'admin.peta-sebaran', 'Peta Sebaran', 'admin.peta-sebaran'],
        ['doc', 'admin.laporan.*', 'Laporan Bulanan', 'admin.laporan.index'],
        ['gear', 'admin.pengaturan.*', 'Pengaturan', 'admin.pengaturan.akun'],
    ];
    $menuPengawas = [
        ['dashboard', 'pengawas.dashboard', 'Dashboard', 'pengawas.dashboard'],
        ['check', 'pengawas.validasi.*', 'Validasi Lapangan', 'pengawas.validasi.index'],
        ['list', 'pengawas.data-ats.*', 'Data ATS', 'pengawas.data-ats.index'],
        ['warning', 'pengawas.ews.*', 'Early Warning', 'pengawas.ews.index'],
        ['swap', 'pengawas.mutasi.*', 'Mutasi', 'pengawas.mutasi.index'],
    ];
    $menuVerifikator = [
        ['check', 'verifikator.verifikasi.*', 'Verifikasi', 'verifikator.verifikasi.index'],
    ];
    $menu = match (auth()->user()->role) {
        'admin_dinas' => $menuAdmin,
        'verifikator' => $menuVerifikator,
        default => $menuPengawas,
    };
    $labelRole = match (auth()->user()->role) { 'admin_dinas' => 'Admin Dinas', 'verifikator' => 'Verifikator', default => 'Pengawas Sekolah' };
    $unread = auth()->user()->unreadNotifications;
@endphp

<div class="flex min-h-screen">
    <aside id="sidebar" class="fixed inset-y-0 left-0 z-40 flex w-64 -translate-x-full flex-col bg-gradient-to-b from-lebak-blue to-lebak-dark text-white transition-transform lg:translate-x-0">
        <div class="flex h-16 shrink-0 items-center gap-3 border-b border-white/10 px-5">
            @include('partials.logo', ['ukuran' => 'h-10 w-10'])
            <div class="leading-tight">
                <p class="font-bold tracking-tight">SI-PATEN</p>
                <p class="text-[11px] text-white/60">Kabupaten Lebak</p>
            </div>
        </div>

        <nav class="flex-1 space-y-1 overflow-y-auto px-3 py-4">
            <p class="px-3 pb-1 text-[11px] font-semibold uppercase tracking-wider text-white/40">Menu</p>
            @foreach ($menu as [$ic, $pola, $label, $href])
                @php $aktif = request()->routeIs($pola); @endphp
                <a href="{{ route($href) }}" class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition {{ $aktif ? 'bg-white text-lebak-blue shadow-md' : 'text-white/75 hover:bg-white/10 hover:text-white' }}">
                    <span class="flex h-8 w-8 items-center justify-center rounded-md {{ $aktif ? 'bg-lebak-blue/10' : 'bg-white/10' }}">{!! $icon($ic) !!}</span>
                    {{ $label }}
                    @if ($label === 'Verifikasi')
                        @php $menunggu = \App\Models\DataAts::where('status_verifikasi', 'Menunggu Verifikasi')->count(); @endphp
                        @if ($menunggu > 0)
                            <span class="ml-auto rounded-full bg-[#e3c97a] px-1.5 py-0.5 text-[10px] font-bold leading-none text-lebak-dark">{{ $menunggu }}</span>
                        @endif
                    @endif
                </a>
            @endforeach
        </nav>

        <div class="shrink-0 border-t border-white/10 p-4">
            <div class="mb-3 flex items-center gap-2.5 rounded-xl bg-white/10 p-3">
                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-lebak-green text-sm font-bold text-lebak-dark">{{ Str::substr(auth()->user()->nama_lengkap, 0, 1) }}</div>
                <div class="min-w-0 leading-tight">
                    <p class="truncate text-sm font-semibold">{{ auth()->user()->nama_lengkap }}</p>
                    <p class="text-xs text-white/60">{{ $labelRole }}</p>
                </div>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="w-full rounded-lg bg-white/10 px-3 py-2 text-left text-sm font-medium text-white/85 transition hover:bg-rose-500">Keluar</button>
            </form>
        </div>
    </aside>

    <div id="overlay" class="fixed inset-0 z-30 hidden bg-lebak-dark/50 lg:hidden"></div>

    <div class="flex min-w-0 flex-1 flex-col lg:ml-64">
        <header class="sticky top-0 z-20 bg-white shadow-sm">
            <div class="flex h-1"><div class="flex-1 bg-lebak-blue"></div><div class="flex-1 bg-lebak-gold"></div><div class="flex-1 bg-lebak-green"></div></div>
            <div class="flex h-16 items-center justify-between px-5">
                <div class="flex items-center gap-3">
                    <button id="toggle-sidebar" class="text-slate-500 lg:hidden">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5M3.75 17.25h16.5"/></svg>
                    </button>
                    <span class="lg:hidden">@include('partials.logo', ['ukuran' => 'h-9 w-9'])</span>
                    <div class="leading-tight">
                        <p class="text-[11px] uppercase tracking-wider text-slate-400">Portal Data ATS Kab. Lebak</p>
                        <h2 class="text-base font-bold text-slate-900">@yield('title', 'SI-PATEN')</h2>
                    </div>
                </div>

                <div class="relative">
                    <button type="button" onclick="document.getElementById('dropdown-notif').classList.toggle('hidden')" class="relative flex h-10 w-10 items-center justify-center rounded-full bg-lebak-mist text-lebak-blue hover:bg-slate-200">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0"/></svg>
                        @if ($unread->count() > 0)
                            <span class="absolute -right-0.5 -top-0.5 flex h-4 w-4 items-center justify-center rounded-full bg-rose-600 text-[10px] font-bold text-white">{{ $unread->count() }}</span>
                        @endif
                    </button>
                    <div id="dropdown-notif" class="absolute right-0 z-20 mt-2 hidden max-h-96 w-80 overflow-y-auto rounded-xl border border-slate-200 bg-white shadow-xl">
                        <p class="border-b border-slate-100 px-4 py-3 text-sm font-bold text-slate-800">Notifikasi</p>
                        @forelse ($unread as $notif)
                            <form method="POST" action="{{ route('notifikasi.baca', $notif->id) }}" class="border-b border-slate-100 last:border-0">
                                @csrf
                                <button type="submit" class="w-full p-3 text-left text-sm hover:bg-lebak-cream">{{ $notif->data['pesan'] ?? 'Notifikasi baru' }}</button>
                            </form>
                        @empty
                            <p class="p-4 text-sm text-slate-400">Tidak ada notifikasi baru.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </header>

        <main class="flex-1 p-6">
            @yield('content')
        </main>
    </div>
</div>

<div id="confirm-modal" class="fixed inset-0 z-50 hidden items-center justify-center p-4" style="background:rgba(0,57,95,.55);backdrop-filter:blur(3px)">
    <div class="modal-pop w-full max-w-sm overflow-hidden rounded-2xl bg-white shadow-2xl">
        <div id="confirm-ribbon" class="h-1.5"></div>
        <div class="p-6 text-center">
            <div id="confirm-icon" class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full"></div>
            <h3 id="confirm-title" class="text-lg font-extrabold text-slate-900"></h3>
            <p id="confirm-text" class="mt-1.5 text-sm leading-relaxed text-slate-500"></p>
            <div class="mt-6 flex gap-3">
                <button type="button" id="confirm-cancel" class="flex-1 rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-50">Batal</button>
                <button type="button" id="confirm-ok" class="flex-1 rounded-xl px-4 py-2.5 text-sm font-bold"></button>
            </div>
        </div>
    </div>
</div>

<script>
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('overlay');
    document.getElementById('toggle-sidebar')?.addEventListener('click', () => {
        sidebar.classList.toggle('-translate-x-full');
        overlay.classList.toggle('hidden');
    });
    overlay?.addEventListener('click', () => {
        sidebar.classList.add('-translate-x-full');
        overlay.classList.add('hidden');
    });
    document.addEventListener('click', (e) => {
        const dd = document.getElementById('dropdown-notif');
        if (!dd || dd.classList.contains('hidden')) return;
        if (!e.target.closest('#dropdown-notif') && !e.target.closest('button[onclick*="dropdown-notif"]')) dd.classList.add('hidden');
    });

    // Popup konfirmasi: menggantikan confirm() bawaan browser
    const modal = document.getElementById('confirm-modal');
    const svg = (d) => '<svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="' + d + '"/></svg>';
    const VARIAN = {
        danger:  { ribbon: '#e11d48', iconBg: '#fff1f2', iconColor: '#e11d48', title: 'Yakin ingin menghapus?', label: 'Ya, hapus', btnBg: '#e11d48', btnColor: '#fff',
                   icon: svg('M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0') },
        warning: { ribbon: '#77612A', iconBg: '#F7F3E8', iconColor: '#77612A', title: 'Konfirmasi tindakan', label: 'Ya, lanjutkan', btnBg: '#77612A', btnColor: '#fff',
                   icon: svg('M12 9v3.75m0 3.75h.007v.008H12v-.008zM21 12a9 9 0 11-18 0 9 9 0 0118 0z') },
        success: { ribbon: '#62B38B', iconBg: '#E6F4EC', iconColor: '#2f8a62', title: 'Konfirmasi persetujuan', label: 'Ya, setujui', btnBg: '#62B38B', btnColor: '#0b3d2a',
                   icon: svg('M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z') },
    };
    let formTarget = null;

    const pilihVarian = (teks) => {
        const t = teks.toLowerCase();
        if (/hapus|tolak/.test(t)) return 'danger';
        if (/setujui/.test(t)) return 'success';
        return 'warning';
    };
    function bukaModal(form) {
        const v = VARIAN[pilihVarian(form.dataset.confirm)];
        formTarget = form;
        document.getElementById('confirm-ribbon').style.background = v.ribbon;
        const ic = document.getElementById('confirm-icon');
        ic.style.background = v.iconBg; ic.style.color = v.iconColor; ic.innerHTML = v.icon;
        document.getElementById('confirm-title').textContent = v.title;
        document.getElementById('confirm-text').textContent = form.dataset.confirm;
        const ok = document.getElementById('confirm-ok');
        ok.textContent = v.label; ok.style.background = v.btnBg; ok.style.color = v.btnColor;
        modal.classList.remove('hidden'); modal.classList.add('flex');
    }
    function tutupModal() { modal.classList.add('hidden'); modal.classList.remove('flex'); formTarget = null; }

    document.querySelectorAll('form[onsubmit*="confirm("]').forEach((f) => {
        const m = f.getAttribute('onsubmit').match(/confirm\((['"])([\s\S]*)\1\)/);
        if (!m) return;
        f.dataset.confirm = m[2];
        f.removeAttribute('onsubmit');
    });
    document.addEventListener('submit', (e) => {
        if (!e.target.dataset || !e.target.dataset.confirm) return;
        e.preventDefault();
        bukaModal(e.target);
    });
    document.getElementById('confirm-ok').addEventListener('click', () => { const f = formTarget; tutupModal(); if (f) f.submit(); });
    document.getElementById('confirm-cancel').addEventListener('click', tutupModal);
    modal.addEventListener('click', (e) => { if (e.target === modal) tutupModal(); });
    document.addEventListener('keydown', (e) => { if (e.key === 'Escape') tutupModal(); });
</script>
@endauth

@guest
    @yield('content')
@endguest

<x-toast />
</body>
</html>