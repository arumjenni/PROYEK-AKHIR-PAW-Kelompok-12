@extends('layouts.staff')

@section('title', 'Riwayat Verifikasi — CivicWatch Traffic')

@section('content')
@php
    $isAdmin = auth()->user()->isAdmin();
    $tabs = [
        ['key' => null,       'label' => 'Semua',   'count' => $counts['all']],
        ['key' => 'approved', 'label' => 'Valid',   'count' => $counts['approved']],
        ['key' => 'rejected', 'label' => 'Invalid', 'count' => $counts['rejected']],
    ];
@endphp

<div>
    <p class="eyebrow">Rekam jejak keputusan</p>
    <h1 class="mt-1 font-display text-3xl font-extrabold text-civic-ink">Riwayat verifikasi</h1>
    <p class="mt-1 text-sm text-slate-600">
        {{ $isAdmin ? 'Seluruh keputusan verifikasi dari semua petugas.' : 'Keputusan verifikasi tilang yang telah Anda proses.' }}
    </p>
</div>

<div class="mt-6 flex flex-wrap gap-2">
    @foreach ($tabs as $tab)
        @php $on = $status === $tab['key']; @endphp
        <a href="{{ route('verifikasi.riwayat', array_filter(['status' => $tab['key']])) }}"
           class="inline-flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-medium transition {{ $on ? 'bg-civic-navy text-white' : 'bg-white text-slate-600 ring-1 ring-civic-line hover:bg-civic-chip' }}">
            {{ $tab['label'] }}
            <span class="rounded-full px-1.5 text-[11px] {{ $on ? 'bg-white/20' : 'bg-civic-chip' }}">{{ $tab['count'] }}</span>
        </a>
    @endforeach
</div>

<section class="mt-4 card overflow-hidden">
    @if ($reports->isEmpty())
        <p class="px-5 py-14 text-center text-sm text-slate-500">Belum ada riwayat verifikasi.</p>
    @else
        <ul class="divide-y divide-civic-line">
            @foreach ($reports as $r)
                <li class="flex flex-wrap items-center gap-x-4 gap-y-2 px-5 py-4">
                    @include('staff.partials.thumb', ['r' => $r, 'class' => 'size-12'])
                    <div class="min-w-[8rem] flex-1">
                        <p class="font-display font-bold text-civic-navy">{{ $r->plate_number }}</p>
                        <p class="text-xs text-slate-500">{{ $r->code }} · {{ $r->location_name ?: '-' }}</p>
                    </div>
                    <div class="flex min-w-[10rem] flex-1 items-center gap-2 text-sm">
                        <span class="material-symbols-outlined !text-[18px] text-civic-teal">{{ $r->type->icon }}</span>{{ $r->type->name }}
                    </div>
                    <div class="min-w-[9rem] text-sm text-slate-600">
                        {{ $r->reviewed_at->translatedFormat('d M Y') }} · {{ $r->reviewed_at->format('H:i') }} WIB
                        @if ($isAdmin && $r->reviewer) <span class="block text-xs text-slate-500">oleh {{ $r->reviewer->name }}</span> @endif
                    </div>
                    <div class="text-right">
                        <x-status-badge :status="$r->status" :staff="true" />
                        @if ($r->status === 'rejected' && $r->reject_reason) <p class="mt-1 text-[11px] text-slate-500">{{ $r->reject_reason }}</p> @endif
                    </div>
                    <a href="{{ route('antrean.show', $r) }}" class="grid place-items-center size-9 rounded-full text-slate-500 hover:bg-civic-chip hover:text-civic-navy" title="Lihat rincian">
                        <span class="material-symbols-outlined">visibility</span>
                    </a>
                </li>
            @endforeach
        </ul>
    @endif
</section>

@if ($reports->hasPages())
    <div class="mt-4">{{ $reports->links() }}</div>
@endif
@endsection
