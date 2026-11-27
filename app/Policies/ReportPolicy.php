<?php

namespace App\Policies;

use App\Models\Report;
use App\Models\User;

class ReportPolicy
{
    /** Warga hanya boleh mengubah laporannya sendiri yang masih menunggu. */
    public function update(User $user, Report $report): bool
    {
        return $report->user_id === $user->id && $report->isPending();
    }

    public function delete(User $user, Report $report): bool
    {
        return $this->update($user, $report);
    }
}
