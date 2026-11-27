<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ExportLog;
use App\Models\Report;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Ekspor rekap laporan. Sengaja TIDAK menyertakan NIK, kontak, maupun identitas pelapor.
 */
class ExportController extends Controller
{
    private const PDF_MAX_ROWS = 2000;

    public function index(): View
    {
        return view('admin.export.index', [
            'from' => today()->subDays(6)->toDateString(),
            'to' => today()->toDateString(),
            'logs' => ExportLog::latest()->limit(5)->get(),
        ]);
    }

    /** Ringkasan jumlah (dipanggil JS saat filter berubah). */
    public function summary(Request $request): JsonResponse
    {
        [$from, $to, $status] = $this->params($request);

        $counts = Report::whereBetween('captured_at', [$from, $to])
            ->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');

        $selected = $status === 'all' ? (int) $counts->sum() : (int) ($counts[$status] ?? 0);

        return response()->json([
            'days' => (int) $from->diffInDays($to->copy()->startOfDay()) + 1,
            'selected' => $selected,
            'approved' => (int) ($counts['approved'] ?? 0),
            'pending' => (int) ($counts['pending'] ?? 0),
            'rejected' => (int) ($counts['rejected'] ?? 0),
            'sizeKb' => max(1, (int) round($selected * 0.12)),
        ]);
    }

    public function download(Request $request): StreamedResponse|View|RedirectResponse
    {
        [$from, $to, $status] = $this->params($request);
        $format = $request->query('format') === 'pdf' ? 'pdf' : 'csv';

        $query = $this->query($from, $to, $status);
        $total = (clone $query)->count();

        if ($total === 0) {
            return back()->with('error', 'Tidak ada laporan pada rentang dan status yang dipilih.');
        }

        // "Unduh ulang" dari riwayat tidak dicatat dua kali
        if (! $request->boolean('nolog')) {
            ExportLog::create([
                'user_id' => $request->user()->id,
                'date_from' => $from->toDateString(),
                'date_to' => $to->toDateString(),
                'status_filter' => $status,
                'format' => $format,
                'total_rows' => $total,
            ]);
        }

        return $format === 'pdf'
            ? $this->pdf($query, $from, $to, $status, $total)
            : $this->csv($query, $from, $to);
    }

    /* ----------------------------------------------------------- format */

    private function csv(Builder $query, Carbon $from, Carbon $to): StreamedResponse
    {
        $filename = 'laporan-civicwatch-'.$from->format('Ymd').'-'.$to->format('Ymd').'.csv';

        return response()->streamDownload(function () use ($query) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // BOM agar Excel membaca UTF-8
            fputcsv($out, ['ID Laporan', 'Tanggal', 'Waktu', 'Nomor Polisi', 'Lokasi', 'Jenis Pelanggaran', 'Status Verifikasi'], ',', '"', '\\');

            $query->chunkById(500, function ($rows) use ($out) {
                foreach ($rows as $r) {
                    fputcsv($out, [
                        $r->code,
                        $r->captured_at?->format('Y-m-d'),
                        $r->captured_at?->format('H:i'),
                        $r->plate_number,
                        $this->safe($r->location_name),
                        $r->type->name,
                        $r->staffStatusLabel(),
                    ], ',', '"', '\\');
                }
            });

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /** PDF: halaman cetak yang rapi; browser membuka dialog cetak -> "Simpan sebagai PDF". */
    private function pdf(Builder $query, Carbon $from, Carbon $to, string $status, int $total): View
    {
        return view('admin.export.print', [
            'rows' => $query->orderBy('captured_at')->limit(self::PDF_MAX_ROWS)->get(),
            'from' => $from,
            'to' => $to,
            'status' => $status,
            'total' => $total,
            'truncated' => $total > self::PDF_MAX_ROWS,
            'max' => self::PDF_MAX_ROWS,
            'generatedBy' => auth()->user()->name,
        ]);
    }

    /* ----------------------------------------------------------- helper */

    /** @return array{0:Carbon,1:Carbon,2:string} */
    private function params(Request $request): array
    {
        $data = $request->validate([
            'from' => ['required', 'date'],
            'to' => ['required', 'date', 'after_or_equal:from'],
            'status' => ['nullable', 'in:all,pending,approved,rejected'],
        ]);

        return [
            Carbon::parse($data['from'])->startOfDay(),
            Carbon::parse($data['to'])->endOfDay(),
            $data['status'] ?? 'all',
        ];
    }

    private function query(Carbon $from, Carbon $to, string $status): Builder
    {
        return Report::with('type')
            ->whereBetween('captured_at', [$from, $to])
            ->when($status !== 'all', fn ($q) => $q->where('status', $status));
    }

    /** Cegah CSV/formula injection di Excel. */
    private function safe(?string $value): string
    {
        $value = (string) $value;

        return preg_match('/^[=+\-@\t\r]/', $value) ? "'".$value : $value;
    }
}
