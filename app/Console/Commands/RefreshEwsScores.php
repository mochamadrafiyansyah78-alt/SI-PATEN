<?php

namespace App\Console\Commands;

use App\Services\EwsService;
use Illuminate\Console\Command;

class RefreshEwsScores extends Command
{
    /**
     * php artisan ews:refresh
     * Jadwalkan harian di routes/console.php:
     *   Schedule::command('ews:refresh')->dailyAt('01:00');
     */
    protected $signature = 'ews:refresh';

    protected $description = 'Hitung ulang skor & level Early Warning System untuk seluruh data_ats';

    public function handle(EwsService $ews): int
    {
        $total = $ews->refreshAll();
        $this->info("Skor EWS diperbarui untuk {$total} data.");

        return self::SUCCESS;
    }
}
