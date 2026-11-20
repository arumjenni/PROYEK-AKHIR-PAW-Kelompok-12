@extends('layouts.staff')

@section('title', 'Review Laporan '.$report->code.' — CivicWatch Traffic')

@push('head')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
@endpush

@section('content')
@php $me = auth()->user(); @endphp

<div class="flex flex-wrap items-center justify-between gap-3">
    <div class="flex flex-wrap items-center gap-3">
        <h1 class="font-display text-3xl font-extrabold text-civic-ink">Review Laporan</h1>
        <span class="rounded-lg bg-civic-chip px-3 py-1 font-mono text-sm font-bold text-civic-navy">{{ $report->code }}</span>
        <x-status-badge :status="$report->status" :staff="true" class="uppercase" />
    </div>
    <div class="flex items-center gap-3 text-xs text-slate-500">
        <span>{{ $pending }} laporan menunggu</span>
        <a href="{{ route('antrean.index') }}" class="btn-light !py-2"><span class="material-symbols-outlined !text-[18px]">arrow_back</span> Kembali ke Antrean</a>
    </div>
</div>

<div class="mt-6 grid gap-6 lg:grid-cols-[minmax(0,1.15fr)_minmax(0,1fr)] lg:items-start">

    {{-- ============ KIRI: BUKTI ============ --}}
    <div class="space-y-6">
        <section class="card overflow-hidden">
            <div class="flex flex-wrap items-center gap-2 px-5 py-3 text-xs">
                <span class="inline-flex items-center gap-1.5 rounded-full bg-sky-100 px-3 py-1 font-bold text-civic-teal">
                    <span class="size-2 rounded-full bg-civic-teal"></span> LIVE CAPTURE — ASLI
                </span>
                <span class="text-slate-500">Foto diambil langsung dari kamera aplikasi</span>
            </div>

            <div class="relative aspect-[4/3] bg-slate-900">
                @if ($report->photo_url)
                    <img src="{{ $report->photo_url }}" alt="Bukti {{ $report->plate_number }}" class="size-full object-contain">
                @else
                    <div class="grid size-full place-items-center px-6 text-center text-slate-300">
                        <div>
                            <span class="material-symbols-outlined !text-[64px]">directions_car</span>
                            <p class="mt-2 font-display text-lg font-bold text-white">{{ $report->plate_number }} — {{ $report->type->name }}</p>
                            <p class="text-sm">Foto bukti tidak tersedia (data contoh)</p>
                        </div>
                    </div>
                @endif
            </div>

            <div class="flex flex-wrap items-center gap-x-6 gap-y-1 bg-civic-surface px-5 py-3 text-sm">
                <span class="font-display font-bold">{{ $report->plate_number }}</span>
                <span class="inline-flex items-center gap-1.5 rounded-md bg-red-600 px-2 py-0.5 text-xs font-semibold text-white">{{ $report->type->name }}</span>
            </div>
        </section>

        <section class="card p-5">
            <div class="flex items-center justify-between">
                <h2 class="flex items-center gap-2 font-display font-bold"><span class="material-symbols-outlined text-civic-teal">location_on</span> Lokasi Kejadian</h2>
                <span class="text-xs text-slate-500">{{ $report->location_name ?: 'Nama jalan tidak tercatat' }}</span>
            </div>
            @if ($report->latitude && $report->longitude)
                <div id="reviewMap" data-lat="{{ $report->latitude }}" data-lng="{{ $report->longitude }}" data-label="{{ $report->plate_number }}"
                     class="mt-3 h-56 overflow-hidden rounded-xl z-0"></div>
            @else
                <p class="mt-3 rounded-xl bg-civic-surface p-6 text-center text-sm text-slate-500">Koordinat tidak tersedia.</p>
            @endif
        </section>
    </div>

    {{-- ============ KANAN: METADATA & KEPUTUSAN ============ --}}
    <div class="space-y-6">
        <section class="card p-5">
            <div class="flex items-center justify-between">
                <h2 class="flex items-center gap-2 font-display font-bold"><span class="material-symbols-outlined text-civic-navy">lock</span> Metadata Terkunci dari Kamera</h2>
                <span class="rounded bg-civic-chip px-2 py-0.5 text-[10px] font-bold tracking-wider text-civic-teal">TAMPER-PROOF</span>
            </div>

            <div class="mt-4 space-y-3">
                <div class="rounded-xl bg-civic-surface p-4">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500">GPS coordinates & lokasi</p>
                    <p class="font-display font-bold">{{ $report->location_name ?: '-' }}</p>
                    <p class="text-sm text-civic-teal">
                        @if ($report->latitude) {{ number_format($report->latitude, 4) }}, {{ number_format($report->longitude, 4) }}
                            · <a target="_blank" rel="noopener" class="underline" href="https://www.openstreetmap.org/?mlat={{ $report->latitude }}&mlon={{ $report->longitude }}#map=17/{{ $report->latitude }}/{{ $report->longitude }}">buka peta ↗</a>
                        @else - @endif
                    </p>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div class="rounded-xl bg-civic-surface p-4">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Timestamp</p>
                        <p class="font-display font-bold">{{ $report->captured_at->translatedFormat('d M Y') }}</p>
                        <p class="text-xs text-slate-500">{{ $report->captured_at->format('H:i:s') }} WIB</p>
                    </div>
                    <div class="rounded-xl bg-civic-surface p-4">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Plat nomor</p>
                        <p class="font-display font-bold">{{ $report->plate_number }}</p>
                        <p class="text-xs text-civic-teal">Dilaporkan manual oleh warga</p>
                    </div>
                </div>
                <div class="flex flex-wrap items-center justify-between gap-2 text-sm">
                    <span class="text-slate-600">Reputasi pelapor <b>{{ $report->user->name }}</b>:</span>
                    <span class="font-semibold text-amber-600">
                        {{ $reporterAccuracy !== null ? 'Skor '.$reporterAccuracy.'%' : 'Belum ada riwayat' }}
                        <span class="font-normal text-slate-500">({{ $reporterLevel }})</span>
                    </span>
                </div>
                @if ($report->notes)
                    <div class="rounded-xl bg-civic-surface p-4 text-sm">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Catatan pelapor</p>
                        <p class="mt-1 text-slate-700">{{ $report->notes }}</p>
                    </div>
                @endif
            </div>
        </section>

        @if ($report->isPending())
            <form method="POST" id="reviewForm" class="card space-y-4 p-5">
                @csrf
                <div>
                    <div class="flex items-center justify-between">
                        <label for="violation_type_id" class="label !mb-0">Jenis Pelanggaran Teridentifikasi</label>
                        <span class="rounded bg-civic-chip px-2 py-0.5 text-[10px] font-semibold text-civic-teal">Dapat dikoreksi</span>
                    </div>
                    <select id="violation_type_id" name="violation_type_id" class="input mt-1.5">
                        @foreach ($types as $t)
                            <option value="{{ $t->id }}" @selected(old('violation_type_id', $report->violation_type_id) == $t->id)>{{ $t->name }} (+{{ $t->points }} poin)</option>
                        @endforeach
                    </select>
                    @error('violation_type_id') <p class="field-error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <div class="flex items-center justify-between">
                        <label for="reject_reason" class="label !mb-0">Alasan Penolakan <span class="text-red-600">*</span></label>
                        <span class="text-xs text-slate-500">Max 255 karakter</span>
                    </div>
                    <textarea id="reject_reason" name="reject_reason" rows="3" maxlength="255" class="input mt-1.5 resize-none"
                              placeholder="Tulis alasan jika laporan tidak valid (cth: nomor plat tidak terbaca jelas, kendaraan darurat bertugas, dsb)…">{{ old('reject_reason') }}</textarea>
                    @error('reject_reason') <p class="field-error">{{ $message }}</p> @enderror
                    <p id="reasonHint" class="mt-1.5 flex items-center gap-1 text-xs text-slate-500">
                        <span class="material-symbols-outlined !text-[14px]">info</span> Wajib diisi untuk Tolak (Invalid). Tidak diperlukan untuk persetujuan.
                    </p>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <button type="submit" formaction="{{ route('antrean.approve', $report) }}"
                            class="btn bg-emerald-700 text-white hover:bg-emerald-800 !py-4">
                        <span class="material-symbols-outlined">check_circle</span> Setujui (Valid)
                    </button>
                    <button type="submit" id="btnReject" formaction="{{ route('antrean.reject', $report) }}"
                            class="btn bg-red-700 text-white hover:bg-red-800 !py-4">
                        <span class="material-symbols-outlined">cancel</span> Tolak (Invalid)
                    </button>
                </div>

                <p class="rounded-xl bg-civic-surface px-4 py-3 text-center text-xs text-slate-600">
                    Keputusan tercatat resmi atas nama <b>{{ $me->name }}</b>@if ($me->badge_number) (NRP {{ $me->badge_number }})@endif @if ($me->unit) • {{ $me->unit }}@endif.
                </p>
            </form>
        @else
            <section class="card space-y-3 p-5">
                <h2 class="font-display font-bold">Keputusan verifikasi</h2>
                <x-status-badge :status="$report->status" :staff="true" />
                <dl class="grid grid-cols-2 gap-3 text-sm">
                    <div><dt class="text-xs text-slate-500">Diputuskan oleh</dt><dd class="font-semibold">{{ $report->reviewer?->name ?? '-' }}</dd></div>
                    <div><dt class="text-xs text-slate-500">Waktu keputusan</dt><dd>{{ $report->reviewed_at?->translatedFormat('d M Y, H:i') }} WIB</dd></div>
                    <div><dt class="text-xs text-slate-500">Jenis pelanggaran</dt><dd class="font-semibold">{{ $report->type->name }}</dd></div>
                    <div><dt class="text-xs text-slate-500">Poin diberikan</dt><dd class="font-semibold text-civic-teal">+{{ $report->points_awarded }}</dd></div>
                </dl>
                @if ($report->reject_reason)
                    <p class="rounded-xl bg-red-50 px-4 py-3 text-sm text-red-800"><b>Alasan penolakan:</b> {{ $report->reject_reason }}</p>
                @endif
                <a href="{{ route('verifikasi.riwayat') }}" class="btn-light w-full">Kembali ke riwayat</a>
            </section>
        @endif

        <section class="rounded-2xl bg-civic-surface p-5">
            <h3 class="flex items-center gap-2 font-display font-bold"><span class="material-symbols-outlined text-civic-teal">policy</span> SOP Verifikasi</h3>
            <p class="mt-2 text-sm leading-relaxed text-slate-600">
                Pastikan plat nomor terbaca jelas, pelanggaran terlihat pada foto, dan lokasi sesuai dengan koordinat sebelum menyetujui. Poin otomatis diberikan ke pelapor saat laporan dinyatakan Valid.
            </p>
        </section>
    </div>
</div>
@endsection

@push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        (function () {
            // Peta lokasi kejadian
            var el = document.getElementById('reviewMap');
            if (el && typeof L !== 'undefined') {
                var pos = [parseFloat(el.dataset.lat), parseFloat(el.dataset.lng)];
                var map = L.map(el, { scrollWheelZoom: false }).setView(pos, 16);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19, attribution: '&copy; OpenStreetMap contributors' }).addTo(map);
                L.circleMarker(pos, { radius: 10, color: '#fff', weight: 3, fillColor: '#C62828', fillOpacity: 1 }).addTo(map);
            }

            // Alasan wajib diisi untuk Tolak
            var reject = document.getElementById('btnReject');
            var reason = document.getElementById('reject_reason');
            var hint = document.getElementById('reasonHint');
            if (reject && reason) {
                reject.addEventListener('click', function (e) {
                    if (!reason.value.trim()) {
                        e.preventDefault();
                        hint.classList.add('text-red-600');
                        reason.focus();
                        return;
                    }
                    if (!confirm('Tolak laporan ini sebagai INVALID?')) e.preventDefault();
                });
            }
        })();
    </script>
@endpush
