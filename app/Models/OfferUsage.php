<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OfferUsage extends Model
{
    protected $fillable = [
        'offer_id',
        'order_id',
        'customer_id',
        'discount_amount',
    ];

    protected function casts(): array
    {
        return [
            'discount_amount' => 'decimal:2',
        ];
    }

    public function offer(): BelongsTo
    {
        return $this->belongsTo(Offer::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}
