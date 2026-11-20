@extends('layouts.staff')

@section('title', 'Ekspor Data — CivicWatch Traffic')

@section('content')
<div>
    <p class="eyebrow">Arsip & pelaporan publik</p>
    <h1 class="mt-1 font-display text-3xl font-extrabold text-civic-ink">Ekspor data laporan</h1>
    <p class="mt-1 text-sm text-slate-600">Siapkan rekap laporan. Pilih periode dan status laporan untuk mengunduh data CivicWatch Traffic.</p>
</div>

<form id="exportForm" method="GET" action="{{ route('admin.export.download') }}" class="mt-6 grid gap-6 lg:grid-cols-[minmax(0,1fr)_320px] lg:items-start"
      data-summary-url="{{ route('admin.export.summary') }}">

    <div class="space-y-6">
        {{-- Rentang waktu & status --}}
        <section class="card p-5 sm:p-6">
            <div class="flex items-center justify-between">
                <h2 class="flex items-center gap-2 font-display font-bold"><span class="material-symbols-outlined text-civic-navy">date_range</span> Rentang Waktu (Date Range)</h2>
                <span id="periodChip" class="rounded bg-sky-100 px-2 py-0.5 text-[11px] font-semibold text-civic-teal">Periode terpilih: 7 hari</span>
            </div>
            <div class="mt-4 grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="from" class="label">Tanggal Mulai</label>
                    <input id="from" type="date" name="from" value="{{ old('from', $from) }}" class="input" required>
                    <p class="mt-1 text-[11px] text-slate-500">00:00:00 WIB (cut-off awal)</p>
                    @error('from') <p class="field-error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="to" class="label">Tanggal Akhir</label>
                    <input id="to" type="date" name="to" value="{{ old('to', $to) }}" class="input" required>
                    <p class="mt-1 text-[11px] text-slate-500">23:59:59 WIB (cut-off akhir)</p>
                    @error('to') <p class="field-error">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="mt-5">
                <label for="status" class="label flex items-center gap-2"><span class="material-symbols-outlined !text-[18px] text-civic-navy">filter_list</span> Status Laporan (Report Status)</label>
                <select id="status" name="status" class="input">
                    <option value="all">Semua status (Pending, Valid, Invalid)</option>
                    <option value="pending">Pending saja</option>
                    <option value="approved">Valid saja</option>
                    <option value="rejected">Invalid saja</option>
                </select>
                <p class="mt-1 text-[11px] text-slate-500">"Semua status" mencakup Pending, Valid, dan Invalid.</p>
            </div>

            <div class="mt-5">
                <p class="label flex items-center gap-2"><span class="material-symbols-outlined !text-[18px] text-civic-navy">description</span> Format Berkas Ekspor</p>
                <div class="grid gap-3 sm:grid-cols-2">
                    <label class="relative flex cursor-pointer items-center gap-3 rounded-2xl border border-civic-line bg-white p-4 has-[:checked]:border-civic-navy has-[:checked]:bg-civic-surface has-[:checked]:ring-2 has-[:checked]:ring-civic-navy/20">
                        <input type="radio" name="format" value="csv" class="peer sr-only" checked>
                        <span class="grid size-11 place-items-center rounded-xl bg-civic-navy text-white"><span class="material-symbols-outlined">table_view</span></span>
                        <span><span class="block font-display font-bold">CSV <span class="ml-1 rounded bg-civic-chip px-1.5 text-[10px] font-medium text-civic-teal">Spreadsheet</span></span>
                            <span class="text-xs text-slate-600">Kompatibel Excel & Sheets</span></span>
                    </label>
                    <label class="relative flex cursor-pointer items-center gap-3 rounded-2xl border border-civic-line bg-white p-4 has-[:checked]:border-civic-navy has-[:checked]:bg-civic-surface has-[:checked]:ring-2 has-[:checked]:ring-civic-navy/20">
                        <input type="radio" name="format" value="pdf" class="peer sr-only">
                        <span class="grid size-11 place-items-center rounded-xl bg-civic-chip text-civic-navy"><span class="material-symbols-outlined">picture_as_pdf</span></span>
                        <span><span class="block font-display font-bold">PDF <span class="ml-1 rounded bg-civic-chip px-1.5 text-[10px] font-medium text-civic-teal">Siap cetak</span></span>
                            <span class="text-xs text-slate-600">Halaman cetak → simpan sebagai PDF</span></span>
                    </label>
                </div>
            </div>
        </section>

        {{-- Data siap diekspor --}}
        <section class="rounded-2xl bg-civic-surface p-5 sm:p-6">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <h2 class="flex items-center gap-2 font-display text-lg font-bold"><span class="material-symbols-outlined text-civic-navy">dataset</span> Data siap diekspor</h2>
                <span class="rounded-full bg-civic-amber px-4 py-1.5 font-display text-sm font-bold text-civic-amberink"><span id="totalRows">…</span> LAPORAN TERCATAT</span>
            </div>
            <div class="mt-4 rounded-xl bg-white p-4 text-sm text-slate-700">
                <p class="text-[10px] font-bold uppercase tracking-wider text-civic-teal">Kolom data yang disertakan</p>
                <p class="mt-1">ID laporan, tanggal & waktu, nomor polisi, lokasi, jenis pelanggaran, dan status verifikasi.</p>
            </div>
            <div class="mt-3 flex gap-3 rounded-xl bg-white p-4 text-sm">
                <span class="material-symbols-outlined text-civic-navy">verified_user</span>
                <p class="text-slate-700"><b>Perlindungan data pribadi.</b> NIK warga, nomor kontak, serta identitas pelapor <b class="text-civic-navy">tidak disertakan</b> dalam ekspor ini demi menjaga privasi.</p>
            </div>
        </section>
    </div>

    {{-- Kolom kanan --}}
    <div class="space-y-6">
        <section class="card p-5">
            <div class="flex items-center justify-between"><h2 class="font-display font-bold">Proporsi Status</h2><span id="chipDays" class="text-xs font-semibold text-civic-teal">7 hari</span></div>
            <div class="mt-3 flex h-3 w-full overflow-hidden rounded-full bg-civic-chip" aria-hidden="true">
                <div id="barApproved" class="bg-civic-navy" style="width:0%"></div>
                <div id="barPending" class="bg-sky-400" style="width:0%"></div>
                <div id="barRejected" class="bg-red-300" style="width:0%"></div>
            </div>
            <div class="mt-3 flex flex-wrap gap-x-4 gap-y-1 text-xs text-slate-600">
                <span class="inline-flex items-center gap-1.5"><span class="size-2 rounded-full bg-civic-navy"></span>Valid: <b id="nApproved">–</b></span>
                <span class="inline-flex items-center gap-1.5"><span class="size-2 rounded-full bg-sky-400"></span>Pending: <b id="nPending">–</b></span>
                <span class="inline-flex items-center gap-1.5"><span class="size-2 rounded-full bg-red-300"></span>Invalid: <b id="nRejected">–</b></span>
            </div>
            <dl class="mt-4 space-y-1 rounded-xl bg-civic-surface p-3 text-xs">
                <div class="flex justify-between"><dt class="text-slate-600">Estimasi ukuran</dt><dd class="font-semibold">~<span id="sizeKb">–</span> KB</dd></div>
                <div class="flex justify-between"><dt class="text-slate-600">Petugas otorisasi</dt><dd class="font-semibold">{{ auth()->user()->name }}</dd></div>
            </dl>
        </section>

        <section class="card p-5">
            <button id="btnExport" class="btn-primary w-full !py-4"><span class="material-symbols-outlined">download</span> <span id="btnExportLabel">Ekspor Berkas CSV</span></button>
            <p class="mt-2 text-center text-xs text-slate-500">File akan diunduh dalam format yang dipilih.</p>
        </section>
    </div>
</form>

{{-- Riwayat ekspor --}}
<section class="mt-8 card overflow-hidden">
    <div class="px-5 py-4">
        <h2 class="font-display text-lg font-bold">Riwayat Ekspor Terakhir</h2>
        <p class="text-xs text-slate-500">Aktivitas pengunduhan rekap data oleh operator yang bertugas.</p>
    </div>
    @if ($logs->isEmpty())
        <p class="border-t border-civic-line px-5 py-8 text-center text-sm text-slate-500">Belum ada ekspor.</p>
    @else
        <div class="overflow-x-auto">
            <table class="w-full min-w-[640px] text-sm">
                <thead class="bg-civic-surface text-[11px] font-bold uppercase tracking-wider text-slate-500">
                    <tr><th class="px-5 py-3 text-left">Stempel waktu</th><th class="px-3 py-3 text-left">Rentang data</th><th class="px-3 py-3 text-left">Filter status</th><th class="px-3 py-3 text-left">Format</th><th class="px-3 py-3 text-right">Total baris</th><th class="px-5 py-3 text-right">Aksi</th></tr>
                </thead>
                <tbody class="divide-y divide-civic-line">
                    @foreach ($logs as $log)
                        <tr>
                            <td class="px-5 py-3">{{ $log->created_at->translatedFormat('d M Y, H:i') }}</td>
                            <td class="px-3 py-3 text-slate-600">{{ $log->date_from->translatedFormat('d M Y') }} – {{ $log->date_to->translatedFormat('d M Y') }}</td>
                            <td class="px-3 py-3"><span class="rounded bg-civic-chip px-2 py-0.5 text-xs">{{ $log->statusLabel() }}</span></td>
                            <td class="px-3 py-3 font-bold uppercase text-civic-navy">{{ $log->format }}</td>
                            <td class="px-3 py-3 text-right font-semibold">{{ number_format($log->total_rows, 0, ',', '.') }}</td>
                            <td class="px-5 py-3 text-right">
                                <a class="inline-flex items-center gap-1 text-xs font-semibold text-civic-teal hover:underline"
                                   href="{{ route('admin.export.download', ['from' => $log->date_from->toDateString(), 'to' => $log->date_to->toDateString(), 'status' => $log->status_filter, 'format' => $log->format, 'nolog' => 1]) }}"
                                   @if ($log->format === 'pdf') target="_blank" @endif>
                                    <span class="material-symbols-outlined !text-[16px]">download</span> Unduh Ulang
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</section>
@endsection

@push('scripts')
    <script src="{{ asset('js/export.js') }}"></script>
@endpush
