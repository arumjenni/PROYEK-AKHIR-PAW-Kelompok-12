@extends('layouts.app')

@section('body')
@php
    $user = auth()->user();
    $nav = [
        ['label' => 'Beranda',  'route' => 'beranda',        'icon' => 'home',         'active' => ['beranda']],
        ['label' => 'Laporkan', 'route' => 'laporan.create', 'icon' => 'campaign',     'active' => ['laporan.create']],
        ['label' => 'Riwayat',  'route' => 'laporan.index',  'icon' => 'history',      'active' => ['laporan.index', 'laporan.edit']],
        ['label' => 'Hadiah',   'route' => 'hadiah.index',   'icon' => 'emoji_events', 'active' => ['hadiah.*']],
    ];
@endphp

<div class="min-h-screen lg:pl-64">

    {{-- ===================== SIDEBAR (desktop) ===================== --}}
    <aside class="hidden lg:flex fixed inset-y-0 left-0 w-64 flex-col bg-white border-r border-civic-line px-6 pt-6 pb-6">
        {{-- Logo (sudah memuat teks, jadi tidak perlu teks tambahan di sampingnya) --}}
        <a href="{{ route('beranda') }}" class="block mb-8" aria-label="CivicWatch Traffic">
            <img src="{{ asset('images/logo.jpg') }}" alt="CivicWatch Traffic" class="h-24 w-auto mx-auto">
        </a>

        <nav class="space-y-1.5">
            @foreach ($nav as $item)
                @php $active = request()->routeIs(...$item['active']); @endphp
                <a href="{{ route($item['route']) }}"
                   class="flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-medium transition
                          {{ $active ? 'bg-civic-navy text-white shadow-md' : 'text-slate-600 hover:bg-civic-chip' }}">
                    <span class="material-symbols-outlined {{ $active ? 'icon-fill' : '' }}">{{ $item['icon'] }}</span>
                    {{ $item['label'] }}
                </a>
            @endforeach
        </nav>

        <div class="mt-auto space-y-3">
            <a href="{{ route('profil.edit') }}" class="flex items-center gap-3 rounded-xl bg-civic-surface p-3 hover:bg-civic-chip transition">
                <span class="grid place-items-center size-9 rounded-full bg-civic-navy text-white">
                    <span class="material-symbols-outlined">person</span>
                </span>
                <span class="min-w-0">
                    <span class="block text-sm font-semibold truncate">{{ $user->name }}</span>
                    <span class="block text-[11px] text-slate-500">Citizen Reporter</span>
                </span>
            </a>
            <div class="flex gap-2 rounded-xl bg-civic-chip p-3 text-[11px] leading-snug text-civic-navy">
                <span class="material-symbols-outlined !text-[16px] shrink-0">shield</span>
                Utamakan keselamatan. Jangan melapor saat sedang mengemudi.
            </div>
        </div>
    </aside>

    {{-- ===================== TOP BAR ===================== --}}
    <header class="sticky top-0 z-30 bg-white/90 backdrop-blur border-b border-civic-line">
        <div class="flex items-center gap-2 sm:gap-3 h-16 lg:h-[72px] px-4 lg:px-8">
            <a href="{{ route('beranda') }}" class="lg:hidden shrink-0" aria-label="CivicWatch Traffic">
                <img src="{{ asset('images/logo.jpg') }}" alt="CivicWatch Traffic" class="h-11 w-auto">
            </a>

            <p class="flex-1 min-w-0 truncate text-center font-display font-bold text-sm sm:text-base lg:text-lg">
                {{ $greeting }}, {{ $user->firstName() }}
            </p>

            {{-- Poin --}}
            <a href="{{ route('hadiah.index') }}"
               class="flex items-center gap-1.5 rounded-full bg-civic-amber px-3 py-1.5 text-xs font-bold text-civic-amberink hover:brightness-95 transition">
                <span class="material-symbols-outlined !text-[16px]">star</span>
                {{ number_format($user->points) }} <span class="hidden sm:inline">Poin <span class="font-normal">(Poin tersedia)</span></span>
            </a>

            {{-- Lonceng notifikasi --}}
            <div class="relative">
                <button type="button" data-dropdown-toggle="menuNotif" class="relative grid place-items-center size-10 rounded-full hover:bg-civic-chip transition" aria-label="Notifikasi">
                    <span class="material-symbols-outlined">notifications</span>
                    @if ($hasNewNotification)
                        <span class="absolute top-2 right-2 size-2 rounded-full bg-red-500 ring-2 ring-white"></span>
                    @endif
                </button>
                <div id="menuNotif" data-dropdown class="hidden absolute right-0 mt-2 w-72 sm:w-80 rounded-2xl bg-white shadow-xl ring-1 ring-civic-line overflow-hidden">
                    <p class="px-4 py-3 text-sm font-bold border-b border-civic-line">Notifikasi</p>
                    @forelse ($notifications as $n)
                        <a href="{{ route('laporan.index') }}" class="flex gap-3 px-4 py-3 hover:bg-civic-surface">
                            <span class="material-symbols-outlined mt-0.5 {{ $n->status === 'approved' ? 'text-civic-teal' : 'text-red-600' }}">
                                {{ $n->status === 'approved' ? 'check_circle' : 'cancel' }}
                            </span>
                            <span class="text-sm">
                                Laporan <b>{{ $n->plate_number }}</b>
                                {{ $n->status === 'approved' ? 'disetujui (+'.$n->points_awarded.' poin)' : 'ditolak' }}
                                <span class="block text-[11px] text-slate-500">{{ $n->reviewed_at->diffForHumans() }}</span>
                            </span>
                        </a>
                    @empty
                        <p class="px-4 py-6 text-sm text-center text-slate-500">Belum ada notifikasi.</p>
                    @endforelse
                </div>
            </div>

            <a href="{{ route('profil.edit') }}" class="hidden sm:grid place-items-center size-10 rounded-full hover:bg-civic-chip transition" aria-label="Pengaturan profil">
                <span class="material-symbols-outlined">settings</span>
            </a>

            {{-- Avatar + menu --}}
            <div class="relative">
                <button type="button" data-dropdown-toggle="menuUser" class="grid place-items-center size-10 rounded-full bg-civic-navy text-white" aria-label="Menu akun">
                    <span class="material-symbols-outlined">person</span>
                </button>
                <div id="menuUser" data-dropdown class="hidden absolute right-0 mt-2 w-56 rounded-2xl bg-white shadow-xl ring-1 ring-civic-line overflow-hidden">
                    <div class="px-4 py-3 border-b border-civic-line">
                        <p class="text-sm font-bold truncate">{{ $user->name }}</p>
                        <p class="text-xs text-slate-500 truncate">{{ $user->email }}</p>
                    </div>
                    <a href="{{ route('profil.edit') }}" class="flex items-center gap-2 px-4 py-2.5 text-sm hover:bg-civic-surface">
                        <span class="material-symbols-outlined">manage_accounts</span> Pengaturan profil
                    </a>
                    <a href="{{ route('hadiah.history') }}" class="flex items-center gap-2 px-4 py-2.5 text-sm hover:bg-civic-surface">
                        <span class="material-symbols-outlined">receipt_long</span> Riwayat penukaran
                    </a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="flex w-full items-center gap-2 px-4 py-2.5 text-sm text-red-600 hover:bg-red-50">
                            <span class="material-symbols-outlined">logout</span> Keluar
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </header>

    {{-- ===================== KONTEN ===================== --}}
    <main class="px-4 lg:px-8 py-6 lg:py-8 pb-28 lg:pb-10">
        <div class="mx-auto max-w-6xl">
            @include('partials.flash')
            @yield('content')
        </div>
    </main>

    {{-- ===================== NAV BAWAH (mobile) ===================== --}}
    <nav class="lg:hidden fixed bottom-0 inset-x-0 z-30 grid grid-cols-4 bg-white border-t border-civic-line pb-[env(safe-area-inset-bottom)]">
        @foreach ($nav as $item)
            @php $active = request()->routeIs(...$item['active']); @endphp
            <a href="{{ route($item['route']) }}" class="flex flex-col items-center gap-0.5 py-2.5 text-[11px] font-medium {{ $active ? 'text-civic-navy' : 'text-slate-500' }}">
                <span class="material-symbols-outlined !text-[24px] {{ $active ? 'icon-fill' : '' }}">{{ $item['icon'] }}</span>
                {{ $item['label'] }}
            </a>
        @endforeach
    </nav>
</div>
@endsection
