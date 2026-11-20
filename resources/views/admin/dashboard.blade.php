@extends('layouts.staff')

@section('title', 'Ringkasan Laporan — CivicWatch Traffic')

@section('content')
@php
    $change = fn ($v) => $v === null ? null : (($v >= 0 ? '+' : '').str_replace('.', ',', $v).'%');
    $fmt = fn ($n) => number_format($n, 0, ',', '.');
@endphp

<div class="flex flex-wrap items-end justify-between gap-3">
    <h1 class="font-display text-3xl font-extrabold text-civic-ink">Ringkasan laporan</h1>
    <div class="flex items-center gap-2">
        <div class="flex rounded-full bg-white p-1 shadow-card">
            @foreach ([7, 14, 30] as $d)
                <a href="{{ route('admin.dashboard', ['days' => $d]) }}" class="rounded-full px-3 py-1 text-xs font-semibold {{ $days === $d ? 'bg-civic-navy text-white' : 'text-slate-600 hover:bg-civic-chip' }}">{{ $d }} hari</a>
            @endforeach
        </div>
        <span class="inline-flex items-center gap-1.5 rounded-full bg-civic-chip px-3 py-1.5 text-xs font-medium text-civic-navy">
            <span class="material-symbols-outlined !text-[16px]">calendar_month</span>
            {{ $from->translatedFormat('d M') }} – {{ $to->translatedFormat('d M Y') }}
        </span>
    </div>
</div>

{{-- Profil admin --}}
<section class="mt-5 card flex flex-wrap items-center justify-between gap-4 p-4 sm:p-5">
    <div class="flex items-center gap-4">
        <span class="grid place-items-center size-14 rounded-2xl bg-civic-navy text-white"><span class="material-symbols-outlined icon-fill !text-[28px]">admin_panel_settings</span></span>
        <div>
            <p class="font-display text-xl font-bold">{{ auth()->user()->name }}</p>
            <p class="text-sm text-slate-600">Akses penuh, pengelola sistem</p>
        </div>
    </div>
    <a href="{{ route('admin.export.index') }}" class="btn-light"><span class="material-symbols-outlined">download</span> Ekspor data</a>
</section>

{{-- Kartu --}}
<section class="mt-5 grid grid-cols-2 gap-4 lg:grid-cols-4">
    <div class="card p-5">
        <div class="flex items-center justify-between"><p class="text-[11px] font-bold uppercase tracking-widest text-slate-500">Total laporan</p>
            <span class="grid place-items-center size-9 rounded-lg bg-civic-chip text-civic-navy"><span class="material-symbols-outlined !text-[18px]">pending_actions</span></span></div>
        <p class="mt-3 font-display text-4xl font-extrabold text-civic-navy">{{ $fmt($cards['total']) }}</p>
        <p class="mt-1 text-xs {{ ($cards['totalChange'] ?? 0) >= 0 ? 'text-civic-teal' : 'text-red-600' }}">
            {{ $change($cards['totalChange']) ? $change($cards['totalChange']).' dari periode lalu' : 'Belum ada pembanding' }}</p>
    </div>
    <div class="card p-5">
        <div class="flex items-center justify-between"><p class="text-[11px] font-bold uppercase tracking-widest text-slate-500">Pending</p>
            <span class="grid place-items-center size-9 rounded-lg bg-civic-amber text-civic-amberink"><span class="material-symbols-outlined !text-[18px]">schedule</span></span></div>
        <p class="mt-3 font-display text-4xl font-extrabold text-amber-500">{{ $fmt($cards['pending']) }}</p>
        <p class="mt-1 flex items-center gap-1 text-xs text-slate-600"><span class="size-2 rounded-full bg-amber-500"></span> Perlu verifikasi segera</p>
    </div>
    <div class="card p-5">
        <div class="flex items-center justify-between"><p class="text-[11px] font-bold uppercase tracking-widest text-slate-500">Valid</p>
            <span class="grid place-items-center size-9 rounded-lg bg-civic-chip text-civic-navy"><span class="material-symbols-outlined icon-fill !text-[18px]">check_circle</span></span></div>
        <p class="mt-3 font-display text-4xl font-extrabold text-civic-navy">{{ $fmt($cards['valid']) }}
            <span class="text-xs font-bold text-civic-teal">{{ str_replace('.', ',', $cards['validPercent']) }}%</span></p>
        <p class="mt-1 text-xs text-slate-600">Diteruskan ke penindakan</p>
    </div>
    <div class="card p-5">
        <div class="flex items-center justify-between"><p class="text-[11px] font-bold uppercase tracking-widest text-slate-500">Invalid</p>
            <span class="grid place-items-center size-9 rounded-lg bg-red-100 text-red-700"><span class="material-symbols-outlined !text-[18px]">cancel</span></span></div>
        <p class="mt-3 font-display text-4xl font-extrabold text-red-700">{{ $fmt($cards['invalid']) }}
            <span class="text-xs font-bold text-red-600">{{ str_replace('.', ',', $cards['invalidPercent']) }}%</span></p>
        <p class="mt-1 text-xs text-slate-600">Bukti buram / duplikasi</p>
    </div>
</section>

{{-- Tren harian --}}
<section class="mt-5 card p-5 sm:p-6">
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <h2 class="flex items-center gap-2 font-display text-lg font-bold">Tren Laporan Harian
                <span class="rounded bg-civic-chip px-2 py-0.5 text-[11px] font-medium text-civic-navy">{{ $days }} hari</span></h2>
            <p class="mt-1"><span class="font-display text-3xl font-extrabold text-civic-navy">{{ $todayCount }}</span> <span class="text-slate-600">laporan hari ini</span></p>
        </div>
        @if ($todayChange !== null)
            <div class="flex items-center gap-3 rounded-xl bg-civic-surface px-4 py-2">
                <span class="grid place-items-center size-9 rounded-full bg-sky-100 text-civic-teal"><span class="material-symbols-outlined">{{ $todayChange >= 0 ? 'arrow_upward' : 'arrow_downward' }}</span></span>
                <div><p class="font-display font-bold text-civic-teal">{{ $change($todayChange) }}</p><p class="text-[11px] text-slate-500">vs kemarin ({{ $yesterdayCount }})</p></div>
            </div>
        @endif
    </div>

    <div class="mt-6 flex items-end gap-1.5 sm:gap-3" role="img" aria-label="Grafik batang jumlah laporan per hari">
        @foreach ($series as $s)
            <div class="flex min-w-0 flex-1 flex-col items-center">
                <div class="flex h-48 w-full flex-col items-center justify-end">
                    <span class="mb-1 text-[10px] sm:text-[11px] {{ $s['today'] ? 'font-bold text-civic-navy' : 'text-slate-500' }}">{{ $s['count'] }}</span>
                    <div class="w-full rounded-t-lg {{ $s['today'] ? 'bg-civic-navy' : 'bg-civic-chip' }}"
                         style="height: {{ max(2, round($s['count'] / $maxCount * 82)) }}%"></div>
                </div>
                <p class="mt-2 text-xs font-semibold {{ $s['today'] ? 'text-civic-navy' : 'text-slate-700' }}">{{ $s['day'] }}</p>
                @if ($days <= 14) <p class="text-[10px] text-slate-500">{{ $s['today'] ? 'Hari ini' : $s['name'] }}</p> @endif
            </div>
        @endforeach
    </div>
    <p class="mt-5 rounded-lg bg-civic-surface px-3 py-2 text-[11px] text-slate-600">Rentang data: {{ $from->translatedFormat('d F') }} – {{ $to->translatedFormat('d F Y') }}</p>
</section>

{{-- Jenis terbanyak + respons --}}
<section class="mt-5 grid gap-5 lg:grid-cols-2">
    <div class="card p-5 sm:p-6">
        <h2 class="flex items-center justify-between font-display text-lg font-bold">Tipe Pelanggaran Terbanyak <span class="material-symbols-outlined text-civic-teal">traffic</span></h2>
        <div class="mt-4 space-y-4">
            @forelse ($topTypes as $t)
                @php $p = $total > 0 ? round($t->total / $total * 100) : 0; @endphp
                <div>
                    <div class="flex items-center justify-between text-sm"><span class="font-medium">{{ $t->type->name }}</span><span class="text-civic-teal">{{ $fmt($t->total) }} ({{ $p }}%)</span></div>
                    <div class="mt-1.5 h-2 w-full overflow-hidden rounded-full bg-civic-chip"><div class="h-full rounded-full bg-civic-navy" style="width: {{ $p }}%"></div></div>
                </div>
            @empty
                <p class="text-sm text-slate-500">Belum ada laporan pada periode ini.</p>
            @endforelse
        </div>
    </div>

    <div class="card p-5 sm:p-6">
        <h2 class="flex items-center justify-between font-display text-lg font-bold">Respons Verifikasi <span class="material-symbols-outlined text-amber-500">speed</span></h2>
        <div class="mt-5 flex items-center gap-6">
            <div class="grid size-28 shrink-0 place-items-center rounded-full"
                 style="background: conic-gradient(#1B3A8F {{ $reviewedPercent * 3.6 }}deg, #E4E8FF 0)">
                <div class="grid size-20 place-items-center rounded-full bg-white font-display text-xl font-extrabold text-civic-navy">{{ $reviewedPercent }}%</div>
            </div>
            <div>
                <p class="font-display text-3xl font-extrabold text-civic-navy">
                    @if ($avgMinutes === null) – @elseif ($avgMinutes >= 60) {{ floor($avgMinutes / 60) }} jam {{ $avgMinutes % 60 }} mnt @else {{ $avgMinutes }} Menit @endif
                </p>
                <p class="mt-1 text-sm text-slate-600">Rata-rata waktu dari laporan masuk hingga keputusan valid/invalid. Lingkaran: {{ $reviewedPercent }}% laporan sudah diputuskan.</p>
            </div>
        </div>
    </div>
</section>

<a href="{{ route('admin.export.index') }}" class="btn-primary mt-5 w-full !py-4"><span class="material-symbols-outlined">download</span> Ekspor Ringkasan Laporan</a>
@endsection
