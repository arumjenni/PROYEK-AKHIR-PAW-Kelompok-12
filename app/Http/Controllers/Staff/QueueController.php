<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Report;
use App\Models\User;
use App\Models\ViolationType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/** Antrean & review laporan - dipakai petugas dan admin. */
class QueueController extends Controller
{
    /* ----------------------------------------------------------- daftar antrean */

    public function index(Request $request): View
    {
        $sort = $request->query('sort') === 'newest' ? 'newest' : 'oldest';

        $reports = Report::with(['type', 'user'])
            ->where('status', Report::STATUS_PENDING)
            ->orderBy('captured_at', $sort === 'newest' ? 'desc' : 'asc')
            ->paginate(5)
            ->withQueryString();

        return view('staff.queue.index', compact('reports', 'sort'));
    }

    /** "Tinjau Instan": langsung buka laporan menunggu yang paling lama. */
    public function next(Request $request): RedirectResponse
    {
        $request->session()->reflash(); // pertahankan pesan sukses dari keputusan sebelumnya

        $report = Report::where('status', Report::STATUS_PENDING)->orderBy('captured_at')->first();

        return $report
            ? redirect()->route('antrean.show', $report)
            : redirect()->route('antrean.index')->with('success', 'Antrean kosong. Semua laporan sudah diverifikasi.');
    }

    /* ----------------------------------------------------------- review */

    public function show(Report $report): View
    {
        $report->load(['type', 'user', 'reviewer']);

        return view('staff.queue.show', [
            'report' => $report,
            'types' => ViolationType::where('is_active', true)->orderBy('name')->get(),
            'reporterAccuracy' => $report->user->reportAccuracy(),
            'reporterLevel' => $report->user->levelInfo()['current']['name'],
            'pending' => Report::where('status', Report::STATUS_PENDING)->count(),
        ]);
    }

    /** Setujui (Valid): poin otomatis masuk ke saldo warga sesuai pengaturan jenis pelanggaran saat ini. */
    public function approve(Request $request, Report $report): RedirectResponse
    {
        $data = $request->validate([
            'violation_type_id' => ['required', Rule::exists('violation_types', 'id')],
        ]);

        $points = DB::transaction(function () use ($request, $report, $data) {
            $locked = Report::whereKey($report->id)->lockForUpdate()->firstOrFail();

            if (! $locked->isPending()) {
                return null;
            }

            $type = ViolationType::findOrFail($data['violation_type_id']);

            $locked->update([
                'status' => Report::STATUS_APPROVED,
                'violation_type_id' => $type->id,
                'points_awarded' => $type->points,
                'reject_reason' => null,
                'reviewed_by' => $request->user()->id,
                'reviewed_at' => now(),
            ]);

            User::whereKey($locked->user_id)->increment('points', $type->points);

            return $type->points;
        });

        if ($points === null) {
            return redirect()->route('antrean.index')->with('error', 'Laporan itu sudah diputuskan oleh petugas lain.');
        }

        return redirect()->route('antrean.next')
            ->with('success', "Laporan {$report->code} dinyatakan VALID. +{$points} poin diberikan ke pelapor.");
    }

    /** Tolak (Invalid): alasan wajib. */
    public function reject(Request $request, Report $report): RedirectResponse
    {
        $data = $request->validate([
            'reject_reason' => ['required', 'string', 'max:255'],
        ]);

        $done = DB::transaction(function () use ($request, $report, $data) {
            $locked = Report::whereKey($report->id)->lockForUpdate()->firstOrFail();

            if (! $locked->isPending()) {
                return false;
            }

            $locked->update([
                'status' => Report::STATUS_REJECTED,
                'points_awarded' => 0,
                'reject_reason' => trim($data['reject_reason']),
                'reviewed_by' => $request->user()->id,
                'reviewed_at' => now(),
            ]);

            return true;
        });

        if (! $done) {
            return redirect()->route('antrean.index')->with('error', 'Laporan itu sudah diputuskan oleh petugas lain.');
        }

        return redirect()->route('antrean.next')->with('success', "Laporan {$report->code} dinyatakan INVALID.");
    }

    /* ----------------------------------------------------------- riwayat */

    public function history(Request $request): View
    {
        $user = $request->user();
        $status = in_array($request->query('status'), ['approved', 'rejected'], true) ? $request->query('status') : null;

        // Petugas melihat keputusannya sendiri; admin melihat semua keputusan.
        $base = Report::whereNotNull('reviewed_at')
            ->when($user->isPetugas(), fn ($q) => $q->where('reviewed_by', $user->id));

        $counts = (clone $base)->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');

        $reports = (clone $base)->with(['type', 'user', 'reviewer'])
            ->when($status, fn ($q) => $q->where('status', $status))
            ->latest('reviewed_at')
            ->paginate(10)
            ->withQueryString();

        return view('staff.queue.history', [
            'reports' => $reports,
            'status' => $status,
            'counts' => [
                'all' => (int) $counts->sum(),
                'approved' => (int) ($counts['approved'] ?? 0),
                'rejected' => (int) ($counts['rejected'] ?? 0),
            ],
        ]);
    }
}
