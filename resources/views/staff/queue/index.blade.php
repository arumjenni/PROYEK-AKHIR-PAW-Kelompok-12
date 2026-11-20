@extends('layouts.staff')

@section('title', 'Antrean Laporan — CivicWatch Traffic')

@section('content')
<div class="flex flex-wrap items-end justify-between gap-4">
    <div>
        <h1 class="font-display text-3xl font-extrabold text-civic-ink">Total laporan menunggu:
            <span class="ml-1 inline-block rounded-full bg-civic-amber px-3 py-0.5 text-2xl text-civic-amberink">{{ $reports->total() }}</span></h1>
        <p class="mt-1 text-sm text-slate-600">Periksa bukti visual, koordinat, dan kecocokan pelat nomor sebelum memberikan keputusan verifikasi.</p>
    </div>
    <form method="GET" class="flex items-center gap-2 rounded-xl bg-white px-3 py-2 shadow-card">
        <span class="text-xs text-slate-600">Urutkan berdasarkan:</span>
        <select name="sort" onchange="this.form.submit()" class="rounded-lg border-0 bg-transparent text-sm font-semibold text-civic-navy focus:outline-none">
            <option value="oldest" @selected($sort === 'oldest')>Terlama dahulu</option>
            <option value="newest" @selected($sort === 'newest')>Terbaru dahulu</option>
        </select>
    </form>
</div>

<div class="mt-6 space-y-4">
    @forelse ($reports as $r)
        <article class="card flex flex-wrap items-center gap-x-5 gap-y-4 p-4 sm:p-5">
            @include('staff.partials.thumb', ['r' => $r, 'class' => 'h-24 w-full sm:w-40'])

            <div class="grid place-items-center gap-1 rounded-xl bg-civic-surface px-4 py-3 text-center">
                <span class="material-symbols-outlined text-civic-teal">directions_car</span>
                <span class="text-[10px] font-bold uppercase text-civic-teal">Kendaraan</span>
                <span class="text-[10px] text-slate-500">{{ $r->code }}</span>
            </div>

            <div class="min-w-[14rem] flex-1">
                <div class="flex flex-wrap items-center gap-2">
                    <p class="font-display text-2xl font-extrabold text-civic-ink">{{ $r->plate_number }}</p>
                    <x-status-badge status="pending" />
                </div>
                <p class="mt-1 flex flex-wrap items-center gap-x-4 gap-y-1 text-sm text-slate-600">
                    <span class="inline-flex items-center gap-1"><span class="material-symbols-outlined !text-[16px]">event</span>{{ $r->captured_at->translatedFormat('d M Y') }} · {{ $r->captured_at->format('H.i') }}</span>
                    <span class="inline-flex items-center gap-1"><span class="material-symbols-outlined !text-[16px]">location_on</span>{{ $r->location_name ?: 'Lokasi tidak tercatat' }}</span>
                </p>
                <p class="mt-1.5 inline-flex items-center gap-1.5 text-sm font-semibold text-red-700">
                    <span class="material-symbols-outlined !text-[18px]">{{ $r->type->icon }}</span>{{ $r->type->name }}
                </p>
            </div>

            <a href="{{ route('antrean.show', $r) }}" class="btn-primary w-full sm:w-auto">Tinjau <span class="material-symbols-outlined !text-[18px]">arrow_forward</span></a>
        </article>
    @empty
        <div class="card px-5 py-16 text-center text-sm text-slate-500">
            <span class="material-symbols-outlined !text-[44px] mb-2 block text-emerald-600">task_alt</span>
            Antrean kosong. Semua laporan sudah diverifikasi.
        </div>
    @endforelse
</div>

@if ($reports->total() > 0)
    <div class="mt-6 flex flex-wrap items-center justify-between gap-3 text-sm text-slate-600">
        <p>Menampilkan <b>{{ $reports->count() }}</b> dari <b>{{ $reports->total() }}</b> laporan total</p>
        {{ $reports->links() }}
    </div>
@endif

<section class="mt-8 flex items-start gap-4 card p-5">
    <span class="grid place-items-center size-11 shrink-0 rounded-full bg-civic-chip text-civic-navy"><span class="material-symbols-outlined">shield</span></span>
    <div>
        <h3 class="font-display font-bold">Protokol Penilaian Bukti</h3>
        <p class="mt-1 text-sm text-slate-600">Pastikan plat nomor terbaca jelas, pelanggaran terlihat pada foto, dan lokasi sesuai sebelum menyetujui. Alasan wajib diisi saat menolak.</p>
    </div>
</section>
@endsection
