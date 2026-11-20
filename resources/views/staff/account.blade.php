@extends('layouts.staff')

@section('title', 'Akun & Pengaturan — CivicWatch Traffic')

@section('content')
<div class="max-w-2xl">
    <h1 class="font-display text-3xl font-extrabold text-civic-ink">Akun & pengaturan</h1>
    <p class="mt-1 text-sm text-slate-600">Perbarui data akun Anda. Username, NRP, dan satuan hanya dapat diubah oleh administrator.</p>

    <form method="POST" action="{{ route('akun.update') }}" class="card mt-6 space-y-4 p-5 sm:p-6">
        @csrf
        @method('PUT')

        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label class="label">Username</label>
                <input value="{{ $user->username }}" class="input bg-civic-surface" disabled>
            </div>
            <div>
                <label class="label">Peran</label>
                <input value="{{ $user->roleLabel() }}" class="input bg-civic-surface" disabled>
            </div>
            @if ($user->badge_number)
                <div>
                    <label class="label">Badge / NRP</label>
                    <input value="{{ $user->badge_number }}" class="input bg-civic-surface" disabled>
                </div>
                <div>
                    <label class="label">Satuan & shift</label>
                    <input value="{{ $user->unit }}{{ $user->shiftLabel() ? ' · '.$user->shiftLabel() : '' }}" class="input bg-civic-surface" disabled>
                </div>
            @endif
        </div>

        <div>
            <label for="name" class="label">Nama lengkap</label>
            <input id="name" name="name" value="{{ old('name', $user->name) }}" class="input" required>
            @error('name') <p class="field-error">{{ $message }}</p> @enderror
        </div>
        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label for="email" class="label">Email</label>
                <input id="email" type="email" name="email" value="{{ old('email', $user->email) }}" class="input" required>
                @error('email') <p class="field-error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="phone" class="label">Nomor HP</label>
                <input id="phone" name="phone" value="{{ old('phone', $user->phone) }}" class="input">
                @error('phone') <p class="field-error">{{ $message }}</p> @enderror
            </div>
        </div>

        <hr class="border-civic-line">
        <p class="text-sm font-semibold">Ganti password <span class="font-normal text-slate-500">(kosongkan jika tidak ingin mengubah)</span></p>
        <div>
            <label for="current_password" class="label">Password saat ini</label>
            <input id="current_password" type="password" name="current_password" class="input" autocomplete="current-password">
            @error('current_password') <p class="field-error">{{ $message }}</p> @enderror
        </div>
        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label for="password" class="label">Password baru</label>
                <input id="password" type="password" name="password" class="input" autocomplete="new-password">
                @error('password') <p class="field-error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="password_confirmation" class="label">Ulangi password baru</label>
                <input id="password_confirmation" type="password" name="password_confirmation" class="input" autocomplete="new-password">
            </div>
        </div>

        <div class="flex justify-end">
            <button class="btn-primary"><span class="material-symbols-outlined">save</span> Simpan perubahan</button>
        </div>
    </form>
</div>
@endsection
