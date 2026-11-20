{{-- Thumbnail bukti: foto bila ada, jika tidak ikon mobil. Variabel: $r (Report), $class --}}
<div class="{{ $class ?? 'size-12' }} shrink-0 overflow-hidden rounded-lg bg-civic-surface grid place-items-center text-slate-400">
    @if ($r->photo_url)
        <img src="{{ $r->photo_url }}" alt="Bukti {{ $r->plate_number }}" class="size-full object-cover" loading="lazy">
    @else
        <span class="material-symbols-outlined">directions_car</span>
    @endif
</div>
