<?php

namespace Database\Seeders;

use App\Models\Kecamatan;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 28 Kecamatan Kabupaten Lebak
        $daftarKecamatan = [
            'Banjarsari', 'Bayah', 'Bojongmanik', 'Cibadak', 'Cibeber',
            'Cigemblong', 'Cihara', 'Cijaku', 'Cikulur', 'Cileles',
            'Cilograng', 'Cimarga', 'Cipanas', 'Cirinten', 'Curug bitung',
            'Gunung kencana', 'Kalanganyar', 'Lebakgedong', 'Leuwidamar', 'Maja',
            'Malingping', 'Muncang', 'Panggarangan', 'Rangkasbitung', 'Sajira',
            'Sobang', 'Wanasalam', 'Warunggunung',
        ];

        foreach ($daftarKecamatan as $nama) {
            Kecamatan::firstOrCreate(['nama_kecamatan' => $nama]);
        }

        // Akun Admin Dinas Pendidikan
        User::firstOrCreate(
            ['email' => 'admin@dindik-lebak.go.id'],
            [
                'nama_lengkap' => 'Admin Dinas Pendidikan',
                'password'     => Hash::make('password'),
                'role'         => 'admin_dinas',
                'is_active'    => true,
            ]
        );
    }
}