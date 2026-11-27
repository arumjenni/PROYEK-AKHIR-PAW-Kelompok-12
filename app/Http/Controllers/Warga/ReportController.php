<?php

namespace App\Http\Controllers\Warga;

use App\Http\Controllers\Controller;
use App\Models\Report;
use App\Models\ViolationType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ReportController extends Controller
{
    private const PLATE_REGEX = '/^([A-Z]{1,2})\s*(\d{1,4})\s*([A-Z]{0,3})$/';

    /* ---------------------------------------------------------------- READ (daftar) */

    public function index(Request $request): View
    {
        $user = $request->user();

        $status = $request->query('status');
        $sort = $request->query('sort') === 'oldest' ? 'asc' : 'desc';
        $month = $request->query('month'); // format YYYY-MM

        $counts = $user->reports()->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');

        $query = $user->reports()->with('type');

        if (in_array($status, [Report::STATUS_PENDING, Report::STATUS_APPROVED, Report::STATUS_REJECTED], true)) {
            $query->where('status', $status);
        } else {
            $status = null;
        }

        if ($month && preg_match('/^(\d{4})-(\d{2})$/', $month, $m)) {
            $query->whereYear('captured_at', (int) $m[1])->whereMonth('captured_at', (int) $m[2]);
        } else {
            $month = null;
        }

        $reports = $query->orderBy('captured_at', $sort)->paginate(10)->withQueryString();

        $summary = [
            'total' => (int) $counts->sum(),
            'pending' => (int) ($counts[Report::STATUS_PENDING] ?? 0),
            'approved' => (int) ($counts[Report::STATUS_APPROVED] ?? 0),
            'rejected' => (int) ($counts[Report::STATUS_REJECTED] ?? 0),
            'earned' => $user->totalEarnedPoints(),
        ];

        return view('warga.reports.index', [
            'reports' => $reports,
            'summary' => $summary,
            'status' => $status,
            'month' => $month,
            'sort' => $sort === 'asc' ? 'oldest' : 'latest',
        ]);
    }

    /* ---------------------------------------------------------------- CREATE */

    public function create(): View
    {
        $types = ViolationType::where('is_active', true)->orderBy('name')->get();

        return view('warga.reports.create', [
            'types' => $types,
            'maxPoints' => (int) $types->max('points'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'plate_number' => ['required', 'string', 'max:15'],
            'violation_type_id' => ['required', Rule::exists('violation_types', 'id')->where('is_active', true)],
            'notes' => ['nullable', 'string', 'max:150'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'location_name' => ['nullable', 'string', 'max:255'],
            'photo_data' => ['nullable', 'string'],
            'photo_file' => ['nullable', 'image', 'max:5120'],
        ]);

        $data['plate_number'] = $this->normalizePlate($data['plate_number']);

        if (empty($data['photo_data']) && ! $request->hasFile('photo_file')) {
            throw ValidationException::withMessages(['photo_data' => 'Foto bukti wajib diambil terlebih dahulu.']);
        }

        // Cegah spam: laporan identik (plat + jenis) dari user yang sama dalam 1 jam terakhir
        $duplicate = $request->user()->reports()
            ->where('plate_number', $data['plate_number'])
            ->where('violation_type_id', $data['violation_type_id'])
            ->where('created_at', '>=', now()->subHour())
            ->exists();

        if ($duplicate) {
            throw ValidationException::withMessages([
                'plate_number' => 'Laporan yang sama (plat & jenis pelanggaran) sudah Anda kirim dalam 1 jam terakhir.',
            ]);
        }

        $photoPath = $this->storePhoto($request);

        Report::create([
            'code' => 'CW-'.now()->format('ymd').'-'.strtoupper(Str::random(5)),
            'user_id' => $request->user()->id,
            'violation_type_id' => $data['violation_type_id'],
            'plate_number' => $data['plate_number'],
            'notes' => $data['notes'] ?? null,
            'photo_path' => $photoPath,
            'latitude' => $data['latitude'],
            'longitude' => $data['longitude'],
            'location_name' => $data['location_name'] ?? null,
            'captured_at' => now(),
            'status' => Report::STATUS_PENDING,
        ]);

        return redirect()->route('laporan.index')
            ->with('success', 'Laporan berhasil dikirim dan menunggu verifikasi petugas.');
    }

    /* ---------------------------------------------------------------- UPDATE */

    public function edit(Report $report): View
    {
        Gate::authorize('update', $report);

        return view('warga.reports.edit', [
            'report' => $report->load('type'),
            'types' => ViolationType::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    /** Hanya plat, jenis pelanggaran & catatan yang boleh diubah. Foto & lokasi dikunci sebagai bukti. */
    public function update(Request $request, Report $report): RedirectResponse
    {
        Gate::authorize('update', $report);

        $data = $request->validate([
            'plate_number' => ['required', 'string', 'max:15'],
            'violation_type_id' => ['required', Rule::exists('violation_types', 'id')->where('is_active', true)],
            'notes' => ['nullable', 'string', 'max:150'],
        ]);

        $data['plate_number'] = $this->normalizePlate($data['plate_number']);

        $report->update($data);

        return redirect()->route('laporan.index')->with('success', 'Laporan berhasil diperbarui.');
    }

    /* ---------------------------------------------------------------- DELETE */

    public function destroy(Report $report): RedirectResponse
    {
        Gate::authorize('delete', $report);

        if ($report->photo_path) {
            File::delete(public_path($report->photo_path));
        }

        $report->delete();

        return redirect()->route('laporan.index')->with('success', 'Laporan dibatalkan dan dihapus.');
    }

    /* ---------------------------------------------------------------- helper */

    /** "n1234abc" / "N  1234  ABC" -> "N 1234 ABC". Lempar error validasi jika format tidak valid. */
    private function normalizePlate(string $plate): string
    {
        $plate = strtoupper(trim($plate));

        if (! preg_match(self::PLATE_REGEX, $plate, $m)) {
            throw ValidationException::withMessages([
                'plate_number' => 'Format plat tidak valid. Contoh: N 1234 ABC',
            ]);
        }

        return trim($m[1].' '.$m[2].' '.$m[3]);
    }

    /** Simpan foto (base64 dari kamera, atau file dari mode cadangan) ke public/uploads/reports. */
    private function storePhoto(Request $request): string
    {
        $dir = public_path('uploads/reports');
        File::ensureDirectoryExists($dir);
        $base = now()->format('YmdHis').'-'.Str::random(10);

        if ($request->hasFile('photo_file')) {
            $file = $request->file('photo_file');
            $name = $base.'.'.($file->guessExtension() ?: 'jpg');
            $file->move($dir, $name);

            return 'uploads/reports/'.$name;
        }

        $raw = (string) $request->input('photo_data');

        if (! preg_match('/^data:image\/(jpeg|png|webp);base64,/', $raw, $m)) {
            throw ValidationException::withMessages(['photo_data' => 'Format foto tidak valid.']);
        }

        $binary = base64_decode(substr($raw, strlen($m[0])), true);

        if ($binary === false || strlen($binary) > 6 * 1024 * 1024 || @getimagesizefromstring($binary) === false) {
            throw ValidationException::withMessages(['photo_data' => 'Foto tidak valid atau terlalu besar (maks 6 MB).']);
        }

        $name = $base.'.'.($m[1] === 'jpeg' ? 'jpg' : $m[1]);
        File::put($dir.DIRECTORY_SEPARATOR.$name, $binary);

        return 'uploads/reports/'.$name;
    }
}
