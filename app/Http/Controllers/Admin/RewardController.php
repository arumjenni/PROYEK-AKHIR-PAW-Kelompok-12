<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Redemption;
use App\Models\RewardItem;
use App\Models\User;
use App\Models\ViolationType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class RewardController extends Controller
{
    /** Pilihan ikon untuk katalog (nama Material Symbols). */
    public const ICONS = [
        'account_balance_wallet' => 'Dompet / e-wallet',
        'confirmation_number' => 'Voucher / tiket',
        'smartphone' => 'Pulsa',
        'shopping_bag' => 'Belanja',
        'local_gas_station' => 'BBM',
        'redeem' => 'Hadiah',
    ];

    /* ----------------------------------------------------------- READ */

    public function index(): View
    {
        $monthStart = now()->startOfMonth();

        return view('admin.rewards.index', [
            'rewards' => RewardItem::orderBy('points_cost')->get(),
            'types' => ViolationType::orderBy('name')->get(),
            'claims' => Redemption::where('created_at', '>=', $monthStart)->count(),
            'spent' => (int) Redemption::where('created_at', '>=', $monthStart)->sum('points_spent'),
            'circulating' => (int) User::where('role', User::ROLE_WARGA)->sum('points'),
        ]);
    }

    /* ----------------------------------------------------------- CREATE / UPDATE */

    public function create(): View
    {
        return view('admin.rewards.form', ['reward' => null, 'icons' => self::ICONS]);
    }

    public function store(Request $request): RedirectResponse
    {
        RewardItem::create($this->validated($request, null));

        return redirect()->route('admin.rewards.index')->with('success', 'Reward baru berhasil ditambahkan.');
    }

    public function edit(RewardItem $reward): View
    {
        return view('admin.rewards.form', ['reward' => $reward, 'icons' => self::ICONS]);
    }

    public function update(Request $request, RewardItem $reward): RedirectResponse
    {
        $reward->update($this->validated($request, $reward));

        return redirect()->route('admin.rewards.index')->with('success', 'Reward berhasil diperbarui.');
    }

    /* ----------------------------------------------------------- DELETE */

    public function destroy(RewardItem $reward): RedirectResponse
    {
        $name = $reward->name;
        $reward->delete(); // riwayat penukaran tetap menyimpan nama hadiah

        return redirect()->route('admin.rewards.index')->with('success', "Reward {$name} dihapus.");
    }

    /* ----------------------------------------------------------- pengaturan poin */

    /** Hanya berlaku untuk laporan yang disetujui SETELAH ini. Poin yang sudah diterima warga tidak berubah. */
    public function updatePoints(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'points' => ['required', 'array'],
            'points.*' => ['required', 'integer', 'min:0', 'max:10000'],
        ]);

        DB::transaction(function () use ($data) {
            foreach ($data['points'] as $id => $points) {
                ViolationType::whereKey((int) $id)->update(['points' => (int) $points]);
            }
        });

        return redirect()->route('admin.rewards.index')->with('success', 'Pengaturan poin disimpan. Berlaku untuk laporan baru.');
    }

    /* ----------------------------------------------------------- helper */

    private function validated(Request $request, ?RewardItem $reward): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100', Rule::unique('reward_items', 'name')->ignore($reward?->id)],
            'description' => ['nullable', 'string', 'max:255'],
            'category' => ['required', 'string', 'max:50'],
            'icon' => ['required', Rule::in(array_keys(self::ICONS))],
            'badge' => ['nullable', 'string', 'max:50'],
            'points_cost' => ['required', 'integer', 'min:1', 'max:100000'],
            'stock' => ['required', 'integer', 'min:0', 'max:100000'],
        ]);

        $data['is_active'] = $request->boolean('is_active');
        $data['badge'] = ($data['badge'] ?? '') === '' ? null : $data['badge'];

        return $data;
    }
}
