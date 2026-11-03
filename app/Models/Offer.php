<?php

namespace App\Models;

use App\Enums\OfferScope;
use App\Enums\OfferType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Offer extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'type',
        'value',
        'coupon_code',
        'min_purchase',
        'max_discount',
        'usage_limit',
        'per_customer_limit',
        'starts_at',
        'ends_at',
        'scope',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'type' => OfferType::class,
            'scope' => OfferScope::class,
            'value' => 'decimal:2',
            'min_purchase' => 'decimal:2',
            'max_discount' => 'decimal:2',
            'usage_limit' => 'integer',
            'per_customer_limit' => 'integer',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'offer_products')->withTimestamps();
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'offer_categories')->withTimestamps();
    }

    public function usages(): HasMany
    {
        return $this->hasMany(OfferUsage::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
