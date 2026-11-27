<?php

namespace Database\Seeders;

use App\Models\Report;
use App\Models\RewardItem;
use App\Models\User;
use App\Models\ViolationType;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedUsers();
        $this->seedViolationTypes();
        $this->seedRewards();
        $this->seedDemoReports();
        $this->seedExtraDemo();
    }

    /** 3 akun siap pakai (ubah password setelah deploy sungguhan!). */
    private function seedUsers(): void
    {
        $accounts = [
            ['username' => 'warga', 'name' => 'Ahmad', 'email' => 'warga@civicwatch.test', 'phone' => '081234567890',
                'password' => 'warga123', 'role' => User::ROLE_WARGA, 'points' => 150],
            ['username' => 'petugas', 'name' => 'Aiptu Budi Hartono', 'email' => 'petugas@civicwatch.test', 'phone' => '081200000001',
                'password' => 'petugas123', 'role' => User::ROLE_PETUGAS, 'points' => 0,
                'badge_number' => '82040517', 'unit' => 'Satlantas Metro Jaya', 'shift_start' => '08:00', 'shift_end' => '16:00'],
            ['username' => 'admin', 'name' => 'Super Admin', 'email' => 'admin@civicwatch.test', 'phone' => null,
                'password' => 'admin123', 'role' => User::ROLE_ADMIN, 'points' => 0],
        ];

        foreach ($accounts as $account) {
            User::updateOrCreate(['username' => $account['username']], $account + ['is_active' => true]);
        }
    }

    private function seedViolationTypes(): void
    {
        $types = [
            ['name' => 'Parkir di trotoar', 'icon' => 'directions_walk', 'points' => 50],
            ['name' => 'Parkir di bahu jalan', 'icon' => 'local_parking', 'points' => 50],
            ['name' => 'Menerobos lampu merah', 'icon' => 'traffic', 'points' => 100],
            ['name' => 'Melawan arus', 'icon' => 'u_turn_left', 'points' => 100],
            ['name' => 'Tidak memakai helm', 'icon' => 'sports_motorsports', 'points' => 50],
            ['name' => 'Menggunakan ponsel saat berkendara', 'icon' => 'smartphone', 'points' => 75],
            ['name' => 'Menerobos jalur busway', 'icon' => 'directions_bus', 'points' => 75],
        ];

        foreach ($types as $type) {
            // Jangan timpa poin yang mungkin sudah diubah admin
            $existing = ViolationType::where('name', $type['name'])->first();
            if ($existing) {
                continue;
            }
            ViolationType::create($type);
        }
    }

    private function seedRewards(): void
    {
        $rewards = [
            ['name' => 'E-Wallet Rp25.000', 'category' => 'Digital Saldo', 'icon' => 'account_balance_wallet', 'points_cost' => 100, 'stock' => 40, 'badge' => null,
                'description' => 'Saldo digital instan (GoPay, OVO, atau DANA) untuk transportasi & kebutuhan harian.'],
            ['name' => 'Voucher Pajak', 'category' => 'Resmi Pemerintah', 'icon' => 'confirmation_number', 'points_cost' => 150, 'stock' => 25, 'badge' => 'Nilai Tertinggi (SAMSAT)',
                'description' => 'Potongan pajak kendaraan bermotor Rp50.000 resmi SAMSAT induk dan gerai drive-thru wilayah.'],
            ['name' => 'E-Wallet Rp75.000', 'category' => 'Digital Saldo', 'icon' => 'account_balance_wallet', 'points_cost' => 300, 'stock' => 15, 'badge' => null,
                'description' => 'Kumpulkan poin melalui pelaporan terverifikasi untuk hadiah bernilai lebih besar.'],
        ];

        foreach ($rewards as $reward) {
            if (RewardItem::where('name', $reward['name'])->exists()) {
                continue;
            }
            RewardItem::create($reward);
        }
    }

    /** Data contoh untuk akun warga, sama dengan yang ada di desain UI. */
    private function seedDemoReports(): void
    {
        $user = User::where('username', 'warga')->first();
        $officer = User::where('username', 'petugas')->first();
        if (! $user || $user->reports()->exists()) {
            return;
        }

        $type = fn (string $name) => ViolationType::where('name', $name)->value('id');

        $rows = [
            ['plate_number' => 'N 1234 ABC', 'type' => 'Parkir di trotoar', 'location_name' => 'Jl. Ijen No. 42, Malang',
                'lat' => -7.9645, 'lng' => 112.6197, 'at' => '2026-11-05 09:12', 'status' => 'pending'],
            ['plate_number' => 'N 5678 DEF', 'type' => 'Menerobos lampu merah', 'location_name' => 'Simpang Kaliurang, Malang',
                'lat' => -7.9553, 'lng' => 112.6149, 'at' => '2026-11-04 16:30', 'status' => 'approved', 'points' => 100, 'reviewed' => '2026-11-04 17:40'],
            ['plate_number' => 'N 9012 GHI', 'type' => 'Melawan arus', 'location_name' => 'Jl. Basuki Rahmat, Malang',
                'lat' => -7.9772, 'lng' => 112.6351, 'at' => '2026-11-03 08:45', 'status' => 'rejected', 'reason' => 'Foto buram', 'reviewed' => '2026-11-03 10:05'],
            ['plate_number' => 'N 3456 JKL', 'type' => 'Parkir di bahu jalan', 'location_name' => 'Jl. Soekarno Hatta, Malang',
                'lat' => -7.9391, 'lng' => 112.6171, 'at' => '2026-11-01 11:20', 'status' => 'approved', 'points' => 50, 'reviewed' => '2026-11-01 14:00'],
        ];

        foreach ($rows as $i => $row) {
            Report::create([
                'code' => 'CW-261101-DEMO'.($i + 1),
                'user_id' => $user->id,
                'violation_type_id' => $type($row['type']),
                'plate_number' => $row['plate_number'],
                'location_name' => $row['location_name'],
                'latitude' => $row['lat'],
                'longitude' => $row['lng'],
                'captured_at' => $row['at'],
                'status' => $row['status'],
                'points_awarded' => $row['points'] ?? 0,
                'reject_reason' => $row['reason'] ?? null,
                'reviewed_by' => isset($row['reviewed']) ? $officer?->id : null,
                'reviewed_at' => $row['reviewed'] ?? null,
            ]);
        }
    }

    /** Warga & laporan contoh tambahan supaya antrean petugas dan grafik admin tidak kosong. */
    private function seedExtraDemo(): void
    {
        if (Report::where('code', 'like', 'CW-DEMO-X%')->exists()) {
            return;
        }

        $officer = User::where('username', 'petugas')->first();
        $types = ViolationType::all();
        if (! $officer || $types->isEmpty()) {
            return;
        }

        $citizens = [
            User::updateOrCreate(['username' => 'siti'], [
                'name' => 'Siti Rahma', 'email' => 'siti@civicwatch.test', 'password' => 'warga123',
                'role' => User::ROLE_WARGA, 'nik' => '3173014508920006', 'is_active' => true, 'points' => 0,
            ]),
            User::updateOrCreate(['username' => 'andi'], [
                'name' => 'Andi Pratama', 'email' => 'andi@civicwatch.test', 'password' => 'warga123',
                'role' => User::ROLE_WARGA, 'nik' => '3174051207950003', 'is_active' => true, 'points' => 0,
            ]),
        ];
        $citizens[0]->forceFill(['created_at' => '2026-08-12 10:00:00'])->save();
        $citizens[1]->forceFill(['created_at' => '2026-08-28 10:00:00'])->save();

        $places = ['Jl. Jenderal Sudirman', 'Jl. Soekarno Hatta', 'Jl. Basuki Rahmat', 'Jl. Ijen', 'Jl. Veteran', 'Jl. Gatot Subroto', 'Jl. Kawi', 'Jl. Dieng'];
        $reasons = ['Foto buram', 'Plat tidak terbaca', 'Bukti tidak cukup', 'Laporan duplikat'];
        $pattern = ['approved', 'approved', 'pending', 'approved', 'rejected', 'approved', 'pending', 'approved'];
        $letters = 'ABCDEFGHJKLMNPRSTUVWXYZ';

        mt_srand(42); // acak tapi selalu sama setiap seed

        for ($i = 0; $i < 40; $i++) {
            $status = $pattern[$i % count($pattern)];
            $type = $types[mt_rand(0, $types->count() - 1)];
            $citizen = $citizens[$i % 2];
            $at = now()->subDays(mt_rand(0, 13))->setTime(mt_rand(6, 20), mt_rand(0, 59), 0);
            if ($at->isFuture()) {
                $at = now()->subMinutes(30);
            }
            $reviewedAt = $status === 'pending' ? null : $at->copy()->addMinutes(mt_rand(5, 120));
            if ($reviewedAt && $reviewedAt->isFuture()) {
                $reviewedAt = now();
            }

            Report::create([
                'code' => 'CW-DEMO-X'.str_pad((string) $i, 3, '0', STR_PAD_LEFT),
                'user_id' => $citizen->id,
                'violation_type_id' => $type->id,
                'plate_number' => 'N '.mt_rand(1000, 9999).' '.$letters[mt_rand(0, 22)].$letters[mt_rand(0, 22)].$letters[mt_rand(0, 22)],
                'location_name' => $places[mt_rand(0, count($places) - 1)].', Malang',
                'latitude' => -7.9666 + mt_rand(-300, 300) / 10000,
                'longitude' => 112.6326 + mt_rand(-300, 300) / 10000,
                'captured_at' => $at,
                'status' => $status,
                'points_awarded' => $status === 'approved' ? $type->points : 0,
                'reject_reason' => $status === 'rejected' ? $reasons[mt_rand(0, 3)] : null,
                'reviewed_by' => $status === 'pending' ? null : $officer->id,
                'reviewed_at' => $reviewedAt,
            ]);
        }

        foreach ($citizens as $citizen) {
            $citizen->update(['points' => $citizen->totalEarnedPoints()]);
        }
    }
}
