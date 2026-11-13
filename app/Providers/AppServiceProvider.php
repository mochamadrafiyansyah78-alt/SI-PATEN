<?php

namespace App\Providers;

use App\Models\DataAts;
use App\Observers\DataAtsObserver;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Step 4-6: auto-hitung skor EWS + kirim notifikasi kasus Merah
        // setiap kali data_ats dibuat/diupdate (lihat app/Observers/DataAtsObserver.php)
        DataAts::observe(DataAtsObserver::class);

        RateLimiter::for('login', fn (Request $r) => Limit::perMinute(5)->by(strtolower((string) $r->input('email')) . '|' . $r->ip()));
        RateLimiter::for('unggah', fn (Request $r) => Limit::perMinute(6)->by($r->user()?->id ?: $r->ip()));

        if ($this->app->isProduction()) {
            URL::forceScheme('https');
        }
    }
}