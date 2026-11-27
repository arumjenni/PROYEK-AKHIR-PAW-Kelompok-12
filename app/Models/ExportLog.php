<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExportLog extends Model
{
    protected $fillable = ['user_id', 'date_from', 'date_to', 'status_filter', 'format', 'total_rows'];

    protected function casts(): array
    {
        return ['date_from' => 'date', 'date_to' => 'date'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function statusLabel(): string
    {
        return match ($this->status_filter) {
            'pending' => 'Pending saja',
            'approved' => 'Valid saja',
            'rejected' => 'Invalid saja',
            default => 'Semua status',
        };
    }
}
