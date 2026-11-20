@extends('layouts.warga')

@section('title', 'Riwayat Penukaran — CivicWatch Traffic')

@section('content')
<a href="{{ route('hadiah.index') }}" class="inline-flex items-center gap-1 text-sm font-medium text-civic-teal hover:underline">
    <span class="material-symbols-outlined !text-[18px]">arrow_back</span> Kembali ke hadiah
</a>
<h1 class="mt-2 font-display text-3xl font-extrabold text-civic-ink">Riwayat penukaran</h1>
<p class="mt-1 text-sm text-slate-600">Daftar hadiah yang pernah Anda tukar dengan poin.</p>

<section class="mt-6 card overflow-hidden">
    @if ($redemptions->isEmpty())
        <div class="px-5 py-14 text-center text-sm text-slate-500">Belum ada penukaran. <a href="{{ route('hadiah.index') }}" class="font-semibold text-civic-navy underline">Lihat pilihan hadiah</a>.</div>
    @else
        <ul class="divide-y divide-civic-line">
            @foreach ($redemptions as $rd)
                <li class="flex flex-wrap items-center justify-between gap-3 px-5 py-4">
                    <div>
                        <p class="font-semibold">{{ $rd->reward_name }}</p>
                        <p class="text-xs text-slate-500">Kode <span class="font-mono font-bold">{{ $rd->code }}</span> · {{ $rd->created_at->translatedFormat('d M Y, H:i') }} WIB</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="font-semibold text-red-600">−{{ $rd->points_spent }} poin</span>
                        <span class="rounded-full bg-civic-amber px-3 py-1 text-xs font-semibold capitalize text-civic-amberink">{{ $rd->status }}</span>
                    </div>
                </li>
            @endforeach
        </ul>
    @endif
</section>

@if ($redemptions->hasPages())
    <div class="mt-4">{{ $redemptions->links() }}</div>
@endif
@endsection
