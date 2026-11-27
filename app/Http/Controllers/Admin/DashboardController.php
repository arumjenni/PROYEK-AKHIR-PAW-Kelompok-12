<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $days = in_array((int) $request->query('days'), [7, 14, 30], true) ? (int) $request->query('days') : 7;

        $to = today();
        $from = $to->copy()->subDays($days - 1);
        $range = [$from->copy()->startOfDay(), $to->copy()->endOfDay()];

        $inRange = fn () => Report::whereBetween('captured_at', $range);

        // --- Kartu ringkasan
        $counts = $inRange()->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');
        $total = (int) $counts->sum();
        $pending = (int) ($counts[Report::STATUS_PENDING] ?? 0);
        $valid = (int) ($counts[Report::STATUS_APPROVED] ?? 0);
        $invalid = (int) ($counts[Report::STATUS_REJECTED] ?? 0);

        $prevTotal = Report::whereBetween('captured_at', [
            $from->copy()->subDays($days)->startOfDay(),
            $from->copy()->subDay()->endOfDay(),
        ])->count();

        $pct = fn (int $n) => $total > 0 ? round($n / $total * 100, 1) : 0;

        // --- Tren harian
        $perDay = $inRange()->get(['captured_at'])
            ->groupBy(fn ($r) => $r->captured_at->toDateString())
            ->map->count();

        $series = [];
        for ($d = $from->copy(); $d->lte($to); $d->addDay()) {
            $series[] = [
                'day' => $d->format('d'),
                'name' => $d->translatedFormat('D'),
                'count' => (int) ($perDay[$d->toDateString()] ?? 0),
                'today' => $d->isSameDay($to),
            ];
        }
        $todayCount = end($series)['count'];
        $yesterdayCount = count($series) > 1 ? $series[count($series) - 2]['count'] : 0;

        // --- Jenis pelanggaran terbanyak
        $topTypes = $inRange()->selectRaw('violation_type_id, count(*) as total')
            ->groupBy('violation_type_id')->orderByDesc('total')->limit(3)
            ->with('type')->get();

        // --- Waktu respons verifikasi
        $reviewed = $inRange()->whereNotNull('reviewed_at')->get(['captured_at', 'reviewed_at']);
        $avgMinutes = $reviewed->isEmpty()
            ? null
            : (int) round($reviewed->avg(fn ($r) => abs($r->captured_at->diffInMinutes($r->reviewed_at))));

        return view('admin.dashboard', [
            'days' => $days,
            'from' => $from,
            'to' => $to,
            'cards' => [
                'total' => $total,
                'totalChange' => $prevTotal > 0 ? round(($total - $prevTotal) / $prevTotal * 100, 1) : null,
                'pending' => $pending,
                'valid' => $valid,
                'validPercent' => $pct($valid),
                'invalid' => $invalid,
                'invalidPercent' => $pct($invalid),
            ],
            'series' => $series,
            'maxCount' => max(1, collect($series)->max('count')),
            'todayCount' => $todayCount,
            'todayChange' => $yesterdayCount > 0 ? round(($todayCount - $yesterdayCount) / $yesterdayCount * 100, 1) : null,
            'yesterdayCount' => $yesterdayCount,
            'topTypes' => $topTypes,
            'total' => $total,
            'avgMinutes' => $avgMinutes,
            'reviewedPercent' => $total > 0 ? (int) round(($valid + $invalid) / $total * 100) : 0,
        ]);
    }
}
