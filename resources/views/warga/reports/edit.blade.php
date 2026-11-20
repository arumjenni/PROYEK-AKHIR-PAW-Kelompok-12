@extends('layouts.warga')

@section('title', 'Ubah Laporan — CivicWatch Traffic')

@section('content')
<div class="max-w-2xl">
    <a href="{{ route('laporan.index') }}" class="inline-flex items-center gap-1 text-sm font-medium text-civic-teal hover:underline">
        <span class="material-symbols-outlined !text-[18px]">arrow_back</span> Kembali ke riwayat
    </a>
    <h1 class="mt-2 font-display text-3xl font-extrabold text-civic-ink">Ubah laporan</h1>
    <p class="mt-1 text-sm text-slate-600">Laporan <b>{{ $report->code }}</b> masih menunggu verifikasi, sehingga datanya masih bisa dikoreksi. Foto & lokasi dikunci sebagai bukti.</p>

    <form method="POST" action="{{ route('laporan.update', $report) }}" class="card mt-6 p-5 sm:p-6 space-y-4">
        @csrf
        @method('PUT')

        @if ($report->photo_url)
            <img src="{{ $report->photo_url }}" alt="Foto bukti" class="w-full max-h-64 rounded-xl object-cover">
        @endif
        <p class="flex items-center gap-1.5 text-xs text-slate-500">
            <span class="material-symbols-outlined !text-[16px]">location_on</span>
            {{ $report->location_name ?: 'Lokasi tidak tercatat' }}
            · {{ $report->captured_at->translatedFormat('d M Y') }} {{ $report->captured_at->format('H:i') }} WIB
        </p>

        <div>
            <label for="plate_number" class="label">Nomor Plat Kendaraan</label>
            <input id="plate_number" name="plate_number" value="{{ old('plate_number', $report->plate_number) }}" maxlength="15"
                   class="input font-mono uppercase" required>
            @error('plate_number') <p class="field-error">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="violation_type_id" class="label">Jenis Pelanggaran</label>
            <select id="violation_type_id" name="violation_type_id" class="input" required>
                @foreach ($types as $t)
                    <option value="{{ $t->id }}" @selected(old('violation_type_id', $report->violation_type_id) == $t->id)>{{ $t->name }}</option>
                @endforeach
            </select>
            @error('violation_type_id') <p class="field-error">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="notes" class="label">Catatan Tambahan <span class="font-normal text-slate-500">(Opsional, maks 150)</span></label>
            <textarea id="notes" name="notes" rows="3" maxlength="150" class="input resize-none">{{ old('notes', $report->notes) }}</textarea>
            @error('notes') <p class="field-error">{{ $message }}</p> @enderror
        </div>

        <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
            <a href="{{ route('laporan.index') }}" class="btn-light">Batal</a>
            <button class="btn-primary"><span class="material-symbols-outlined">save</span> Simpan perubahan</button>
        </div>
    </form>
</div>
@endsection
