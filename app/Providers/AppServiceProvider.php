<?php

namespace App\Providers;

use App\Models\Report;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Aman untuk MySQL/MariaDB versi lama di XAMPP (hindari error "key was too long")
        Schema::defaultStringLength(191);

        // Layout petugas/admin: jumlah laporan menunggu untuk badge menu & lonceng
        View::composer('layouts.staff', function ($view) {
            $view->with('pendingCount', Report::where('status', Report::STATUS_PENDING)->count());
        });

        // Data yang selalu dibutuhkan layout warga: sapaan + notifikasi lonceng.
        View::composer('layouts.warga', function ($view) {
            $hour = now()->hour;
            $greeting = match (true) {
                $hour < 4 => 'Selamat malam',
                $hour < 11 => 'Selamat pagi',
                $hour < 15 => 'Selamat siang',
                $hour < 18 => 'Selamat sore',
                default => 'Selamat malam',
            };

            $user = Auth::user();
            $notifications = $user
                ? $user->reports()->with('type')
                    ->where('status', '!=', 'pending')
                    ->whereNotNull('reviewed_at')
                    ->latest('reviewed_at')->limit(5)->get()
                : collect();

            $view->with([
                'greeting' => $greeting,
                'notifications' => $notifications,
                'hasNewNotification' => $notifications->contains(fn ($r) => $r->reviewed_at->gte(now()->subDays(7))),
            ]);
        });
    }
}
