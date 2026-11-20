@extends('layouts.warga')

@section('title', 'Riwayat Laporan — CivicWatch Traffic')

@section('content')
@php
    // Pertahankan filter lain saat pindah tab status
    $keep = array_filter(['month' => $month, 'sort' => $sort === 'oldest' ? 'oldest' : null]);
    $tabs = [
        ['key' => null,       'label' => 'Semua',    'count' => $summary['total'],    'dot' => null],
        ['key' => 'pending',  'label' => 'Menunggu', 'count' => $summary['pending'],  'dot' => 'bg-amber-500'],
        ['key' => 'approved', 'label' => 'Disetujui','count' => $summary['approved'], 'dot' => 'bg-civic-teal'],
        ['key' => 'rejected', 'label' => 'Ditolak',  'count' => $summary['rejected'], 'dot' => 'bg-red-600'],
    ];
@endphp

<div class="flex flex-wrap items-start justify-between gap-3">
    <div>
        <p class="eyebrow flex items-center gap-1"><span class="material-symbols-outlined !text-[16px]">verified_user</span> Sistem verifikasi sipil terpadu</p>
        <h1 class="mt-1 font-display text-3xl font-extrabold text-civic-ink">Riwayat laporan</h1>
        <p class="mt-1 text-sm text-slate-600">Pantau status validasi dan distribusi poin kontribusi warga Anda secara langsung.</p>
    </div>
    <a href="{{ route('laporan.create') }}" class="btn-primary"><span class="material-symbols-outlined">add_circle</span> Buat Laporan Baru</a>
</div>

{{-- Ringkasan --}}
<div class="mt-6 grid gap-4 sm:grid-cols-2">
    <div class="card flex items-center justify-between p-5">
        <div>
            <p class="text-[11px] font-bold tracking-widest uppercase text-slate-500">Laporan terkirim</p>
            <p class="mt-1 font-display text-4xl font-extrabold text-civic-navy">{{ $summary['total'] }}
                <span class="text-xs font-bold text-civic-teal">Tercatat di server</span></p>
            <p class="mt-1 text-sm text-slate-600">{{ $summary['pending'] }} laporan dalam peninjauan aktif</p>
        </div>
        <span class="grid place-items-center size-14 rounded-2xl bg-civic-chip text-civic-navy"><span class="material-symbols-outlined icon-fill !text-[28px]">description</span></span>
    </div>
    <div class="card flex items-center justify-between p-5 bg-gradient-to-br from-white to-orange-50">
        <div>
            <p class="text-[11px] font-bold tracking-widest uppercase text-slate-500">Total poin diperoleh
                <span class="ml-1 rounded-full bg-civic-amber px-2 py-0.5 text-[10px] text-civic-amberink normal-case tracking-normal">Terverifikasi</span></p>
            <p class="mt-1 font-display text-4xl font-extrabold text-civic-amberink">{{ number_format($summary['earned']) }}
                <span class="text-xs font-bold text-amber-600">Poin Sipil</span></p>
            <p class="mt-1 text-sm text-slate-600">Dapat ditukarkan dengan voucer & saldo e-wallet</p>
        </div>
        <span class="grid place-items-center size-14 rounded-2xl bg-civic-amber text-civic-amberink"><span class="material-symbols-outlined icon-fill !text-[28px]">military_tech</span></span>
    </div>
</div>

{{-- Filter --}}
<div class="mt-6 flex flex-wrap items-center justify-between gap-3">
    <div class="flex flex-wrap gap-2">
        @foreach ($tabs as $tab)
            @php $on = $status === $tab['key']; @endphp
            <a href="{{ route('laporan.index', array_filter(['status' => $tab['key']] + $keep)) }}"
               class="inline-flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-medium transition {{ $on ? 'bg-civic-navy text-white' : 'bg-white text-slate-600 ring-1 ring-civic-line hover:bg-civic-chip' }}">
                @if ($tab['dot']) <span class="size-2 rounded-full {{ $tab['dot'] }}"></span> @endif
                {{ $tab['label'] }}
                <span class="rounded-full px-1.5 text-[11px] {{ $on ? 'bg-white/20' : 'bg-civic-chip' }}">{{ $tab['count'] }}</span>
            </a>
        @endforeach
    </div>

    <form method="GET" action="{{ route('laporan.index') }}" class="flex items-center gap-2">
        @if ($status) <input type="hidden" name="status" value="{{ $status }}"> @endif
        <input type="month" name="month" value="{{ $month }}" onchange="this.form.submit()"
               class="rounded-lg border border-civic-line bg-white px-3 py-2 text-sm" aria-label="Filter bulan">
        <select name="sort" onchange="this.form.submit()" class="rounded-lg border border-civic-line bg-white px-3 py-2 text-sm" aria-label="Urutkan">
            <option value="latest" @selected($sort === 'latest')>Urutkan: Terbaru</option>
            <option value="oldest" @selected($sort === 'oldest')>Urutkan: Terlama</option>
        </select>
        @if ($month)
            <a href="{{ route('laporan.index', array_filter(['status' => $status, 'sort' => $keep['sort'] ?? null])) }}" class="text-xs text-civic-teal hover:underline">Reset bulan</a>
        @endif
    </form>
</div>

{{-- Daftar --}}
<section class="mt-4 card overflow-hidden">
    @if ($reports->isEmpty())
        <div class="px-5 py-14 text-center text-sm text-slate-500">
            <span class="material-symbols-outlined !text-[40px] mb-2 block">inbox</span>
            Tidak ada laporan untuk filter ini.
        </div>
    @else
        {{-- Desktop --}}
        <table class="hidden md:table w-full text-sm">
            <thead class="bg-civic-surface text-[11px] font-bold uppercase tracking-wider text-slate-500">
                <tr>
                    <th class="px-5 py-3 text-left">Pelat kendaraan</th>
                    <th class="px-3 py-3 text-left">Jenis pelanggaran</th>
                    <th class="px-3 py-3 text-left">Tanggal & waktu</th>
                    <th class="px-3 py-3 text-left">Status</th>
                    <th class="px-3 py-3 text-left">Detail / poin</th>
                    <th class="px-5 py-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-civic-line">
                @foreach ($reports as $r)
                    <tr class="hover:bg-civic-surface/60">
                        <td class="px-5 py-4"><span class="rounded-md bg-civic-chip px-2.5 py-1 font-mono text-xs font-bold">{{ $r->plate_number }}</span></td>
                        <td class="px-3 py-4">
                            <p class="font-medium">{{ $r->type->name }}</p>
                            <p class="flex items-center gap-1 text-xs text-slate-500"><span class="material-symbols-outlined !text-[14px]">location_on</span>{{ $r->location_name ?: '-' }}</p>
                        </td>
                        <td class="px-3 py-4 text-slate-600">{{ $r->captured_at->translatedFormat('d M Y') }} · {{ $r->captured_at->format('H:i') }} WIB</td>
                        <td class="px-3 py-4"><x-status-badge :status="$r->status" /></td>
                        <td class="px-3 py-4 font-semibold">
                            @if ($r->status === 'approved') <span class="text-civic-teal">+{{ $r->points_awarded }} poin</span>
                            @elseif ($r->status === 'pending') <span class="text-amber-600">Menunggu verifikasi</span>
                            @else <span class="text-red-600">0 poin</span> <span class="block text-xs font-normal text-slate-500">{{ $r->reject_reason }}</span>
                            @endif
                        </td>
                        <td class="px-5 py-4">
                            <div class="flex items-center justify-end gap-1.5">
                                @include('warga.partials.proof-button', ['r' => $r])
                                @if ($r->isPending())
                                    <a href="{{ route('laporan.edit', $r) }}" class="grid place-items-center size-9 rounded-lg text-slate-500 hover:bg-civic-chip hover:text-civic-navy" title="Ubah laporan">
                                        <span class="material-symbols-outlined !text-[19px]">edit</span>
                                    </a>
                                    <form method="POST" action="{{ route('laporan.destroy', $r) }}" onsubmit="return confirm('Batalkan dan hapus laporan {{ $r->plate_number }}?')">
                                        @csrf @method('DELETE')
                                        <button class="grid place-items-center size-9 rounded-lg text-slate-500 hover:bg-red-50 hover:text-red-600" title="Hapus laporan">
                                            <span class="material-symbols-outlined !text-[19px]">delete</span>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        {{-- Mobile --}}
        <ul class="md:hidden divide-y divide-civic-line">
            @foreach ($reports as $r)
                <li class="px-5 py-4 space-y-2.5">
                    <div class="flex items-center justify-between gap-2">
                        <span class="rounded-md bg-civic-chip px-2.5 py-1 font-mono text-xs font-bold">{{ $r->plate_number }}</span>
                        <x-status-badge :status="$r->status" />
                    </div>
                    <div>
                        <p class="text-sm font-medium">{{ $r->type->name }}</p>
                        <p class="flex items-center gap-1 text-xs text-slate-500"><span class="material-symbols-outlined !text-[14px]">location_on</span>{{ $r->location_name ?: '-' }}</p>
                        <p class="text-xs text-slate-500">{{ $r->captured_at->translatedFormat('d M Y') }} · {{ $r->captured_at->format('H:i') }} WIB</p>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-semibold {{ $r->status === 'approved' ? 'text-civic-teal' : ($r->status === 'pending' ? 'text-amber-600' : 'text-red-600') }}">
                            {{ $r->status === 'approved' ? '+'.$r->points_awarded.' poin' : ($r->status === 'pending' ? 'Menunggu verifikasi' : '0 poin · '.$r->reject_reason) }}
                        </span>
                        <div class="flex items-center gap-1.5">
                            @include('warga.partials.proof-button', ['r' => $r, 'icon' => true])
                            @if ($r->isPending())
                                <a href="{{ route('laporan.edit', $r) }}" class="grid place-items-center size-9 rounded-full text-slate-500 hover:bg-civic-chip"><span class="material-symbols-outlined !text-[19px]">edit</span></a>
                                <form method="POST" action="{{ route('laporan.destroy', $r) }}" onsubmit="return confirm('Batalkan dan hapus laporan ini?')">
                                    @csrf @method('DELETE')
                                    <button class="grid place-items-center size-9 rounded-full text-slate-500 hover:bg-red-50 hover:text-red-600"><span class="material-symbols-outlined !text-[19px]">delete</span></button>
                                </form>
                            @endif
                        </div>
                    </div>
                </li>
            @endforeach
        </ul>
    @endif

    <div class="flex items-start gap-2 bg-civic-surface px-5 py-4 text-sm text-slate-600">
        <span class="material-symbols-outlined !text-[18px] text-civic-teal">info</span>
        Poin masuk setelah laporan disetujui. Verifikasi biasanya memerlukan 1-3 hari kerja oleh dinas perhubungan terkait.
    </div>
</section>

@if ($reports->hasPages())
    <div class="mt-4">{{ $reports->links() }}</div>
@endif

<section class="mt-6 flex flex-wrap items-center justify-between gap-4 rounded-2xl bg-gradient-to-r from-civic-ink to-civic-navy p-6 text-white">
    <div class="flex items-center gap-4">
        <span class="grid place-items-center size-12 rounded-xl bg-white/10"><span class="material-symbols-outlined icon-fill">shield</span></span>
        <div>
            <h3 class="font-display text-lg font-bold">Integritas Pelaporan Komunitas</h3>
            <p class="max-w-xl text-sm text-sky-200">Setiap laporan diverifikasi petugas dengan metadata lokasi dan waktu demi keadilan dan kenyamanan bersama.</p>
        </div>
    </div>
    <a href="{{ route('laporan.create') }}" class="btn bg-white text-civic-ink hover:bg-civic-chip"><span class="material-symbols-outlined">add_circle</span> Buat Laporan Baru</a>
</section>

@include('partials.proof-modal')
@endsection
