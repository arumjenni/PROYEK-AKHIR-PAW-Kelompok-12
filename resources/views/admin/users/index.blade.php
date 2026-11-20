@extends('layouts.staff')

@section('title', 'Manajemen Pengguna — CivicWatch Traffic')

@section('content')
<div class="flex flex-wrap items-end justify-between gap-3">
    <div>
        <p class="eyebrow">Otoritas pengawasan sipil & aparatur</p>
        <h1 class="mt-1 font-display text-3xl font-extrabold text-civic-ink">Manajemen pengguna</h1>
    </div>
    <a href="{{ route('admin.users.create', ['role' => $tab]) }}" class="btn-primary">
        <span class="material-symbols-outlined">person_add</span> Tambah {{ $tab === 'petugas' ? 'Petugas' : 'Warga' }}
    </a>
</div>

{{-- Pencarian --}}
<form method="GET" action="{{ route('admin.users.index') }}" class="mt-5 flex items-center gap-2 rounded-2xl bg-white p-2 shadow-card">
    <input type="hidden" name="tab" value="{{ $tab }}">
    <span class="material-symbols-outlined ml-2 text-slate-400">search</span>
    <input type="search" name="q" value="{{ $q }}" placeholder="Cari nama, NIK, atau nomor badge…" class="min-w-0 flex-1 bg-transparent px-2 py-2 text-sm outline-none">
    @if ($q !== '') <a href="{{ route('admin.users.index', ['tab' => $tab]) }}" class="text-xs text-civic-teal hover:underline">Reset</a> @endif
    <button class="btn-primary !py-2">Cari</button>
</form>

{{-- Tab --}}
<div class="mt-4 grid grid-cols-2 gap-1 rounded-2xl bg-civic-chip p-1.5">
    @foreach (['warga' => ['Warga', 'groups'], 'petugas' => ['Petugas', 'shield_person']] as $key => [$label, $icon])
        <a href="{{ route('admin.users.index', array_filter(['tab' => $key, 'q' => $q])) }}"
           class="flex items-center justify-center gap-2 rounded-xl py-2.5 text-sm font-semibold transition {{ $tab === $key ? 'bg-civic-navy text-white shadow' : 'text-slate-600 hover:bg-white/60' }}">
            <span class="material-symbols-outlined !text-[18px]">{{ $icon }}</span> {{ $label }} - {{ $counts[$key] }}
        </a>
    @endforeach
</div>

<div class="mt-5 grid gap-4 md:grid-cols-2">
    @forelse ($users as $u)
        <article class="card p-5 {{ $u->is_active ? '' : 'opacity-75' }}">
            <div class="flex items-start justify-between gap-3">
                <div class="flex min-w-0 items-center gap-3">
                    <span class="relative grid size-12 shrink-0 place-items-center rounded-full bg-civic-chip text-civic-navy">
                        <span class="material-symbols-outlined">{{ $tab === 'petugas' ? 'shield_person' : 'person' }}</span>
                        <span class="absolute -bottom-0.5 -right-0.5 size-3.5 rounded-full ring-2 ring-white {{ $u->is_active ? 'bg-emerald-500' : 'bg-slate-400' }}"></span>
                    </span>
                    <div class="min-w-0">
                        <p class="truncate font-display text-lg font-bold">{{ $u->name }}</p>
                        <p class="truncate text-xs text-slate-500">
                            @if ($tab === 'warga') NIK {{ $u->nik ?: '-' }}
                            @else Badge / NRP {{ $u->badge_number ?: '-' }}@endif
                            · {{ '@'.$u->username }}
                        </p>
                    </div>
                </div>
                <span class="shrink-0 rounded-full px-3 py-0.5 text-xs font-semibold {{ $u->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-200 text-slate-600' }}">{{ $u->is_active ? 'Aktif' : 'Nonaktif' }}</span>
            </div>

            <div class="mt-4 flex flex-wrap items-center justify-between gap-2 rounded-xl bg-civic-surface px-3 py-2 text-xs text-slate-600">
                @if ($tab === 'warga')
                    <span class="inline-flex items-center gap-1.5"><span class="material-symbols-outlined !text-[16px] text-civic-teal">campaign</span><b>{{ $u->valid_count }}</b> laporan valid dari {{ $u->total_count }}</span>
                    <span class="inline-flex items-center gap-1.5"><span class="material-symbols-outlined !text-[16px]">event</span>Bergabung {{ $u->created_at->translatedFormat('d M Y') }}</span>
                @else
                    <span><b>{{ $u->month_count }}</b> diverifikasi bulan ini</span>
                    <span>{{ $u->total_count }} total · {{ $u->unit ?: 'Satuan -' }}{{ $u->shiftLabel() ? ' · '.$u->shiftLabel() : '' }}</span>
                @endif
            </div>

            <div class="mt-4 grid grid-cols-2 gap-3">
                <a href="{{ route('admin.users.edit', $u) }}" class="btn-light !py-2.5"><span class="material-symbols-outlined !text-[18px]">edit</span> Edit</a>
                <form method="POST" action="{{ route('admin.users.toggle', $u) }}"
                      onsubmit="return confirm('{{ $u->is_active ? 'Nonaktifkan' : 'Aktifkan kembali' }} akun {{ addslashes($u->name) }}?')">
                    @csrf @method('PATCH')
                    <button class="btn-light w-full !py-2.5 {{ $u->is_active ? '' : 'text-emerald-700' }}">
                        <span class="material-symbols-outlined !text-[18px]">{{ $u->is_active ? 'person_off' : 'person_check' }}</span> {{ $u->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                    </button>
                </form>
            </div>
        </article>
    @empty
        <p class="card col-span-full px-5 py-14 text-center text-sm text-slate-500">Tidak ada pengguna yang cocok.</p>
    @endforelse
</div>

@if ($users->hasPages())
    <div class="mt-5">{{ $users->links() }}</div>
@endif

<aside class="mt-6 flex gap-3 rounded-2xl border-l-4 border-civic-navy bg-white p-5 shadow-card">
    <span class="material-symbols-outlined text-civic-navy">policy</span>
    <div>
        <h3 class="font-display font-bold">Protokol Keamanan Data & Arsip</h3>
        <p class="mt-1 text-sm text-slate-600">Akun nonaktif tidak dapat masuk. Riwayat laporan tetap tersimpan untuk keperluan audit dan pembuktian.</p>
    </div>
</aside>
@endsection
