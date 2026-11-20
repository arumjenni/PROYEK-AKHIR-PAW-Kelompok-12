@extends('layouts.app')

@section('body')
@php
    $user = auth()->user();
    $isAdmin = $user->isAdmin();

    // Petugas: 4 menu (wireframe). Admin: akses penuh -> menu lengkap (hi-fi).
    $nav = $isAdmin ? [
        ['label' => 'Beranda',            'route' => 'admin.dashboard',     'icon' => 'dashboard',       'active' => ['admin.dashboard']],
        ['label' => 'Antrean Laporan',    'route' => 'antrean.index',       'icon' => 'inbox',           'active' => ['antrean.*'], 'badge' => $pendingCount],
        ['label' => 'Riwayat Verifikasi', 'route' => 'verifikasi.riwayat',  'icon' => 'fact_check',      'active' => ['verifikasi.*']],
        ['label' => 'Manajemen Pengguna', 'route' => 'admin.users.index',   'icon' => 'groups',          'active' => ['admin.users.*']],
        ['label' => 'Manajemen Reward',   'route' => 'admin.rewards.index', 'icon' => 'redeem',          'active' => ['admin.rewards.*']],
        ['label' => 'Ekspor Data',        'route' => 'admin.export.index',  'icon' => 'download',        'active' => ['admin.export.*']],
        ['label' => 'Akun',               'route' => 'akun.edit',           'icon' => 'manage_accounts', 'active' => ['akun.*']],
    ] : [
        ['label' => 'Beranda',            'route' => 'petugas.dashboard',   'icon' => 'dashboard',       'active' => ['petugas.dashboard']],
        ['label' => 'Antrean Laporan',    'route' => 'antrean.index',       'icon' => 'inbox',           'active' => ['antrean.*'], 'badge' => $pendingCount],
        ['label' => 'Riwayat Verifikasi', 'route' => 'verifikasi.riwayat',  'icon' => 'fact_check',      'active' => ['verifikasi.*']],
        ['label' => 'Akun',               'route' => 'akun.edit',           'icon' => 'manage_accounts', 'active' => ['akun.*']],
    ];
@endphp

<div class="min-h-screen lg:pl-64">

    {{-- ===================== SIDEBAR (desktop) ===================== --}}
    <aside class="hidden lg:flex fixed inset-y-0 left-0 w-64 flex-col bg-white border-r border-civic-line px-6 pt-6 pb-6">
        <a href="{{ route($user->homeRoute()) }}" class="block mb-8" aria-label="CivicWatch Traffic">
            <img src="{{ asset('images/logo.jpg') }}" alt="CivicWatch Traffic" class="h-24 w-auto mx-auto">
        </a>

        <nav class="space-y-1.5 overflow-y-auto">
            @foreach ($nav as $item)
                @php $active = request()->routeIs(...$item['active']); @endphp
                <a href="{{ route($item['route']) }}"
                   class="flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-medium transition
                          {{ $active ? 'bg-civic-navy text-white shadow-md' : 'text-slate-600 hover:bg-civic-chip' }}">
                    <span class="material-symbols-outlined {{ $active ? 'icon-fill' : '' }}">{{ $item['icon'] }}</span>
                    <span class="flex-1">{{ $item['label'] }}</span>
                    @if (! empty($item['badge']))
                        <span class="rounded-full bg-civic-amber px-2 py-0.5 text-[11px] font-bold text-civic-amberink">{{ $item['badge'] }}</span>
                    @endif
                </a>
            @endforeach
        </nav>

        <div class="mt-auto space-y-3 pt-4">
            <a href="{{ route('akun.edit') }}" class="flex items-center gap-3 rounded-xl bg-civic-surface p-3 hover:bg-civic-chip transition">
                <span class="grid place-items-center size-9 rounded-full bg-civic-navy text-white">
                    <span class="material-symbols-outlined">{{ $isAdmin ? 'admin_panel_settings' : 'shield_person' }}</span>
                </span>
                <span class="min-w-0">
                    <span class="block text-sm font-semibold truncate">{{ $user->name }}</span>
                    <span class="block text-[11px] text-slate-500">{{ $user->roleLabel() }}</span>
                </span>
            </a>
            <div class="flex gap-2 rounded-xl bg-civic-chip p-3 text-[11px] leading-snug text-civic-navy">
                <span class="material-symbols-outlined !text-[16px] shrink-0">verified_user</span>
                {{ $user->unit ?: ($isAdmin ? 'Pusat kendali CivicWatch Traffic' : 'Keputusan Anda tercatat atas nama Anda.') }}
            </div>
        </div>
    </aside>

    {{-- ===================== TOP BAR ===================== --}}
    <header class="sticky top-0 z-30 bg-white/90 backdrop-blur border-b border-civic-line">
        <div class="flex items-center gap-2 sm:gap-3 h-16 lg:h-[72px] px-4 lg:px-8">
            <a href="{{ route($user->homeRoute()) }}" class="lg:hidden shrink-0" aria-label="CivicWatch Traffic">
                <img src="{{ asset('images/logo.jpg') }}" alt="CivicWatch Traffic" class="h-11 w-auto">
            </a>

            <p class="flex-1 min-w-0 truncate font-display font-bold text-xs sm:text-sm lg:text-base uppercase tracking-wide text-civic-ink lg:text-center">
                CivicWatch Traffic — {{ $isAdmin ? 'Administrator' : 'Officer Verifier' }}
            </p>

            @if ($pendingCount > 0)
                <a href="{{ route('antrean.next') }}" class="hidden sm:inline-flex btn-primary !py-2 !px-4">
                    <span class="material-symbols-outlined !text-[18px]">task_alt</span> Tinjau Instan
                </a>
            @endif

            {{-- Lonceng: laporan menunggu --}}
            <a href="{{ route('antrean.index') }}" class="relative grid place-items-center size-10 rounded-full hover:bg-civic-chip transition" aria-label="Antrean laporan" title="{{ $pendingCount }} laporan menunggu verifikasi">
                <span class="material-symbols-outlined">notifications</span>
                @if ($pendingCount > 0)
                    <span class="absolute top-2 right-2 size-2 rounded-full bg-red-500 ring-2 ring-white"></span>
                @endif
            </a>

            {{-- Menu akun --}}
            <div class="relative">
                <button type="button" data-dropdown-toggle="menuUser" class="flex items-center gap-2 rounded-full bg-civic-surface py-1 pl-1 pr-3 hover:bg-civic-chip transition" aria-label="Menu akun">
                    <span class="grid place-items-center size-8 rounded-full bg-civic-navy text-white"><span class="material-symbols-outlined !text-[18px]">person</span></span>
                    <span class="hidden sm:block text-left leading-tight">
                        <span class="block text-sm font-semibold">{{ $user->name }}</span>
                        <span class="block text-[10px] text-slate-500">{{ $user->roleLabel() }}</span>
                    </span>
                </button>
                <div id="menuUser" data-dropdown class="hidden absolute right-0 mt-2 w-56 rounded-2xl bg-white shadow-xl ring-1 ring-civic-line overflow-hidden">
                    <a href="{{ route('akun.edit') }}" class="flex items-center gap-2 px-4 py-2.5 text-sm hover:bg-civic-surface">
                        <span class="material-symbols-outlined">manage_accounts</span> Akun & pengaturan
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

        {{-- Menu mobile: baris yang bisa digeser --}}
        <nav class="lg:hidden flex gap-2 overflow-x-auto border-t border-civic-line px-4 py-2">
            @foreach ($nav as $item)
                @php $active = request()->routeIs(...$item['active']); @endphp
                <a href="{{ route($item['route']) }}"
                   class="inline-flex shrink-0 items-center gap-1.5 rounded-full px-3 py-1.5 text-xs font-medium {{ $active ? 'bg-civic-navy text-white' : 'bg-civic-surface text-slate-600' }}">
                    <span class="material-symbols-outlined !text-[16px]">{{ $item['icon'] }}</span>{{ $item['label'] }}
                    @if (! empty($item['badge'])) <span class="rounded-full bg-civic-amber px-1.5 text-[10px] font-bold text-civic-amberink">{{ $item['badge'] }}</span> @endif
                </a>
            @endforeach
        </nav>
    </header>

    {{-- ===================== KONTEN ===================== --}}
    <main class="px-4 lg:px-8 py-6 lg:py-8 pb-12">
        <div class="mx-auto max-w-6xl">
            @include('partials.flash')
            @yield('content')
        </div>
    </main>
</div>
@endsection
