<?php

use App\Http\Middleware\CheckRole;
use App\Http\Middleware\SecurityHeaders;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => CheckRole::class,
        ]);
        $middleware->append(SecurityHeaders::class);
        $middleware->trustProxies(at: '*');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Gangguan koneksi database -> halaman/JSON ramah, bukan stack trace
        $exceptions->render(function (\Illuminate\Database\QueryException|\PDOException $e, \Illuminate\Http\Request $request) {
            $msg = $e->getMessage();
            $koneksi = $e instanceof \PDOException
                || preg_match('/SQLSTATE\[(08|57|53)|could not connect|timeout|timed out|server closed|Connection refused|tenant|terminating connection/i', $msg);
            if (! $koneksi) {
                return null;
            }
            report($e);
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Server tidak dapat terhubung ke basis data. Coba lagi sebentar lagi.'], 503);
            }

            return response()->view('errors.koneksi', [], 503);
        });
    })->create();