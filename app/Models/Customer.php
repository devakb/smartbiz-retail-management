<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'billing_address',
        'shipping_address',
        'is_walk_in',
        'is_active',
        'outstanding_balance',
    ];

    protected function casts(): array
    {
        return [
            'is_walk_in' => 'boolean',
            'is_active' => 'boolean',
            'outstanding_balance' => 'decimal:2',
        ];
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function offerUsages(): HasMany
    {
        return $this->hasMany(OfferUsage::class);
    }
}
