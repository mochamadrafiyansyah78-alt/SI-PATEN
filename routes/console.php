<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// ================= STEP 4: Early Warning System =================
// Hitung ulang skor & level EWS setiap hari (faktor "lama belum ditangani"
// berubah tiap hari walau tidak ada input/update data baru).
Schedule::command('ews:refresh')->dailyAt('01:00');

// ================= STEP 6: Notifikasi =================
// Ringkasan harian kasus level Merah yang belum selesai, dikirim ke Admin Dinas.
// Dijalankan setelah ews:refresh supaya data levelnya sudah paling update.
Schedule::command('ews:ringkasan-harian')->dailyAt('07:00');