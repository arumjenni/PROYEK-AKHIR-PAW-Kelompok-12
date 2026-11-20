@extends('layouts.warga')

@section('title', 'Laporkan Pelanggaran — CivicWatch Traffic')

@section('content')
<div class="flex flex-wrap items-end justify-between gap-4">
    <div>
        <p class="eyebrow flex items-center gap-1"><span class="material-symbols-outlined !text-[16px]">verified_user</span> Sistem pemantauan sipil resmi</p>
        <h1 class="mt-1 font-display text-3xl font-extrabold text-civic-ink">Laporkan Pelanggaran</h1>
        <p class="mt-1 text-sm text-slate-600">Ambil bukti visual langsung dari kamera dengan integritas metadata otomatis.</p>
    </div>
    <div class="flex items-center gap-3 rounded-2xl bg-civic-amber px-4 py-3">
        <span class="grid place-items-center size-9 rounded-full bg-civic-amberink text-civic-amber"><span class="material-symbols-outlined icon-fill !text-[18px]">workspace_premium</span></span>
        <div>
            <p class="text-[10px] font-bold uppercase tracking-wider text-civic-amberink/80">Insentif warga</p>
            <p class="font-display font-bold text-civic-amberink">Hingga +{{ $maxPoints }} Poin / Laporan Valid</p>
        </div>
    </div>
</div>

<div class="mt-6 grid gap-6 lg:grid-cols-[1fr_390px] lg:items-start">

    {{-- ================= KOLOM KIRI: KAMERA ================= --}}
    <div class="space-y-4">
        <div class="relative aspect-[4/3] overflow-hidden rounded-2xl bg-slate-900 shadow-card">
            <video id="cam" class="absolute inset-0 size-full object-cover" autoplay playsinline muted></video>
            <img id="preview" alt="Pratinjau foto" class="hidden absolute inset-0 size-full object-cover">

            {{-- Pesan jika kamera tidak bisa diakses --}}
            <div id="camError" class="hidden absolute inset-0 grid place-items-center bg-slate-900 px-6 text-center text-sm text-slate-200">
                <div>
                    <span class="material-symbols-outlined !text-[40px] mb-2">no_photography</span>
                    <p id="camErrorText">Kamera tidak tersedia.</p>
                </div>
            </div>

            <span id="liveBadge" class="absolute left-3 top-3 flex items-center gap-1.5 rounded-md bg-black/55 px-2.5 py-1 text-[11px] font-bold tracking-wider text-white">
                <span class="size-2 rounded-full bg-red-500 animate-pulse"></span> LIVE KAMERA
            </span>

            {{-- Bingkai bidik plat --}}
            <div id="reticle" class="pointer-events-none absolute left-1/2 top-[42%] h-[34%] w-[62%] -translate-x-1/2 -translate-y-1/2 rounded-lg border-2 border-sky-300/80">
                <span class="absolute -bottom-8 left-1/2 -translate-x-1/2 whitespace-nowrap rounded-md bg-black/50 px-2.5 py-1 text-[11px] text-white">
                    Arahkan kamera ke plat kendaraan
                </span>
            </div>

            {{-- Bar lokasi & waktu --}}
            <div class="absolute inset-x-0 bottom-0 flex flex-wrap items-center gap-x-3 gap-y-1 bg-black/60 px-3 py-2 text-[11px] text-white">
                <span id="locLabel" class="flex items-center gap-1 min-w-0 truncate cursor-pointer">
                    <span class="material-symbols-outlined !text-[15px] shrink-0">location_on</span>
                    <span id="locText" class="truncate">Mencari lokasi GPS…</span>
                </span>
                <span id="timeLabel" class="ml-auto text-white/70"></span>
                <span id="lockChip" class="rounded bg-slate-500/70 px-2 py-0.5 font-bold tracking-wider">MENCARI GPS</span>
            </div>
        </div>

        <div class="card p-6 text-center">
            {{-- Mode utama: kamera langsung --}}
            <div id="captureBlock">
                <button type="button" id="btnCapture"
                        class="mx-auto grid place-items-center size-20 rounded-full bg-civic-navy text-white ring-8 ring-civic-chip hover:bg-civic-ink transition">
                    <span id="btnCaptureIcon" class="material-symbols-outlined !text-[32px]">photo_camera</span>
                </button>
                <p id="btnCaptureLabel" class="mt-4 font-display text-lg font-bold">Ambil Foto Bukti</p>
                <p class="mx-auto mt-1 max-w-sm text-xs text-slate-600">
                    Foto diambil langsung dari kamera. Koordinat GPS dan stempel waktu otomatis tertanam pada foto.
                </p>
            </div>

            {{-- Mode cadangan: kamera langsung tidak bisa dipakai (mis. HTTP di HP / izin ditolak) --}}
            <div id="fallbackBlock" class="hidden">
                <label class="btn-primary cursor-pointer">
                    <span class="material-symbols-outlined">add_a_photo</span> Buka Kamera Ponsel / Pilih Foto
                    <input type="file" id="photo_file" name="photo_file" form="reportForm" accept="image/*" capture="environment" class="hidden">
                </label>
                <p class="mx-auto mt-3 max-w-sm text-xs text-slate-600">
                    Mode cadangan: di ponsel, tombol ini langsung membuka aplikasi kamera.
                </p>
            </div>
        </div>
        <canvas id="snap" class="hidden"></canvas>
    </div>

    {{-- ================= KOLOM KANAN: FORM ================= --}}
    <div class="space-y-6">
        <form id="reportForm" method="POST" action="{{ route('laporan.store') }}" class="card p-5 sm:p-6" novalidate>
            @csrf
            <input type="hidden" name="photo_data" id="photo_data">
            <input type="hidden" name="latitude" id="latitude" value="{{ old('latitude') }}">
            <input type="hidden" name="longitude" id="longitude" value="{{ old('longitude') }}">
            <input type="hidden" name="location_name" id="location_name" value="{{ old('location_name') }}">

            <div>
                <h2 class="font-display text-xl font-bold">Detail Laporan</h2>
                <p class="text-xs text-slate-500">Lengkapi data pelanggaran lalu lintas.</p>
            </div>

            @if ($errors->has('photo_data') || $errors->has('photo_file') || $errors->has('latitude') || $errors->has('longitude'))
                <div class="mt-4 rounded-xl bg-red-50 px-4 py-3 text-sm text-red-800 ring-1 ring-red-200">
                    @foreach (array_unique(array_merge($errors->get('photo_data'), $errors->get('photo_file'), $errors->get('latitude'))) as $msg)
                        <p>{{ $msg }}</p>
                    @endforeach
                </div>
            @endif

            <div class="mt-5">
                <label for="plate_number" class="label">Nomor Plat Kendaraan <span class="text-red-600">*</span></label>
                <div class="relative">
                    <span class="material-symbols-outlined pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400">directions_car</span>
                    <input type="text" id="plate_number" name="plate_number" value="{{ old('plate_number') }}" maxlength="15"
                           placeholder="e.g. N 1234 ABC" autocomplete="off" class="input pl-11 font-mono uppercase" required>
                </div>
                @error('plate_number') <p class="field-error">{{ $message }}</p> @enderror
            </div>

            <div class="mt-4">
                <label for="violation_type_id" class="label">Jenis Pelanggaran <span class="text-red-600">*</span></label>
                <select id="violation_type_id" name="violation_type_id" class="input" required>
                    <option value="">Pilih jenis pelanggaran</option>
                    @foreach ($types as $t)
                        <option value="{{ $t->id }}" @selected(old('violation_type_id') == $t->id)>{{ $t->name }} (+{{ $t->points }} poin)</option>
                    @endforeach
                </select>
                @error('violation_type_id') <p class="field-error">{{ $message }}</p> @enderror
            </div>

            <div class="mt-4">
                <div class="flex items-center justify-between">
                    <label for="notes" class="label !mb-0">Catatan Tambahan <span class="font-normal text-slate-500">(Opsional)</span></label>
                    <span id="notesCount" class="text-xs text-slate-500">0/150</span>
                </div>
                <textarea id="notes" name="notes" rows="3" maxlength="150" class="input mt-1.5 resize-none"
                          placeholder="Jelaskan patokan lokasi spesifik atau kronologi singkat…">{{ old('notes') }}</textarea>
                @error('notes') <p class="field-error">{{ $message }}</p> @enderror
            </div>

            <button type="submit" id="btnSubmit" class="btn-primary mt-5 w-full" disabled>
                <span class="material-symbols-outlined">send</span> Kirim Laporan
            </button>
            <p id="submitHint" class="mt-2 text-center text-xs text-slate-500">Ambil foto bukti dan pastikan lokasi GPS aktif untuk mengirim.</p>

            <div class="mt-4 flex gap-2.5 rounded-xl bg-civic-chip p-3.5 text-xs leading-relaxed text-civic-navy">
                <span class="material-symbols-outlined !text-[18px] shrink-0">info</span>
                Laporan diverifikasi langsung oleh petugas kepolisian & dinas perhubungan. Pastikan foto jernih, objektif, dan tidak membahayakan diri saat mengambil bukti.
            </div>
        </form>

        <div class="card p-5">
            <p class="text-[11px] font-bold tracking-widest uppercase text-slate-500">Alur verifikasi laporan Anda</p>
            <div class="mt-3 grid grid-cols-3 gap-2 text-center">
                <div class="rounded-xl bg-civic-amber p-3 text-civic-amberink">
                    <span class="material-symbols-outlined">hourglass_top</span>
                    <p class="text-xs font-bold">Menunggu</p><p class="text-[10px] opacity-80">Triage Petugas</p>
                </div>
                <div class="rounded-xl bg-civic-surface p-3 text-slate-600">
                    <span class="material-symbols-outlined">verified</span>
                    <p class="text-xs font-bold">Disetujui</p><p class="text-[10px]">Poin bertambah</p>
                </div>
                <div class="rounded-xl bg-civic-surface p-3 text-slate-600">
                    <span class="material-symbols-outlined">gavel</span>
                    <p class="text-xs font-bold">Penindakan</p><p class="text-[10px]">Tilang Elektronik</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    <script src="{{ asset('js/camera.js') }}"></script>
@endpush
