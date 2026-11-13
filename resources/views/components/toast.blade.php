{{-- Toast global: flash session, galat validasi, gangguan jaringan, dan cegah klik ganda --}}
<div id="toast-wrap" class="pointer-events-none fixed right-4 top-4 z-[60] flex w-[calc(100%-2rem)] max-w-sm flex-col gap-2" aria-live="polite"></div>
<script>
(function () {
    const wrap = document.getElementById('toast-wrap');
    const GAYA = {
        success: ['#E6F4EC', '#1f6b48', '#62B38B'],
        error: ['#fff1f2', '#be123c', '#e11d48'],
        warning: ['#FBF6E6', '#77612A', '#C9A24B'],
    };
    function toast(pesan, tipe = 'success', ms = 6000) {
        if (!pesan) return;
        const [bg, fg, garis] = GAYA[tipe] || GAYA.success;
        const el = document.createElement('div');
        el.className = 'pointer-events-auto rounded-xl px-4 py-3 text-sm font-medium shadow-lg';
        el.style.cssText = `background:${bg};color:${fg};border-left:4px solid ${garis}`;
        el.textContent = pesan;
        el.addEventListener('click', () => el.remove());
        wrap.appendChild(el);
        setTimeout(() => el.remove(), ms);
    }
    window.toast = toast;

    @if (session('success')) toast(@js(session('success')), 'success'); @endif
    @if (session('error')) toast(@js(session('error')), 'error'); @endif
    @if ($errors->any()) toast(@js($errors->first()), 'error', 8000); @endif

    // Jaringan putus / server bermasalah
    window.addEventListener('offline', () => toast('Koneksi internet terputus. Periksa jaringan Anda.', 'error', 8000));
    window.addEventListener('online', () => toast('Koneksi pulih kembali.', 'success'));
    window.addEventListener('unhandledrejection', (e) => {
        if (e.reason instanceof TypeError) toast('Tidak dapat terhubung ke server. Coba lagi sebentar lagi.', 'error', 8000);
    });
    const asliFetch = window.fetch;
    window.fetch = async (...args) => {
        try {
            const r = await asliFetch(...args);
            if (r.status >= 500) toast('Server sedang bermasalah. Silakan coba beberapa saat lagi.', 'error', 8000);
            else if (r.status === 419) toast('Sesi berakhir. Muat ulang halaman lalu coba lagi.', 'warning', 8000);
            else if (r.status === 429) toast('Terlalu banyak permintaan. Tunggu sebentar.', 'warning', 8000);
            return r;
        } catch (err) {
            toast('Tidak dapat terhubung ke server. Periksa koneksi Anda.', 'error', 8000);
            throw err;
        }
    };

    // Cegah submit ganda (jalan setelah handler modal konfirmasi)
    document.addEventListener('submit', (e) => {
        if (e.defaultPrevented) return;
        const f = e.target;
        if (f.method && f.method.toLowerCase() === 'get') return;
        f.querySelectorAll('button[type=submit],button:not([type])').forEach((b) => {
            setTimeout(() => { b.disabled = true; b.dataset.teks = b.textContent; b.textContent = 'Memproses…'; }, 0);
        });
    });
    // Tombol "kembali" dari cache: aktifkan lagi tombol
    window.addEventListener('pageshow', (e) => {
        if (!e.persisted) return;
        document.querySelectorAll('button[data-teks]').forEach((b) => { b.disabled = false; b.textContent = b.dataset.teks; });
    });
})();
</script>
