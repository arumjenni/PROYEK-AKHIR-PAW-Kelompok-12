<?php

namespace App\Http\Controllers\Warga;

use App\Http\Controllers\Controller;
use App\Models\Report;
use App\Models\RewardItem;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();

        $counts = $user->reports()
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $approved = (int) ($counts[Report::STATUS_APPROVED] ?? 0);
        $rejected = (int) ($counts[Report::STATUS_REJECTED] ?? 0);
        $decided = $approved + $rejected;

        $stats = [
            'total' => (int) $counts->sum(),
            'pending' => (int) ($counts[Report::STATUS_PENDING] ?? 0),
            'approved' => $approved,
            'accuracy' => $decided > 0 ? round($approved / $decided * 100, 1) : 0,
        ];

        $recentReports = $user->reports()->with('type')->latest('captured_at')->limit(3)->get();

        // Titik pelanggaran terverifikasi (semua warga). Plat nomor sengaja tidak ditampilkan.
        $mapPoints = Report::with('type')
            ->where('status', Report::STATUS_APPROVED)
            ->whereNotNull('latitude')->whereNotNull('longitude')
            ->latest('captured_at')->limit(100)->get()
            ->map(fn (Report $r) => [
                'lat' => $r->latitude,
                'lng' => $r->longitude,
                'type' => $r->type->name,
                'place' => $r->location_name,
                'date' => $r->captured_at?->translatedFormat('d M Y'),
            ])->values();

        // Hadiah yang disorot: yang termahal yang masih terjangkau, kalau tidak ada -> termurah
        $rewards = RewardItem::where('is_active', true)->orderBy('points_cost')->get();
        $featuredReward = $rewards->filter(fn ($r) => $r->points_cost <= $user->points)->last() ?? $rewards->first();

        return view('warga.dashboard', compact('stats', 'recentReports', 'mapPoints', 'featuredReward'));
    }
}
