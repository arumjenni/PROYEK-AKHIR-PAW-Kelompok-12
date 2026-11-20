{{-- Tombol pembuka modal bukti. Variabel: $r (Report), $icon (true = hanya ikon mata) --}}
<button type="button"
        data-proof
        data-code="{{ $r->code }}"
        data-plate="{{ $r->plate_number }}"
        data-type="{{ $r->type->name }}"
        data-status="{{ ['pending' => 'Menunggu verifikasi', 'approved' => 'Disetujui (+'.$r->points_awarded.' poin)', 'rejected' => 'Ditolak'][$r->status] ?? $r->status }}"
        data-date="{{ $r->captured_at?->translatedFormat('d F Y') }} · {{ $r->captured_at?->format('H:i') }} WIB"
        data-location="{{ $r->location_name }}"
        data-notes="{{ $r->notes }}"
        data-photo="{{ $r->photo_url }}"
        data-reason="{{ $r->reject_reason }}"
        data-lat="{{ $r->latitude }}"
        data-lng="{{ $r->longitude }}"
        class="{{ ($icon ?? false) ? 'grid place-items-center size-9 rounded-full text-slate-500 hover:bg-civic-chip hover:text-civic-navy' : 'inline-flex items-center gap-1.5 rounded-lg bg-civic-chip px-3 py-2 text-xs font-semibold text-civic-navy hover:bg-civic-navy hover:text-white transition' }}"
        title="Lihat detail & bukti">
    <span class="material-symbols-outlined !text-[19px]">visibility</span>
    @unless ($icon ?? false) Lihat Bukti @endunless
</button>
