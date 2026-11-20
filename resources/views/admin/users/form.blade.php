@extends('layouts.staff')

@php $isEdit = (bool) $user; $isPetugas = $role === 'petugas'; @endphp

@section('title', ($isEdit ? 'Ubah ' : 'Tambah ').($isPetugas ? 'Petugas' : 'Warga').' — CivicWatch Traffic')

@section('content')
<div class="max-w-2xl">
    <a href="{{ route('admin.users.index', ['tab' => $role]) }}" class="inline-flex items-center gap-1 text-sm font-medium text-civic-teal hover:underline">
        <span class="material-symbols-outlined !text-[18px]">arrow_back</span> Kembali ke pengguna
    </a>
    <h1 class="mt-2 font-display text-3xl font-extrabold text-civic-ink">{{ $isEdit ? 'Ubah' : 'Tambah' }} {{ $isPetugas ? 'petugas' : 'warga' }}</h1>

    <form method="POST" action="{{ $isEdit ? route('admin.users.update', $user) : route('admin.users.store') }}" class="card mt-6 space-y-4 p-5 sm:p-6">
        @csrf
        @if ($isEdit) @method('PUT') @else <input type="hidden" name="role" value="{{ $role }}"> @endif

        <div>
            <label for="name" class="label">Nama lengkap</label>
            <input id="name" name="name" value="{{ old('name', $user?->name) }}" class="input" required>
            @error('name') <p class="field-error">{{ $message }}</p> @enderror
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label for="username" class="label">Username</label>
                @if ($isEdit)
                    <input id="username" value="{{ $user->username }}" class="input bg-civic-surface" disabled>
                @else
                    <input id="username" name="username" value="{{ old('username') }}" class="input" required autocomplete="off">
                    @error('username') <p class="field-error">{{ $message }}</p> @enderror
                @endif
            </div>
            <div>
                <label for="email" class="label">Email</label>
                <input id="email" type="email" name="email" value="{{ old('email', $user?->email) }}" class="input" required>
                @error('email') <p class="field-error">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label for="phone" class="label">Nomor HP</label>
                <input id="phone" name="phone" value="{{ old('phone', $user?->phone) }}" class="input">
                @error('phone') <p class="field-error">{{ $message }}</p> @enderror
            </div>
            @if ($isPetugas)
                <div>
                    <label for="badge_number" class="label">Badge / NRP <span class="text-red-600">*</span></label>
                    <input id="badge_number" name="badge_number" value="{{ old('badge_number', $user?->badge_number) }}" class="input" required>
                    @error('badge_number') <p class="field-error">{{ $message }}</p> @enderror
                </div>
            @else
                <div>
                    <label for="nik" class="label">NIK <span class="font-normal text-slate-500">(16 digit)</span></label>
                    <input id="nik" name="nik" value="{{ old('nik', $user?->nik) }}" inputmode="numeric" maxlength="16" class="input font-mono">
                    @error('nik') <p class="field-error">{{ $message }}</p> @enderror
                </div>
            @endif
        </div>

        @if ($isPetugas)
            <div>
                <label for="unit" class="label">Satuan</label>
                <input id="unit" name="unit" value="{{ old('unit', $user?->unit) }}" class="input" placeholder="mis. Satlantas Metro Jaya">
                @error('unit') <p class="field-error">{{ $message }}</p> @enderror
            </div>
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="shift_start" class="label">Shift mulai</label>
                    <input id="shift_start" type="time" name="shift_start" value="{{ old('shift_start', $user?->shift_start) }}" class="input">
                    @error('shift_start') <p class="field-error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="shift_end" class="label">Shift selesai</label>
                    <input id="shift_end" type="time" name="shift_end" value="{{ old('shift_end', $user?->shift_end) }}" class="input">
                    @error('shift_end') <p class="field-error">{{ $message }}</p> @enderror
                </div>
            </div>
        @endif

        <div>
            <label for="password" class="label">Password {{ $isEdit ? '(kosongkan jika tidak diubah)' : '' }}</label>
            <input id="password" type="password" name="password" class="input" autocomplete="new-password" {{ $isEdit ? '' : 'required' }}>
            @error('password') <p class="field-error">{{ $message }}</p> @enderror
        </div>

        <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
            <a href="{{ route('admin.users.index', ['tab' => $role]) }}" class="btn-light">Batal</a>
            <button class="btn-primary"><span class="material-symbols-outlined">save</span> Simpan</button>
        </div>
    </form>
</div>
@endsection
