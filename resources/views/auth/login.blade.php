@extends('layouts.app')

@section('title', 'Masuk — CivicWatch Traffic')

@section('body')
<div class="min-h-screen grid place-items-center bg-gradient-to-br from-orange-50 via-civic-bg to-sky-50 px-4 py-10">
    <div class="w-full max-w-md">
        <div class="text-center">
            <img src="{{ asset('images/logo.jpg') }}" alt="CivicWatch Traffic" class="mx-auto h-32 w-auto">
            <p class="mt-3 text-sm text-slate-600">Portal Pengawasan Ketertiban Lalu Lintas Warga</p>
        </div>

        <div class="card mt-6 p-6 sm:p-8">
            <h1 class="font-display text-2xl font-extrabold">Masuk</h1>
            <p class="mt-1 text-sm text-slate-500">Gunakan username atau email Anda.</p>

            @include('partials.flash')

            <form method="POST" action="{{ route('login.store') }}" class="mt-5 space-y-4">
                @csrf
                <div>
                    <label for="login" class="label">Username / Email</label>
                    <input id="login" name="login" value="{{ old('login') }}" class="input" autofocus required autocomplete="username">
                    @error('login') <p class="field-error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="password" class="label">Password</label>
                    <input id="password" type="password" name="password" class="input" required autocomplete="current-password">
                    @error('password') <p class="field-error">{{ $message }}</p> @enderror
                </div>
                <label class="flex items-center gap-2 text-sm text-slate-600">
                    <input type="checkbox" name="remember" class="rounded border-civic-line"> Ingat saya
                </label>
                <button class="btn-primary w-full">Masuk</button>
            </form>

            <p class="mt-5 text-center text-sm text-slate-600">
                Belum punya akun? <a href="{{ route('register') }}" class="font-semibold text-civic-navy hover:underline">Daftar sebagai warga</a>
            </p>
        </div>

        @if (config('app.debug'))
            <div class="mt-4 rounded-xl bg-white/70 p-4 text-xs text-slate-600 ring-1 ring-civic-line">
                <p class="mb-1 font-bold text-civic-ink">Akun demo (hanya tampil saat APP_DEBUG=true)</p>
                <p><b>warga</b> / warga123 &nbsp;·&nbsp; <b>petugas</b> / petugas123 &nbsp;·&nbsp; <b>admin</b> / admin123</p>
            </div>
        @endif
    </div>
</div>
@endsection
