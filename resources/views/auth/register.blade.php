<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Daftar Pengawas · SI-PATEN</title>
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

        <div class="flex flex-1 items-start justify-center px-6 py-10 lg:items-center">
            <div class="w-full max-w-md">
                <span class="mb-3 block h-1 w-10 rounded bg-lebak-gold"></span>
                <h2 class="text-2xl font-extrabold text-slate-900">Daftar Akun Pengawas</h2>
                <p class="mt-1 text-sm text-slate-500">Akun langsung terhubung ke sekolah binaan Anda.</p>

                @if ($errors->any())
                    <div class="mt-5 space-y-1 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                        @foreach ($errors->all() as $error) <p>{{ $error }}</p> @endforeach
                    </div>
                @endif

                <form method="POST" action="{{ route('register') }}" class="mt-6 space-y-4 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    @csrf
                    <div>
                        <label class="mb-1.5 block text-sm font-semibold text-slate-700">Nama Lengkap</label>
                        <input type="text" name="nama_lengkap" value="{{ old('nama_lengkap') }}" required autofocus
                               class="w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm focus:border-lebak-blue focus:outline-none focus:ring-2 focus:ring-lebak-blue/25">
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-semibold text-slate-700">Surel</label>
                        <input type="email" name="email" value="{{ old('email') }}" required
                               class="w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm focus:border-lebak-blue focus:outline-none focus:ring-2 focus:ring-lebak-blue/25">
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-semibold text-slate-700">Sekolah</label>
                        <select name="sekolah_id" required
                                class="w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2.5 text-sm focus:border-lebak-blue focus:outline-none focus:ring-2 focus:ring-lebak-blue/25">
                            <option value="" disabled selected>Pilih sekolah Anda</option>
                            @foreach ($sekolah as $s)
                                <option value="{{ $s->id }}" @selected(old('sekolah_id') === $s->id)>{{ $s->nama_sekolah }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="mb-1.5 block text-sm font-semibold text-slate-700">Kata Sandi</label>
                            <input type="password" name="password" required minlength="8"
                                   class="w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm focus:border-lebak-blue focus:outline-none focus:ring-2 focus:ring-lebak-blue/25">
                        </div>
                        <div>
                            <label class="mb-1.5 block text-sm font-semibold text-slate-700">Ulangi Sandi</label>
                            <input type="password" name="password_confirmation" required minlength="8"
                                   class="w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm focus:border-lebak-blue focus:outline-none focus:ring-2 focus:ring-lebak-blue/25">
                        </div>
                    </div>
                    <p class="text-xs text-slate-400">Minimal 8 karakter.</p>
                    <button type="submit" class="w-full rounded-lg bg-lebak-blue py-2.5 text-sm font-bold text-white transition hover:bg-lebak-dark">Daftar</button>
                </form>

                <p class="mt-6 text-center text-sm text-slate-500">
                    Sudah punya akun? <a href="{{ route('login') }}" class="font-semibold text-lebak-gold hover:underline">Masuk di sini</a>
                </p>
            </div>
        </div>
    </main>
</div>
</body>
</html>