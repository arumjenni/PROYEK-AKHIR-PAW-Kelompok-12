@props(['status', 'staff' => false])
@php
    // $staff = true -> istilah petugas/admin (Valid/Invalid), false -> istilah warga (Disetujui/Ditolak)
    $map = [
        'pending'  => ['Menunggu',  'bg-civic-amber text-civic-amberink', 'hourglass_top'],
        'approved' => [$staff ? 'Valid' : 'Disetujui',   'bg-sky-100 text-civic-teal', 'check_circle'],
        'rejected' => [$staff ? 'Invalid' : 'Ditolak',   'bg-red-100 text-red-700',    'cancel'],
    ];
    [$label, $classes, $icon] = $map[$status] ?? [$status, 'bg-slate-100 text-slate-600', 'help'];
@endphp
<span {{ $attributes->merge(['class' => "inline-flex items-center gap-1 rounded-full px-3 py-1 text-xs font-semibold whitespace-nowrap $classes"]) }}>
    <span class="material-symbols-outlined !text-[15px]">{{ $icon }}</span>{{ $label }}
</span>
