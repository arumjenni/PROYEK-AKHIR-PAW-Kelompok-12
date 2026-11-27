<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RewardItem extends Model
{
    protected $fillable = ['name', 'description', 'category', 'icon', 'badge', 'points_cost', 'stock', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean', 'points_cost' => 'integer', 'stock' => 'integer'];
    }

    public function redemptions(): HasMany
    {
        return $this->hasMany(Redemption::class);
    }
}
