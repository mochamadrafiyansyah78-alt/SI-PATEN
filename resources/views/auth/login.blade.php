<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Masuk · SI-PATEN</title>
    @include('partials.head-assets')
</head>
<body class="bg-lebak-mist text-slate-800 antialiased">
<div class="flex min-h-screen">
    @include('auth._panel')

    <main class="flex flex-1 flex-col">
        <div class="flex items-center gap-3 bg-lebak-blue px-6 py-4 text-white lg:hidden">
            @include('partials.logo', ['ukuran' => 'h-9 w-9'])
            <p class="font-bold">SI-PATEN <span class="font-normal text-white/70">· Kab. Lebak</span></p>
        </div>

        <div class="flex flex-1 items-center justify-center px-6 py-10">
            <div class="w-full max-w-md">
                <span class="mb-3 block h-1 w-10 rounded bg-lebak-gold"></span>
                <h2 class="text-2xl font-extrabold text-slate-900">Masuk ke SI-PATEN</h2>
                <p class="mt-1 text-sm text-slate-500">Admin Dinas dan Pengawas Sekolah masuk melalui halaman ini.</p>

                @if ($errors->any())
                    <div class="mt-5 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">{{ $errors->first() }}</div>
                @endif

                <form method="POST" action="{{ route('login') }}" class="mt-6 space-y-5 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    @csrf
                    <div>
                        <label class="mb-1.5 block text-sm font-semibold text-slate-700">Surel</label>
                        <input type="email" name="email" value="{{ old('email') }}" required autofocus placeholder="nama@dindik-lebak.go.id"
                               class="w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm focus:border-lebak-blue focus:outline-none focus:ring-2 focus:ring-lebak-blue/25">
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-semibold text-slate-700">Kata Sandi</label>
                        <div class="relative">
                            <input id="password" type="password" name="password" required
                                   class="w-full rounded-lg border border-slate-300 px-3.5 py-2.5 pr-16 text-sm focus:border-lebak-blue focus:outline-none focus:ring-2 focus:ring-lebak-blue/25">
                            <button type="button" id="toggle-pass" class="absolute inset-y-0 right-3 text-xs font-semibold text-lebak-blue">Lihat</button>
                        </div>
                    </div>
                    <label class="flex items-center gap-2 text-sm text-slate-600">
                        <input type="checkbox" name="remember" class="rounded border-slate-300 text-lebak-blue focus:ring-lebak-blue"> Ingat saya di perangkat ini
                    </label>
                    <button type="submit" class="w-full rounded-lg bg-lebak-blue py-2.5 text-sm font-bold text-white transition hover:bg-lebak-dark">Masuk</button>
                </form>

                <p class="mt-6 text-center text-sm text-slate-500">
                    Pengawas sekolah baru? <a href="{{ route('register') }}" class="font-semibold text-lebak-gold hover:underline">Buat akun</a>
                </p>
            </div>
        </div>
    </main>
</div>
<script>
    document.getElementById('toggle-pass').addEventListener('click', function () {
        const input = document.getElementById('password');
        const tampil = input.type === 'password';
        input.type = tampil ? 'text' : 'password';
        this.textContent = tampil ? 'Sembunyi' : 'Lihat';
    });
</script>
</body>
</html>