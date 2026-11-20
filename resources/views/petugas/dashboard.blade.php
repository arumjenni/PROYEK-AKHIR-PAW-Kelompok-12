@extends('layouts.staff')

@section('title', 'Beranda Petugas — CivicWatch Traffic')

@section('content')
@php $me = auth()->user(); @endphp

<div>
    <p class="eyebrow">CivicWatch Traffic — Officer Verifier</p>
    <h1 class="mt-1 font-display text-3xl font-extrabold text-civic-ink">Beranda petugas</h1>
</div>

{{-- Profil petugas --}}
<section class="mt-6 card flex flex-wrap items-center justify-between gap-5 p-5 sm:p-6">
    <div class="flex items-center gap-4">
        <span class="grid place-items-center size-16 rounded-full bg-civic-chip text-civic-navy"><span class="material-symbols-outlined icon-fill !text-[32px]">shield_person</span></span>
        <div>
            <div class="flex flex-wrap items-center gap-2">
                <h2 class="font-display text-2xl font-bold">{{ $me->name }}</h2>
                <span class="rounded-full bg-civic-chip px-3 py-0.5 text-xs font-semibold text-civic-navy">Petugas Verifikator</span>
            </div>
            <p class="mt-1 text-sm text-slate-600">
                @if ($me->badge_number) NRP {{ $me->badge_number }} @endif
                @if ($me->unit) · {{ $me->unit }} @endif
            </p>
        </div>
    </div>
    @if ($me->shiftLabel())
        <div class="flex items-center gap-3 rounded-2xl bg-civic-surface px-5 py-3">
            <span class="material-symbols-outlined text-civic-navy">schedule</span>
            <div>
                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Jadwal tugas</p>
                <p class="font-display font-bold text-civic-navy">Shift {{ $me->shiftLabel() }}</p>
            </div>
        </div>
    @endif
</section>

<div class="mt-6 grid gap-6 lg:grid-cols-[minmax(0,5fr)_minmax(0,7fr)]">
    {{-- Antrean --}}
    <section class="card border-t-4 border-civic-teal p-6">
        <div class="flex items-start justify-between">
            <div>
                <p class="eyebrow">Inspeksi masuk</p>
                <h2 class="font-display text-xl font-bold">Antrean Laporan</h2>
            </div>
            <span class="grid place-items-center size-11 rounded-xl bg-civic-amber text-civic-amberink"><span class="material-symbols-outlined">assignment_late</span></span>
        </div>
        <p class="mt-4 font-display text-6xl font-extrabold text-civic-navy">{{ $pending }}</p>
        <p class="mt-3 text-sm text-slate-600">
            Laporan warga menunggu verifikasi Anda. Tinjau laporan terlama terlebih dahulu.
        </p>
        <a href="{{ route('antrean.index') }}" class="btn-primary mt-5 w-full">Buka Antrean Laporan <span class="material-symbols-outlined !text-[18px]">arrow_forward</span></a>
    </section>

    {{-- Ringkasan --}}
    <section class="card p-6">
        <p class="eyebrow">Efektivitas petugas</p>
        <h2 class="font-display text-xl font-bold">Ringkasan Verifikasi</h2>

        <div class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-4">
            <div class="rounded-2xl bg-civic-surface p-4">
                <p class="text-xs text-slate-600">Total Laporan</p>
                <p class="mt-1 font-display text-3xl font-extrabold text-civic-ink">{{ $stats['total'] }}</p>
                <p class="mt-1 text-[11px] text-slate-500">Seluruh sistem</p>
            </div>
            <div class="rounded-2xl bg-civic-surface p-4">
                <p class="text-xs text-slate-600">Terverifikasi</p>
                <p class="mt-1 font-display text-3xl font-extrabold text-civic-ink">{{ $stats['verified'] }}</p>
                <p class="mt-1 text-[11px] text-civic-teal">{{ $stats['verifiedPercent'] }}% dari total</p>
            </div>
            <div class="rounded-2xl bg-civic-surface p-4">
                <p class="flex items-center justify-between text-xs text-slate-600">Valid <span class="size-2 rounded-full bg-sky-500"></span></p>
                <p class="mt-1 font-display text-3xl font-extrabold text-civic-ink">{{ $stats['valid'] }}</p>
                <p class="mt-1 text-[11px] text-slate-500">Diteruskan ke tilang</p>
            </div>
            <div class="rounded-2xl bg-civic-surface p-4">
                <p class="flex items-center justify-between text-xs text-slate-600">Invalid <span class="size-2 rounded-full bg-red-600"></span></p>
                <p class="mt-1 font-display text-3xl font-extrabold text-red-700">{{ $stats['invalid'] }}</p>
                <p class="mt-1 text-[11px] text-slate-500">Buram / tak cukup bukti</p>
            </div>
        </div>

        <div class="mt-6">
            <div class="flex items-center justify-between text-xs">
                <span>Rasio valid dari keputusan Anda</span>
                <span class="font-bold text-civic-navy">{{ $stats['accuracy'] }}% valid · {{ $stats['today'] }} diputuskan hari ini</span>
            </div>
            <div class="mt-2 h-2.5 w-full overflow-hidden rounded-full bg-red-200">
                <div class="h-full rounded-full bg-civic-navy" style="width: {{ $stats['verified'] > 0 ? $stats['accuracy'] : 0 }}%"></div>
            </div>
        </div>
    </section>
</div>

{{-- Aktivitas terbaru --}}
<section class="mt-6 card overflow-hidden">
    <div class="flex flex-wrap items-center justify-between gap-2 px-5 py-4">
        <div>
            <h2 class="font-display text-lg font-bold">Aktivitas Verifikasi Terbaru</h2>
            <p class="text-xs text-slate-500">Rekam jejak keputusan verifikasi yang telah Anda proses</p>
        </div>
        <a href="{{ route('verifikasi.riwayat') }}" class="flex items-center gap-1 text-sm font-medium text-civic-teal hover:underline">
            Lihat semua riwayat <span class="material-symbols-outlined !text-[18px]">arrow_forward</span>
        </a>
    </div>

    @if ($recent->isEmpty())
        <p class="border-t border-civic-line px-5 py-10 text-center text-sm text-slate-500">Belum ada keputusan. Mulai dari antrean laporan.</p>
    @else
        <ul class="divide-y divide-civic-line border-t border-civic-line">
            @foreach ($recent as $r)
                <li class="flex flex-wrap items-center gap-x-4 gap-y-2 px-5 py-4">
                    @include('staff.partials.thumb', ['r' => $r, 'class' => 'size-12'])
                    <div class="min-w-[8rem] flex-1">
                        <p class="font-display font-bold text-civic-navy">{{ $r->plate_number }}</p>
                        <p class="text-xs text-slate-500">{{ $r->location_name ?: '-' }}</p>
                    </div>
                    <div class="flex min-w-[10rem] flex-1 items-center gap-2 text-sm">
                        <span class="material-symbols-outlined !text-[18px] text-civic-teal">{{ $r->type->icon }}</span>{{ $r->type->name }}
                    </div>
                    <div class="min-w-[8rem] text-sm text-slate-600">
                        {{ $r->reviewed_at->isToday() ? 'Hari ini' : $r->reviewed_at->translatedFormat('d M Y') }} {{ $r->reviewed_at->format('H:i') }} WIB
                    </div>
                    <x-status-badge :status="$r->status" :staff="true" />
                    <a href="{{ route('antrean.show', $r) }}" class="grid place-items-center size-9 rounded-full text-slate-500 hover:bg-civic-chip hover:text-civic-navy" title="Lihat rincian">
                        <span class="material-symbols-outlined">visibility</span>
                    </a>
                </li>
            @endforeach
        </ul>
    @endif
    <p class="flex items-center gap-2 bg-civic-surface px-5 py-3 text-xs text-slate-600">
        <span class="material-symbols-outlined !text-[16px] text-civic-teal">info</span>
        Keputusan valid otomatis memberikan poin kepada pelapor sesuai pengaturan jenis pelanggaran.
    </p>
</section>
@endsection
