@extends('layouts.staff')

@section('title', 'Manajemen Reward — CivicWatch Traffic')

@section('content')
@php $fmt = fn ($n) => number_format($n, 0, ',', '.'); @endphp

<div class="flex flex-wrap items-end justify-between gap-3">
    <div class="flex flex-wrap items-baseline gap-x-4 gap-y-1">
        <h1 class="font-display text-3xl font-extrabold text-civic-ink">Manajemen Reward</h1>
        <span class="text-slate-500">Katalog & Pengaturan Poin</span>
    </div>
    <div class="flex flex-wrap items-center gap-2 text-xs font-semibold">
        <span class="inline-flex items-center gap-1.5 rounded-full bg-civic-chip px-3 py-1.5 text-civic-navy"><span class="size-2 rounded-full bg-civic-navy"></span>{{ $rewards->where('is_active', true)->count() }} REWARD AKTIF</span>
        <span class="inline-flex items-center gap-1.5 rounded-full bg-civic-amber px-3 py-1.5 text-civic-amberink"><span class="material-symbols-outlined icon-fill !text-[14px]">star</span>{{ $fmt($circulating) }} Poin Beredar</span>
    </div>
</div>

<div class="mt-6 grid gap-6 lg:grid-cols-[minmax(0,1.1fr)_minmax(0,1fr)] lg:items-start">

    {{-- ============ KATALOG ============ --}}
    <section>
        <div class="mb-3 flex items-center justify-between">
            <h2 class="flex items-center gap-2 font-display text-lg font-bold"><span class="material-symbols-outlined text-civic-navy">inventory_2</span> Katalog Reward</h2>
            <span class="text-[11px] font-bold uppercase tracking-widest text-slate-500">Pemberian insentif warga</span>
        </div>

        <a href="{{ route('admin.rewards.create') }}" class="btn-primary w-full !py-4"><span class="material-symbols-outlined">add_circle</span> Tambah Reward Baru</a>

        <div class="mt-4 space-y-3">
            @forelse ($rewards as $r)
                <article class="card flex items-center gap-4 p-4 {{ $r->is_active ? '' : 'opacity-70' }}">
                    <span class="grid size-12 shrink-0 place-items-center rounded-xl bg-civic-chip text-civic-navy"><span class="material-symbols-outlined">{{ $r->icon }}</span></span>
                    <div class="min-w-0 flex-1">
                        <p class="truncate font-display text-lg font-bold">{{ $r->name }}</p>
                        <p class="mt-0.5 flex flex-wrap items-center gap-x-2 gap-y-1 text-xs text-slate-600">
                            <span class="inline-flex items-center gap-1 rounded bg-civic-amber px-2 py-0.5 font-semibold text-civic-amberink"><span class="material-symbols-outlined icon-fill !text-[13px]">monetization_on</span>{{ $fmt($r->points_cost) }} Poin</span>
                            <span>Stok {{ $r->stock }}</span>
                            <span class="font-semibold {{ $r->is_active ? 'text-civic-teal' : 'text-slate-500' }}">• {{ $r->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                        </p>
                    </div>
                    <div class="flex items-center gap-1">
                        <a href="{{ route('admin.rewards.edit', $r) }}" class="grid size-10 place-items-center rounded-lg bg-civic-surface text-slate-600 hover:bg-civic-chip hover:text-civic-navy" title="Ubah reward"><span class="material-symbols-outlined">edit_square</span></a>
                        <form method="POST" action="{{ route('admin.rewards.destroy', $r) }}" onsubmit="return confirm('Hapus reward {{ addslashes($r->name) }}? Riwayat penukaran tetap tersimpan.')">
                            @csrf @method('DELETE')
                            <button class="grid size-10 place-items-center rounded-lg text-slate-500 hover:bg-red-50 hover:text-red-600" title="Hapus reward"><span class="material-symbols-outlined">delete</span></button>
                        </form>
                    </div>
                </article>
            @empty
                <p class="card px-5 py-10 text-center text-sm text-slate-500">Belum ada reward.</p>
            @endforelse
        </div>

        <div class="mt-4 flex flex-wrap items-center justify-between gap-3 rounded-2xl bg-civic-surface p-4">
            <div class="flex items-center gap-3">
                <span class="grid size-11 place-items-center rounded-xl bg-civic-chip text-civic-navy"><span class="material-symbols-outlined">featured_seasonal_and_gifts</span></span>
                <div><p class="font-display font-bold">Total Klaim Bulan Ini</p><p class="text-xs text-slate-600">{{ $fmt($claims) }} voucher berhasil ditukarkan</p></div>
            </div>
            <span class="rounded-lg bg-white px-3 py-2 text-sm"><span class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Poin keluar:</span> <b class="font-display text-civic-navy">{{ $fmt($spent) }}</b></span>
        </div>
    </section>

    {{-- ============ PENGATURAN POIN ============ --}}
    <section>
        <div class="mb-3 flex items-center justify-between">
            <h2 class="flex items-center gap-2 font-display text-lg font-bold"><span class="material-symbols-outlined text-civic-navy">tune</span> Pengaturan Poin</h2>
            <span class="inline-flex items-center gap-1 rounded bg-civic-chip px-2 py-0.5 text-[11px] font-semibold text-civic-navy"><span class="material-symbols-outlined !text-[13px]">lock_open</span>Dapat diedit</span>
        </div>

        <form method="POST" action="{{ route('admin.rewards.points') }}" class="card p-5">
            @csrf @method('PUT')
            <div class="rounded-xl bg-civic-surface p-4">
                <p class="font-display font-bold">Poin per pelanggaran</p>
                <p class="mt-1 text-sm text-slate-600">Poin diberikan ke warga hanya setelah laporan dinyatakan <b class="underline text-civic-navy">Valid</b> oleh petugas.</p>
            </div>

            <div class="mt-3 divide-y divide-civic-line">
                @foreach ($types as $t)
                    <div class="flex items-center gap-3 py-3">
                        <span class="grid size-10 shrink-0 place-items-center rounded-lg bg-civic-chip text-civic-navy"><span class="material-symbols-outlined">{{ $t->icon }}</span></span>
                        <label for="points_{{ $t->id }}" class="min-w-0 flex-1 text-sm font-medium">{{ $t->name }}</label>
                        <input id="points_{{ $t->id }}" type="number" min="0" max="10000" name="points[{{ $t->id }}]" value="{{ old('points.'.$t->id, $t->points) }}"
                               class="w-24 rounded-lg border border-civic-line bg-civic-surface px-3 py-2 text-right font-display font-bold text-civic-navy outline-none focus:border-civic-navy focus:ring-2 focus:ring-civic-navy/20">
                        <span class="text-xs text-slate-500">poin</span>
                    </div>
                @endforeach
            </div>
            @error('points.*') <p class="field-error">{{ $message }}</p> @enderror

            <button class="btn-primary mt-3 w-full !py-3.5"><span class="material-symbols-outlined">save</span> Simpan pengaturan poin</button>
        </form>

        <p class="mt-4 flex gap-2 rounded-2xl bg-civic-surface p-4 text-xs leading-relaxed text-slate-600">
            <span class="material-symbols-outlined !text-[18px] shrink-0 text-civic-teal">info</span>
            <span><b class="text-civic-ink">Catatan kebijakan:</b> Perubahan poin berlaku untuk laporan baru. Poin yang sudah diterima warga tidak berubah.</span>
        </p>
    </section>
</div>
@endsection
