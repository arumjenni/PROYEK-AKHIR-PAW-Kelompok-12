<?php

namespace App\Http\Controllers\Petugas;

use App\Http\Controllers\Controller;
use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();

        $mine = Report::where('reviewed_by', $user->id)
            ->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');

        $valid = (int) ($mine[Report::STATUS_APPROVED] ?? 0);
        $invalid = (int) ($mine[Report::STATUS_REJECTED] ?? 0);
        $verified = $valid + $invalid;
        $totalReports = Report::count();

        $stats = [
            'total' => $totalReports,
            'verified' => $verified,
            'verifiedPercent' => $totalReports > 0 ? round($verified / $totalReports * 100, 1) : 0,
            'valid' => $valid,
            'invalid' => $invalid,
            'accuracy' => $verified > 0 ? round($valid / $verified * 100, 1) : 0,
            'today' => Report::where('reviewed_by', $user->id)->whereDate('reviewed_at', today())->count(),
        ];

        return view('petugas.dashboard', [
            'stats' => $stats,
            'pending' => Report::where('status', Report::STATUS_PENDING)->count(),
            'recent' => Report::with('type')->where('reviewed_by', $user->id)->latest('reviewed_at')->limit(5)->get(),
        ]);
    }
}
