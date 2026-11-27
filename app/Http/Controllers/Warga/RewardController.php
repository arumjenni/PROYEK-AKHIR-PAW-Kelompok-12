<?php

namespace App\Http\Controllers\Warga;

use App\Http\Controllers\Controller;
use App\Models\Redemption;
use App\Models\RewardItem;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class RewardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        return view('warga.rewards.index', [
            'rewards' => RewardItem::where('is_active', true)->orderBy('points_cost')->get(),
            'level' => $user->levelInfo(),
        ]);
    }

    /** Tukar poin dengan hadiah (poin dipotong di dalam transaksi database). */
    public function redeem(Request $request, RewardItem $reward): RedirectResponse
    {
        abort_unless($reward->is_active, 404);

        try {
            $redemption = DB::transaction(function () use ($request, $reward) {
                $user = User::whereKey($request->user()->id)->lockForUpdate()->firstOrFail();
                $item = RewardItem::whereKey($reward->id)->lockForUpdate()->firstOrFail();

                if (! $item->is_active || $item->stock < 1) {
                    throw new \RuntimeException('Maaf, stok hadiah ini sudah habis.');
                }

                if ($user->points < $item->points_cost) {
                    throw new \RuntimeException('Poin Anda belum cukup untuk hadiah ini.');
                }

                $user->decrement('points', $item->points_cost);
                $item->decrement('stock');

                return Redemption::create([
                    'code' => 'RW-'.strtoupper(Str::random(8)),
                    'user_id' => $user->id,
                    'reward_item_id' => $reward->id,
                    'reward_name' => $reward->name,
                    'points_spent' => $reward->points_cost,
                    'status' => 'diproses',
                ]);
            });
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('hadiah.index')
            ->with('success', "Penukaran {$reward->name} berhasil! Kode: {$redemption->code}. Instruksi aktivasi dikirim dalam 1×24 jam kerja.");
    }

    public function history(Request $request): View
    {
        return view('warga.rewards.history', [
            'redemptions' => $request->user()->redemptions()->latest()->paginate(10),
        ]);
    }
}
