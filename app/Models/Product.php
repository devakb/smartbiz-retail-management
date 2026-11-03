<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'upc',
        'sku',
        'name',
        'description',
        'category_id',
        'brand_id',
        'tax_class_id',
        'mrp',
        'selling_price',
        'min_stock_level',
        'unit',
        'image',
        'stock_quantity',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'mrp' => 'decimal:2',
            'selling_price' => 'decimal:2',
            'min_stock_level' => 'decimal:3',
            'stock_quantity' => 'decimal:3',
            'is_active' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function taxClass(): BelongsTo
    {
        return $this->belongsTo(TaxClass::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function offers(): BelongsToMany
    {
        return $this->belongsToMany(Offer::class, 'offer_products')->withTimestamps();
    }

    public function taxRate(): float
    {
        return (float) ($this->taxClass?->rate ?? 0);
    }
}
