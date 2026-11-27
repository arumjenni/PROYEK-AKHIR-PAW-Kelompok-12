<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;

class HomeController extends Controller
{
    /** "/" -> arahkan ke dashboard sesuai role, atau ke halaman login. */
    public function __invoke(Request $request): RedirectResponse
    {
        $user = $request->user();

        return $user
            ? redirect()->route($user->homeRoute())
            : redirect()->route('login');
    }
}
