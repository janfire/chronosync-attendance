<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubscriptionPlan extends Model
{
    protected $fillable = [
        'name', 'slug', 'price_usd', 'max_employees', 'trial_days', 'features', 'is_active',
    ];

    protected $casts = [
        'features'      => 'array',
        'is_active'     => 'boolean',
        'price_usd'     => 'decimal:2',
        'max_employees' => 'integer',
    ];

    public function getFormattedPrice(): string
    {
        return 'USD $' . number_format($this->price_usd, 2);
    }

    public function isUnlimited(): bool
    {
        return $this->max_employees === 0;
    }
}
