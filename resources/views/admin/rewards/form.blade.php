@extends('layouts.staff')

@php $isEdit = (bool) $reward; @endphp

@section('title', ($isEdit ? 'Ubah' : 'Tambah').' Reward — CivicWatch Traffic')

@section('content')
<div class="max-w-2xl">
    <a href="{{ route('admin.rewards.index') }}" class="inline-flex items-center gap-1 text-sm font-medium text-civic-teal hover:underline">
        <span class="material-symbols-outlined !text-[18px]">arrow_back</span> Kembali ke reward
    </a>
    <h1 class="mt-2 font-display text-3xl font-extrabold text-civic-ink">{{ $isEdit ? 'Ubah reward' : 'Tambah reward baru' }}</h1>

    <form method="POST" action="{{ $isEdit ? route('admin.rewards.update', $reward) : route('admin.rewards.store') }}" class="card mt-6 space-y-4 p-5 sm:p-6">
        @csrf
        @if ($isEdit) @method('PUT') @endif

        <div>
            <label for="name" class="label">Nama reward</label>
            <input id="name" name="name" value="{{ old('name', $reward?->name) }}" class="input" placeholder="mis. Voucher Pulsa Rp25.000" required>
            @error('name') <p class="field-error">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="description" class="label">Deskripsi</label>
            <textarea id="description" name="description" rows="2" maxlength="255" class="input resize-none">{{ old('description', $reward?->description) }}</textarea>
            @error('description') <p class="field-error">{{ $message }}</p> @enderror
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label for="category" class="label">Kategori</label>
                <input id="category" name="category" list="categories" value="{{ old('category', $reward?->category ?? 'Digital Saldo') }}" class="input" required>
                <datalist id="categories"><option value="Digital Saldo"><option value="Resmi Pemerintah"><option value="Voucher"></datalist>
                @error('category') <p class="field-error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="icon" class="label">Ikon</label>
                <select id="icon" name="icon" class="input">
                    @foreach ($icons as $key => $label)
                        <option value="{{ $key }}" @selected(old('icon', $reward?->icon ?? 'redeem') === $key)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('icon') <p class="field-error">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label for="points_cost" class="label">Harga (poin)</label>
                <input id="points_cost" type="number" min="1" name="points_cost" value="{{ old('points_cost', $reward?->points_cost) }}" class="input" required>
                @error('points_cost') <p class="field-error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="stock" class="label">Stok</label>
                <input id="stock" type="number" min="0" name="stock" value="{{ old('stock', $reward?->stock ?? 50) }}" class="input" required>
                @error('stock') <p class="field-error">{{ $message }}</p> @enderror
            </div>
        </div>

        <div>
            <label for="badge" class="label">Lencana sorotan <span class="font-normal text-slate-500">(opsional, mis. "Nilai Tertinggi")</span></label>
            <input id="badge" name="badge" maxlength="50" value="{{ old('badge', $reward?->badge) }}" class="input">
            @error('badge') <p class="field-error">{{ $message }}</p> @enderror
        </div>

        <label class="flex items-center gap-2 text-sm">
            <input type="hidden" name="is_active" value="0">
            <input type="checkbox" name="is_active" value="1" class="rounded border-civic-line" @checked(old('is_active', $reward?->is_active ?? true))>
            Aktif (tampil di katalog warga)
        </label>

        <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
            <a href="{{ route('admin.rewards.index') }}" class="btn-light">Batal</a>
            <button class="btn-primary"><span class="material-symbols-outlined">save</span> Simpan</button>
        </div>
    </form>
</div>
@endsection
