<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class BuatAkunVerifikator extends Command
{
    protected $signature = 'akun:verifikator {email} {nama} {--password=}';
    protected $description = 'Buat / ubah akun berperan verifikator';

    public function handle(): int
    {
        $password = $this->option('password') ?: Str::random(12);

        $user = User::firstOrNew(['email' => $this->argument('email')]);
        $user->nama_lengkap = $this->argument('nama');
        $user->password = Hash::make($password);
        $user->role = 'verifikator';
        $user->is_active = true;
        $user->save();

        $this->info("Akun verifikator siap: {$user->email}");
        if (! $this->option('password')) {
            $this->warn("Password sementara: {$password} (segera ganti)");
        }

        return self::SUCCESS;
    }
}
