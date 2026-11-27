<?php

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
        // Percayai header proxy (ngrok/Cloudflare Tunnel/hosting) agar link & aset tetap https
        $middleware->trustProxies(at: '*');

        // Alias middleware pengecekan role: ->middleware('role:warga')
        $middleware->alias([
            'role' => \App\Http\Middleware\EnsureRole::class,
        ]);

        // Tamu yang membuka halaman terproteksi -> halaman login
        $middleware->redirectGuestsTo(fn () => route('login'));
        // User yang sudah login membuka halaman login -> '/' (diarahkan sesuai role)
        $middleware->redirectUsersTo('/');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Foto base64 tidak perlu di-flash kembali ke session saat validasi gagal
        $exceptions->dontFlash(['photo_data']);
    })
    ->create();
