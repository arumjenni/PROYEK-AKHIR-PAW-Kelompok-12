@extends('layouts.warga')

@section('title', 'Beranda — CivicWatch Traffic')

@push('head')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
@endpush

@section('content')
{{-- ============ HERO ============ --}}
<section class="relative overflow-hidden card px-5 py-10 sm:py-14 text-center bg-gradient-to-br from-orange-50 via-white to-sky-50">
    <span class="inline-flex items-center gap-1.5 rounded-full bg-civic-chip px-4 py-1.5 text-xs sm:text-sm font-medium text-civic-teal">
        <span class="material-symbols-outlined !text-[16px]">verified_user</span>
        Portal Pengawasan Ketertiban Lalu Lintas Warga
    </span>
    <h1 class="mt-5 font-display text-3xl sm:text-4xl font-extrabold text-civic-ink">Jalan tertib, mulai dari kita.</h1>
    <p class="mx-auto mt-3 max-w-xl text-sm sm:text-base text-slate-600">
        Temukan pelanggaran? Laporkan langsung secara aman, terverifikasi, dan bantu jaga keselamatan bersama di jalan raya.
    </p>
    <div class="mt-7 flex flex-col sm:flex-row items-center justify-center gap-3">
        <a href="{{ route('laporan.create') }}" class="btn-primary w-full sm:w-auto">
            <span class="material-symbols-outlined">photo_camera</span> Laporkan Pelanggaran
        </a>
        <a href="{{ route('hadiah.index') }}" class="btn-light w-full sm:w-auto">
            <span class="material-symbols-outlined text-amber-500">redeem</span> Tukar Poin
        </a>
    </div>
</section>

{{-- ============ STATISTIK ============ --}}
<section class="mt-6 grid gap-4 sm:grid-cols-3">
    <div class="card p-5 flex items-start justify-between gap-3">
        <div>
            <p class="text-[11px] font-bold tracking-widest uppercase text-slate-500">Total laporan Anda</p>
            <p class="mt-2 font-display text-4xl font-extrabold text-civic-ink">{{ $stats['total'] }}
                <span class="text-base font-bold text-civic-teal">Terkirim</span></p>
            <p class="mt-1 text-xs text-slate-500">{{ $stats['pending'] }} laporan dalam investigasi petugas</p>
        </div>
        <span class="grid place-items-center size-12 shrink-0 rounded-xl bg-civic-chip text-civic-navy">
            <span class="material-symbols-outlined">assignment_turned_in</span>
        </span>
    </div>

    <div class="card p-5 flex items-start justify-between gap-3">
        <div>
            <p class="text-[11px] font-bold tracking-widest uppercase text-slate-500">Poin terkumpul</p>
            <p class="mt-2 font-display text-4xl font-extrabold text-civic-amberink">{{ number_format(auth()->user()->points) }}
                <span class="text-base font-bold text-amber-600">Poin</span></p>
            <p class="mt-1 text-xs text-slate-500">Siap ditukar e-wallet & voucher pajak</p>
        </div>
        <span class="grid place-items-center size-12 shrink-0 rounded-xl bg-civic-amber text-civic-amberink">
            <span class="material-symbols-outlined icon-fill">star</span>
        </span>
    </div>

    <div class="card p-5 flex items-start justify-between gap-3">
        <div>
            <p class="text-[11px] font-bold tracking-widest uppercase text-slate-500">Laporan terverifikasi</p>
            <p class="mt-2 font-display text-4xl font-extrabold text-civic-ink">{{ $stats['approved'] }}
                <span class="text-base font-bold text-civic-teal">Disetujui</span></p>
            <p class="mt-1 text-xs text-slate-500">Akurasi validasi sistem {{ $stats['accuracy'] }}%</p>
        </div>
        <span class="grid place-items-center size-12 shrink-0 rounded-xl bg-civic-chip text-civic-navy">
            <span class="material-symbols-outlined">task_alt</span>
        </span>
    </div>
</section>

{{-- ============ LAPORAN TERBARU ============ --}}
<section class="mt-6 card overflow-hidden">
    <div class="flex items-center justify-between gap-3 px-5 py-4">
        <h2 class="flex items-center gap-2 font-display text-lg font-bold">
            <span class="size-2.5 rounded-full bg-civic-teal"></span> Laporan terbaru
        </h2>
        <a href="{{ route('laporan.index') }}" class="flex items-center gap-1 text-sm font-medium text-civic-teal hover:underline">
            Lihat semua <span class="material-symbols-outlined !text-[18px]">arrow_forward</span>
        </a>
    </div>

    @if ($recentReports->isEmpty())
        <div class="border-t border-civic-line px-5 py-10 text-center text-sm text-slate-500">
            Belum ada laporan. <a href="{{ route('laporan.create') }}" class="font-semibold text-civic-navy underline">Buat laporan pertama Anda</a>.
        </div>
    @else
        {{-- Desktop: tabel --}}
        <table class="hidden md:table w-full text-sm">
            <thead class="bg-civic-surface text-[11px] font-bold uppercase tracking-wider text-slate-500">
                <tr>
                    <th class="px-5 py-3 text-left">Pelat kendaraan</th>
                    <th class="px-3 py-3 text-left">Jenis pelanggaran</th>
                    <th class="px-3 py-3 text-left">Tanggal & waktu</th>
                    <th class="px-3 py-3 text-left">Status validasi</th>
                    <th class="px-3 py-3 text-right">Poin diperoleh</th>
                    <th class="px-5 py-3 text-center">Tindakan</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-civic-line">
                @foreach ($recentReports as $r)
                    <tr class="hover:bg-civic-surface/60">
                        <td class="px-5 py-4"><span class="rounded-md bg-civic-chip px-2.5 py-1 font-mono text-xs font-bold">{{ $r->plate_number }}</span></td>
                        <td class="px-3 py-4">
                            <span class="flex items-center gap-2">
                                <span class="material-symbols-outlined !text-[18px] text-slate-500">{{ $r->type->icon }}</span>{{ $r->type->name }}
                            </span>
                        </td>
                        <td class="px-3 py-4 text-slate-600">{{ $r->captured_at->translatedFormat('d M Y') }} · {{ $r->captured_at->format('H:i') }}</td>
                        <td class="px-3 py-4"><x-status-badge :status="$r->status" /></td>
                        <td class="px-3 py-4 text-right font-semibold">
                            @if ($r->status === 'approved')
                                <span class="text-civic-teal">+{{ $r->points_awarded }} Poin</span>
                            @elseif ($r->status === 'pending')
                                <span class="text-amber-600" title="Estimasi bila disetujui">~+{{ $r->type->points }} Poin</span>
                            @else
                                <span class="text-slate-400">0 Poin</span>
                            @endif
                        </td>
                        <td class="px-5 py-4 text-center">
                            @include('warga.partials.proof-button', ['r' => $r, 'icon' => true])
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        {{-- Mobile: kartu --}}
        <ul class="md:hidden divide-y divide-civic-line border-t border-civic-line">
            @foreach ($recentReports as $r)
                <li class="px-5 py-4 space-y-2">
                    <div class="flex items-center justify-between gap-2">
                        <span class="rounded-md bg-civic-chip px-2.5 py-1 font-mono text-xs font-bold">{{ $r->plate_number }}</span>
                        <x-status-badge :status="$r->status" />
                    </div>
                    <p class="flex items-center gap-2 text-sm font-medium">
                        <span class="material-symbols-outlined !text-[18px] text-slate-500">{{ $r->type->icon }}</span>{{ $r->type->name }}
                    </p>
                    <div class="flex items-center justify-between text-xs text-slate-500">
                        <span>{{ $r->captured_at->translatedFormat('d M Y') }} · {{ $r->captured_at->format('H:i') }}</span>
                        <span class="flex items-center gap-2">
                            <b class="{{ $r->status === 'approved' ? 'text-civic-teal' : 'text-slate-400' }}">
                                {{ $r->status === 'approved' ? '+'.$r->points_awarded : '0' }} Poin
                            </b>
                            @include('warga.partials.proof-button', ['r' => $r, 'icon' => true])
                        </span>
                    </div>
                </li>
            @endforeach
        </ul>
    @endif

    <div class="flex flex-wrap items-center gap-x-3 gap-y-1 bg-civic-surface px-5 py-4 text-sm text-slate-600">
        <span class="material-symbols-outlined !text-[18px] text-civic-teal">verified</span>
        Foto bukti harus memuat pelat kendaraan dan rambu sekitar secara jelas tanpa manipulasi digital.
    </div>
</section>

{{-- ============ PETA + HADIAH ============ --}}
<section class="mt-6 grid gap-6 lg:grid-cols-3">
    <div class="card p-5 lg:col-span-2">
        <div class="flex flex-wrap items-start justify-between gap-2">
            <div>
                <h2 class="font-display text-lg font-bold">Peta Pantauan Wilayah Anda</h2>
                <p class="text-xs text-slate-500">Titik rawan pelanggaran terverifikasi di sekitar lokasi Anda</p>
            </div>
            <span id="gpsBadge" class="rounded-lg bg-civic-chip px-3 py-1.5 text-xs font-medium text-civic-teal">Mencari GPS…</span>
        </div>
        <div class="relative mt-4">
            <div id="monitorMap" class="h-72 sm:h-80 rounded-2xl overflow-hidden z-0"
                 data-points="{{ json_encode($mapPoints) }}"></div>
            <span class="absolute bottom-3 left-3 z-[400] flex items-center gap-1.5 rounded-full bg-white/95 px-3 py-1 text-[11px] font-medium shadow">
                <span class="size-2 rounded-full bg-civic-teal"></span> Radius lapor: 5.0 KM dari posisi aktif
            </span>
        </div>
    </div>

    <div class="card p-5 flex flex-col">
        <div class="flex items-center justify-between">
            <p class="text-[11px] font-bold tracking-widest uppercase text-amber-600">Tukar hadiah</p>
            <span class="material-symbols-outlined icon-fill text-amber-500">stars</span>
        </div>
        @if ($featuredReward)
            @php $affordable = auth()->user()->points >= $featuredReward->points_cost; @endphp
            <h3 class="mt-2 font-display text-xl font-bold leading-snug">E-Wallet & Saldo Transportasi</h3>
            <p class="mt-2 text-sm text-slate-600">
                Tukarkan poin Anda untuk voucher & saldo resmi. Hadiah tersedia mulai dari {{ $featuredReward->points_cost }} poin.
            </p>
            <div class="mt-4 flex items-center gap-3 rounded-xl bg-civic-surface p-3">
                <span class="grid place-items-center size-11 shrink-0 rounded-lg bg-civic-amber text-civic-amberink">
                    <span class="material-symbols-outlined">{{ $featuredReward->icon }}</span>
                </span>
                <span class="min-w-0">
                    <span class="block truncate text-sm font-semibold">{{ $featuredReward->name }}</span>
                    <span class="block text-xs font-semibold text-amber-600">{{ $featuredReward->points_cost }} Poin dibutuhkan</span>
                </span>
            </div>
            <a href="{{ route('hadiah.index') }}"
               class="btn mt-auto w-full {{ $affordable ? 'bg-civic-amber text-civic-amberink hover:brightness-95' : 'bg-civic-chip text-civic-navy' }}">
                {{ $affordable ? 'Klaim Voucher Sekarang' : 'Lihat Hadiah' }}
                <span class="material-symbols-outlined !text-[18px]">chevron_right</span>
            </a>
        @else
            <p class="mt-3 text-sm text-slate-500">Belum ada hadiah tersedia.</p>
        @endif
    </div>
</section>

{{-- ============ KESELAMATAN + FOOTER ============ --}}
<section class="mt-6 card flex items-start gap-4 p-5">
    <span class="grid place-items-center size-11 shrink-0 rounded-full bg-civic-chip text-civic-navy">
        <span class="material-symbols-outlined">shield</span>
    </span>
    <div>
        <h3 class="font-display font-bold">Peringatan Keselamatan Berkendara</h3>
        <p class="mt-1 text-sm text-slate-600">
            Utamakan keselamatan. Jangan melapor saat sedang mengemudi. Gunakan dashboard cam atau laporkan saat kendaraan berhenti di tempat aman.
        </p>
    </div>
</section>

<footer class="mt-8 flex flex-col gap-3 text-sm text-slate-600 sm:flex-row sm:items-center sm:justify-between">
    <nav class="flex flex-wrap gap-x-6 gap-y-2">
        <a href="#" class="hover:text-civic-navy">Tentang Kami</a>
        <a href="#" class="hover:text-civic-navy">Kontak</a>
        <a href="#" class="hover:text-civic-navy">Tautan</a>
        <a href="#" class="hover:text-civic-navy">Syarat & Ketentuan</a>
        <a href="#" class="hover:text-civic-navy">FAQ</a>
    </nav>
    <p class="text-xs">© {{ date('Y') }} CivicWatch Traffic. All rights reserved.</p>
</footer>

@include('partials.proof-modal')
@endsection

@push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="{{ asset('js/map.js') }}"></script>
@endpush
