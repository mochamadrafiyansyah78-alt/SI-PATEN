<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, HasUuids, Notifiable;

    protected $fillable = [
        'nama_lengkap', 'email', 'password', 'role', 'sekolah_id', 'is_active',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function sekolah(): BelongsTo
    {
        return $this->belongsTo(Sekolah::class);
    }

    public function isAdminDinas(): bool
    {
        return $this->role === 'admin_dinas';
    }

    /** Nama route beranda sesuai role, dipakai redirect setelah login. */
    public function beranda(): string
    {
        return match ($this->role) {
            'admin_dinas' => 'admin.dashboard',
            'verifikator' => 'verifikator.verifikasi.index',
            default => 'pengawas.dashboard',
        };
    }

    public function isVerifikator(): bool
    {
        return $this->role === 'verifikator';
    }

    public function isPengawasSekolah(): bool
    {
        return $this->role === 'pengawas_sekolah';
    }
}