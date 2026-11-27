<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    /**
     * Pemakaian: ->middleware('role:warga') atau ->middleware('role:petugas,admin')
     * User dengan role lain dikembalikan ke dashboard miliknya sendiri.
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        // Akun dinonaktifkan admin -> langsung dikeluarkan
        if (! $user->is_active) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->with('error', 'Akun Anda dinonaktifkan. Hubungi administrator.');
        }

        if (! in_array($user->role, $roles, true)) {
            return redirect()->route($user->homeRoute());
        }

        return $next($request);
    }
}
