@extends('layouts.app')

@section('title', 'Daftar — CivicWatch Traffic')

@section('body')
<div class="min-h-screen grid place-items-center bg-gradient-to-br from-orange-50 via-civic-bg to-sky-50 px-4 py-10">
    <div class="w-full max-w-md">
        <div class="text-center">
            <img src="{{ asset('images/logo.jpg') }}" alt="CivicWatch Traffic" class="mx-auto h-28 w-auto">
        </div>

        <div class="card mt-5 p-6 sm:p-8">
            <h1 class="font-display text-2xl font-extrabold">Daftar sebagai warga</h1>
            <p class="mt-1 text-sm text-slate-500">Mulai melapor dan kumpulkan poin kontribusi.</p>

            <form method="POST" action="{{ route('register.store') }}" class="mt-5 space-y-4">
                @csrf
                <div>
                    <label for="name" class="label">Nama lengkap</label>
                    <input id="name" name="name" value="{{ old('name') }}" class="input" required>
                    @error('name') <p class="field-error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="username" class="label">Username</label>
                    <input id="username" name="username" value="{{ old('username') }}" class="input" required autocomplete="username">
                    @error('username') <p class="field-error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="email" class="label">Email</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" class="input" required>
                    @error('email') <p class="field-error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="phone" class="label">Nomor HP / WhatsApp <span class="font-normal text-slate-500">(opsional)</span></label>
                    <input id="phone" name="phone" value="{{ old('phone') }}" class="input" placeholder="08xxxxxxxxxx">
                    @error('phone') <p class="field-error">{{ $message }}</p> @enderror
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="password" class="label">Password</label>
                        <input id="password" type="password" name="password" class="input" required autocomplete="new-password">
                        @error('password') <p class="field-error">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="password_confirmation" class="label">Ulangi password</label>
                        <input id="password_confirmation" type="password" name="password_confirmation" class="input" required autocomplete="new-password">
                    </div>
                </div>
                <button class="btn-primary w-full">Buat akun</button>
            </form>

            <p class="mt-5 text-center text-sm text-slate-600">
                Sudah punya akun? <a href="{{ route('login') }}" class="font-semibold text-civic-navy hover:underline">Masuk</a>
            </p>
        </div>
    </div>
</div>
@endsection
