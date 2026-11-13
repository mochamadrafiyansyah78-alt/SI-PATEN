<?php

namespace App\Console\Commands;

use App\Models\DataAts;
use App\Models\User;
use App\Notifications\RingkasanEwsHarianNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;

class KirimRingkasanEwsHarian extends Command
{
    /**
     * php artisan ews:ringkasan-harian
     * Jadwalkan di routes/console.php:
     *   Schedule::command('ews:ringkasan-harian')->dailyAt('07:00');
     */
    protected $signature = 'ews:ringkasan-harian';

    protected $description = 'Kirim ringkasan harian seluruh kasus level Merah ke Admin Dinas';

    public function handle(): int
    {
        $kasusMerah = DataAts::disetujui()
            ->with(['sekolah', 'kecamatanDomisili'])
            ->where('ews_level', 'Merah')
            ->whereNotIn('status_penanganan', ['Selesai - Kembali Sekolah', 'Selesai - Tidak Berhasil'])
            ->orderByDesc('ews_score')
            ->get();

        if ($kasusMerah->isEmpty()) {
            $this->info('Tidak ada kasus Merah aktif hari ini.');
            return self::SUCCESS;
        }

        $adminDinas = User::where('role', 'admin_dinas')->get();

        if ($adminDinas->isNotEmpty()) {
            Notification::send($adminDinas, new RingkasanEwsHarianNotification($kasusMerah));
        }

        $this->info("Ringkasan {$kasusMerah->count()} kasus Merah dikirim ke {$adminDinas->count()} Admin Dinas.");

        return self::SUCCESS;
    }
}