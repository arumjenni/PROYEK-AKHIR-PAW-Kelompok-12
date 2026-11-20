{{-- Modal detail/bukti laporan. Dibuka dari tombol dengan atribut data-proof (lihat public/js/app.js) --}}
<div id="proofModal" class="hidden fixed inset-0 z-50 flex items-end sm:items-center justify-center bg-civic-ink/60 p-0 sm:p-4" role="dialog" aria-modal="true">
    <div class="w-full sm:max-w-lg max-h-[92vh] overflow-y-auto rounded-t-3xl sm:rounded-3xl bg-white shadow-2xl">
        <div class="flex items-center justify-between px-5 py-4 border-b border-civic-line">
            <div>
                <p class="eyebrow">Detail laporan</p>
                <p id="pmCode" class="text-xs text-slate-500"></p>
            </div>
            <button type="button" data-modal-close class="grid place-items-center size-9 rounded-full hover:bg-civic-chip" aria-label="Tutup">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>

        <div class="p-5 space-y-4">
            <div class="relative aspect-video overflow-hidden rounded-2xl bg-civic-surface grid place-items-center">
                <img id="pmPhoto" alt="Foto bukti" class="hidden absolute inset-0 size-full object-cover">
                <p id="pmNoPhoto" class="px-6 text-center text-sm text-slate-500">
                    <span class="material-symbols-outlined !text-[32px] block mx-auto mb-1">image_not_supported</span>
                    Foto bukti tidak tersedia (data contoh).
                </p>
            </div>

            <div id="pmReasonBox" class="hidden rounded-xl bg-red-50 px-4 py-3 text-sm text-red-800 ring-1 ring-red-200">
                <b>Alasan penolakan:</b> <span id="pmReason"></span>
            </div>

            <dl class="grid grid-cols-2 gap-x-4 gap-y-3 text-sm">
                <div><dt class="text-xs text-slate-500">Plat kendaraan</dt><dd id="pmPlate" class="font-bold"></dd></div>
                <div><dt class="text-xs text-slate-500">Status</dt><dd id="pmStatus" class="font-semibold"></dd></div>
                <div class="col-span-2"><dt class="text-xs text-slate-500">Jenis pelanggaran</dt><dd id="pmType" class="font-semibold"></dd></div>
                <div class="col-span-2"><dt class="text-xs text-slate-500">Waktu</dt><dd id="pmDate"></dd></div>
                <div class="col-span-2"><dt class="text-xs text-slate-500">Lokasi</dt>
                    <dd><span id="pmLocation"></span> <a id="pmMapLink" target="_blank" rel="noopener" class="hidden text-civic-teal font-semibold hover:underline">Lihat di peta ↗</a></dd></div>
                <div class="col-span-2"><dt class="text-xs text-slate-500">Catatan</dt><dd id="pmNotes" class="text-slate-700"></dd></div>
            </dl>
        </div>
    </div>
</div>
