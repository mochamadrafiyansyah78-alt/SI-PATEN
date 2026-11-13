<aside class="relative hidden flex-col justify-between overflow-hidden bg-lebak-blue p-12 text-white lg:sticky lg:top-0 lg:flex lg:h-screen lg:w-[48%]">
    @if (file_exists(public_path('images/lebak-bg.jpg')))
        <div class="absolute inset-0 bg-cover bg-center" style="background-image:url('{{ asset('images/lebak-bg.jpg') }}')"></div>
        <div class="absolute inset-0 bg-gradient-to-b from-lebak-blue/90 via-lebak-blue/80 to-lebak-dark/95"></div>
    @else
        <div class="absolute inset-0 bg-gradient-to-br from-lebak-blue to-lebak-dark"></div>
        @include('partials.kawung')
    @endif

    <div class="relative z-10">
        <div class="flex items-center gap-3">
            @if (file_exists(public_path('images/logo-lebak.png')))
                <img src="{{ asset('images/logo-lebak.png') }}" alt="Logo" class="h-11 w-auto">
            @else
                <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-white text-base font-extrabold text-lebak-blue">SP</div>
            @endif
            <div class="leading-tight">
                <p class="text-lg font-extrabold tracking-tight">SI-PATEN</p>
                <p class="text-xs text-white/70">Dinas Pendidikan Kabupaten Lebak</p>
            </div>
        </div>

        <h1 class="mt-12 max-w-md text-4xl font-extrabold leading-tight">Setiap anak berhak<br>kembali ke <span class="text-[#e3c97a]">bangku sekolah.</span></h1>
        <p class="mt-4 max-w-md text-sm leading-relaxed text-white/80">Sistem pemantauan Anak Tidak Sekolah untuk seluruh sekolah dan kecamatan di Kabupaten Lebak, dari pencatatan sampai penanganan.</p>

        <ol class="mt-8 max-w-sm space-y-3 text-sm">
            <li class="flex items-center gap-3"><span class="flex h-6 w-6 items-center justify-center rounded-full bg-white text-xs font-bold text-lebak-blue">1</span> Pengawas mencatat data siswa</li>
            <li class="flex items-center gap-3"><span class="flex h-6 w-6 items-center justify-center rounded-full bg-[#e3c97a] text-xs font-bold text-lebak-dark">2</span> Dinas memverifikasi laporan</li>
            <li class="flex items-center gap-3"><span class="flex h-6 w-6 items-center justify-center rounded-full bg-lebak-green text-xs font-bold text-lebak-dark">3</span> Kasus dipantau dan ditangani</li>
        </ol>
    </div>

    <svg class="pointer-events-none absolute bottom-0 left-0 w-full" viewBox="0 0 600 240" preserveAspectRatio="xMidYMax slice" aria-hidden="true">
        <circle cx="480" cy="60" r="30" fill="#E3C568" opacity=".95"/>
        <path d="M0 140 L90 90 L170 130 L260 62 L350 126 L440 92 L520 130 L600 104 V240 H0Z" fill="#1d6b78" opacity=".6"/>
        <path d="M0 180 Q150 132 300 176 T600 160 V240 H0Z" fill="#62B38B"/>
        <path d="M0 208 Q150 174 300 202 T600 192 V240 H0Z" fill="#4c9c75"/>
        <path d="M0 222 Q150 196 300 218 T600 210" fill="none" stroke="#3b8a63" stroke-width="3"/>
        <path d="M0 232 Q150 210 300 228 T600 222" fill="none" stroke="#3b8a63" stroke-width="3"/>
        <rect x="210" y="146" width="140" height="62" fill="#fff"/>
        <path d="M200 146 L280 106 L360 146Z" fill="#77612A"/>
        <rect x="268" y="172" width="24" height="36" fill="#00508F"/>
        <rect x="226" y="160" width="26" height="20" fill="#00508F"/>
        <rect x="308" y="160" width="26" height="20" fill="#00508F"/>
        <line x1="280" y1="106" x2="280" y2="78" stroke="#fff" stroke-width="2"/>
        <rect x="280" y="78" width="22" height="6" fill="#d6293e"/>
        <rect x="280" y="84" width="22" height="6" fill="#fff"/>
    </svg>
</aside>