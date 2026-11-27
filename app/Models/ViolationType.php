<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ViolationType extends Model
{
    protected $fillable = ['name', 'icon', 'points', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean', 'points' => 'integer'];
    }

    public function reports(): HasMany
    {
        return $this->hasMany(Report::class);
    }
}
