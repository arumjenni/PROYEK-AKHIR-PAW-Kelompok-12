@extends('layouts.warga')

@section('title', 'Hadiah — CivicWatch Traffic')

@section('content')
@php $points = auth()->user()->points; @endphp

<div class="flex flex-wrap items-start justify-between gap-3">
    <div>
        <p class="eyebrow">Civic Loyalty & Rewards <span class="font-normal normal-case tracking-normal text-slate-500">• SAMSAT & Dishub Terintegrasi</span></p>
        <h1 class="mt-1 font-display text-3xl font-extrabold text-civic-ink">Tukar poin</h1>
        <p class="mt-1 text-sm text-slate-600">Kontribusi baik, manfaat nyata untuk ketertiban bersama</p>
    </div>
    <a href="{{ route('hadiah.history') }}" class="inline-flex items-center gap-2 rounded-lg bg-civic-chip px-4 py-2 text-sm font-medium text-civic-navy hover:bg-civic-navy hover:text-white transition">
        <span class="material-symbols-outlined !text-[18px]">receipt_long</span> Riwayat Penukaran
    </a>
</div>

{{-- Saldo & level --}}
<section class="mt-6 card p-5 sm:p-6">
    <div class="flex flex-wrap items-center justify-between gap-5">
        <div>
            <p class="flex items-center gap-1 text-[11px] font-bold tracking-widest uppercase text-slate-600">
                Saldo poin Anda <span class="material-symbols-outlined !text-[16px] text-civic-teal">verified</span></p>
            <div class="mt-1 flex flex-wrap items-baseline gap-3">
                <span class="font-display text-6xl font-extrabold text-civic-navy">{{ number_format($points) }}</span>
                <span class="font-display text-xl font-bold text-civic-teal">Pts</span>
                <span class="rounded-full bg-sky-50 px-3 py-1 text-xs font-medium text-civic-teal">● Aktif & Terverifikasi</span>
            </div>
        </div>
        <div class="flex items-center gap-3 rounded-2xl bg-civic-surface px-5 py-4">
            <span class="grid place-items-center size-12 rounded-full bg-civic-amber text-civic-amberink"><span class="material-symbols-outlined icon-fill">military_tech</span></span>
            <div>
                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Tingkat pelapor</p>
                <p class="font-display text-lg font-bold">{{ $level['current']['name'] }}</p>
                <p class="text-xs text-civic-teal">{{ number_format($level['earned']) }} poin terkumpul seumur hidup</p>
            </div>
        </div>
    </div>

    @if ($level['next'])
        <div class="mt-6">
            <div class="flex items-center justify-between text-xs">
                <span>Progres menuju <b>{{ $level['next']['name'] }}</b> ({{ $level['next']['min'] }} Pts)</span>
                <span class="font-bold text-civic-teal">{{ $level['earned'] }} / {{ $level['next']['min'] }} Pts ({{ $level['percent'] }}%)</span>
            </div>
            <div class="mt-2 h-2.5 w-full overflow-hidden rounded-full bg-civic-chip">
                <div class="h-full rounded-full bg-gradient-to-r from-civic-teal to-civic-navy" style="width: {{ $level['percent'] }}%"></div>
            </div>
            <p class="mt-2 text-xs text-slate-600">Kumpulkan {{ $level['next']['min'] - $level['earned'] }} poin lagi melalui validasi laporan pelanggaran untuk naik ke tingkat berikutnya.</p>
        </div>
    @else
        <p class="mt-5 text-sm text-civic-teal font-medium">Anda sudah mencapai tingkat tertinggi. Terima kasih atas kontribusinya!</p>
    @endif
</section>

{{-- Pilihan hadiah --}}
<div class="mt-8 flex items-center justify-between">
    <h2 class="flex items-center gap-3 font-display text-xl font-bold">Pilihan hadiah
        <span class="rounded-full bg-civic-chip px-3 py-1 text-xs font-medium text-civic-navy">{{ $rewards->count() }} tersedia</span></h2>
    <span class="hidden sm:flex items-center gap-1 text-xs text-slate-500"><span class="material-symbols-outlined !text-[16px]">tune</span> Diurutkan: poin terendah</span>
</div>

<div class="mt-4 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
    @forelse ($rewards as $reward)
        @php
            $inStock = $reward->stock > 0;
            $can = $points >= $reward->points_cost && $inStock;
            $missing = $reward->points_cost - $points;
            $pct = min(100, (int) floor($points / $reward->points_cost * 100));
        @endphp
        <article class="relative flex flex-col card p-6 {{ $reward->badge ? 'ring-2 ring-civic-amber' : '' }}">
            @if ($reward->badge)
                <span class="absolute -top-3 left-1/2 -translate-x-1/2 inline-flex items-center gap-1 whitespace-nowrap rounded-full bg-civic-amber px-3 py-1 text-[11px] font-bold text-civic-amberink">
                    <span class="material-symbols-outlined icon-fill !text-[14px]">star</span>{{ $reward->badge }}
                </span>
            @endif
            <div class="flex items-start justify-between">
                <span class="grid place-items-center size-12 rounded-xl {{ $reward->badge ? 'bg-sky-100 text-civic-teal' : 'bg-civic-chip text-civic-navy' }} {{ $can ? '' : 'opacity-60' }}">
                    <span class="material-symbols-outlined">{{ $reward->icon }}</span>
                </span>
                <span class="rounded-md px-2 py-1 text-[11px] font-medium {{ $reward->category === 'Resmi Pemerintah' ? 'bg-civic-amber text-civic-amberink' : 'bg-civic-chip text-slate-600' }}">{{ $reward->category }}</span>
            </div>

            <h3 class="mt-5 font-display text-xl font-bold {{ $can ? '' : 'text-slate-500' }}">{{ $reward->name }}</h3>
            <p class="mt-1 text-sm leading-relaxed text-slate-600">{{ $reward->description }}</p>

            <div class="mt-auto pt-6">
                <p class="font-display text-2xl font-extrabold {{ $can ? 'text-civic-navy' : 'text-slate-600' }}">{{ number_format($reward->points_cost) }} <span class="text-base font-bold">poin</span></p>

                @if ($can)
                    <p class="mt-1 flex items-center gap-1.5 text-xs font-semibold text-emerald-700"><span class="size-2 rounded-full bg-emerald-600"></span> Dapat ditukar sekarang</p>
                    <form method="POST" action="{{ route('hadiah.redeem', $reward) }}" class="mt-3"
                          onsubmit="return confirm('Tukar {{ $reward->points_cost }} poin dengan {{ $reward->name }}?')">
                        @csrf
                        <button class="btn-primary w-full"><span class="material-symbols-outlined">redeem</span> Tukar Sekarang</button>
                    </form>
                @elseif (! $inStock)
                    <p class="mt-1 flex items-center gap-1 text-xs text-slate-500"><span class="material-symbols-outlined !text-[14px]">inventory_2</span> Stok sedang habis</p>
                    <button class="btn-primary mt-3 w-full" disabled><span class="material-symbols-outlined">block</span> Stok Habis</button>
                @else
                    <div class="mt-2 h-1.5 w-full overflow-hidden rounded-full bg-civic-chip"><div class="h-full rounded-full bg-slate-400" style="width: {{ $pct }}%"></div></div>
                    <p class="mt-1.5 flex items-center gap-1 text-xs text-slate-500"><span class="material-symbols-outlined !text-[14px]">lock</span> Butuh {{ $missing }} poin lagi</p>
                    <button class="btn-primary mt-3 w-full" disabled><span class="material-symbols-outlined">lock</span> Poin Belum Cukup</button>
                @endif
            </div>
        </article>
    @empty
        <p class="text-sm text-slate-500">Belum ada hadiah tersedia.</p>
    @endforelse
</div>

{{-- CTA --}}
<section class="mt-8 flex flex-wrap items-center justify-between gap-4 rounded-2xl bg-civic-surface p-5">
    <div class="flex items-center gap-4">
        <span class="grid place-items-center size-11 rounded-xl bg-civic-chip text-civic-navy"><span class="material-symbols-outlined">traffic</span></span>
        <div>
            <h3 class="font-display font-bold">Ingin saldo reward lebih banyak?</h3>
            <p class="text-sm text-slate-600">Laporkan pelanggaran lalu lintas dengan bukti jelas. Dapatkan poin untuk setiap validasi petugas.</p>
        </div>
    </div>
    <a href="{{ route('laporan.create') }}" class="btn-light"><span class="material-symbols-outlined">add_circle</span> Buat Laporan Baru</a>
</section>

<p class="mt-5 flex items-start gap-2 rounded-2xl bg-white p-4 text-xs leading-relaxed text-slate-600 shadow-card">
    <span class="material-symbols-outlined !text-[18px] text-civic-teal shrink-0">info</span>
    Poin dipotong otomatis setelah penukaran dikonfirmasi. Voucher digital dan instruksi aktivasi akan dikirimkan ke kontak terdaftar dalam 1×24 jam kerja. Syarat dan ketentuan berlaku di bawah pengawasan Dishub & Tim CivicWatch.
</p>
@endsection
