<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

class UserFactory extends Factory
{
    public function definition(): array
    {
        return [
            'nama_lengkap' => fake()->name(),
            'email'        => fake()->unique()->safeEmail(),
            'password'     => Hash::make('password'),
            'role'         => 'pengawas_sekolah',
            'is_active'    => true,
        ];
    }
}