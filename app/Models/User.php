<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    public const ROLE_WARGA = 'warga';
    public const ROLE_PETUGAS = 'petugas';
    public const ROLE_ADMIN = 'admin';

    /** Tingkatan pelapor berdasarkan total poin yang pernah diperoleh. */
    private const TIERS = [
        ['name' => 'Pelapor Baru', 'min' => 0],
        ['name' => 'Warga Teladan', 'min' => 100],
        ['name' => 'Silver Reporter', 'min' => 300],
        ['name' => 'Gold Reporter', 'min' => 700],
    ];

    protected $fillable = [
        'name', 'username', 'email', 'phone', 'nik', 'badge_number', 'unit', 'shift_start', 'shift_end',
        'password', 'role', 'points', 'is_active',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'points' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /** Laporan yang dibuat warga. */
    public function reports(): HasMany
    {
        return $this->hasMany(Report::class);
    }

    /** Laporan yang diputuskan (valid/invalid) oleh petugas ini. */
    public function reviewedReports(): HasMany
    {
        return $this->hasMany(Report::class, 'reviewed_by');
    }

    public function redemptions(): HasMany
    {
        return $this->hasMany(Redemption::class);
    }

    public function isWarga(): bool
    {
        return $this->role === self::ROLE_WARGA;
    }

    public function isPetugas(): bool
    {
        return $this->role === self::ROLE_PETUGAS;
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    /** Nama route dashboard sesuai role. */
    public function homeRoute(): string
    {
        return match ($this->role) {
            self::ROLE_PETUGAS => 'petugas.dashboard',
            self::ROLE_ADMIN => 'admin.dashboard',
            default => 'beranda',
        };
    }

    public function roleLabel(): string
    {
        return match ($this->role) {
            self::ROLE_PETUGAS => 'Petugas Verifikator',
            self::ROLE_ADMIN => 'Super Admin',
            default => 'Citizen Reporter',
        };
    }

    /** Jadwal tugas, mis. "08.00 - 16.00"; null jika belum diisi. */
    public function shiftLabel(): ?string
    {
        return ($this->shift_start && $this->shift_end)
            ? str_replace(':', '.', $this->shift_start).' - '.str_replace(':', '.', $this->shift_end)
            : null;
    }

    public function firstName(): string
    {
        return explode(' ', trim($this->name))[0];
    }

    /** Total poin yang pernah diperoleh dari laporan disetujui (tidak berkurang saat menukar). */
    public function totalEarnedPoints(): int
    {
        return (int) $this->reports()->where('status', Report::STATUS_APPROVED)->sum('points_awarded');
    }

    /** Persentase laporan valid dari laporan yang sudah diputuskan; null jika belum ada. */
    public function reportAccuracy(): ?int
    {
        $counts = $this->reports()
            ->whereIn('status', [Report::STATUS_APPROVED, Report::STATUS_REJECTED])
            ->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');

        $total = (int) $counts->sum();

        return $total > 0 ? (int) round(($counts[Report::STATUS_APPROVED] ?? 0) / $total * 100) : null;
    }

    /** @return array{earned:int,current:array,next:?array,percent:int} */
    public function levelInfo(): array
    {
        $earned = $this->totalEarnedPoints();
        $current = self::TIERS[0];
        $next = null;

        foreach (self::TIERS as $i => $tier) {
            if ($earned >= $tier['min']) {
                $current = $tier;
                $next = self::TIERS[$i + 1] ?? null;
            }
        }

        $percent = $next ? min(100, (int) floor($earned / $next['min'] * 100)) : 100;

        return compact('earned', 'current', 'next', 'percent');
    }
}
