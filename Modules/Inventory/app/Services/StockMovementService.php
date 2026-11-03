<?php

namespace Modules\Inventory\Services;

use App\Enums\StockMovementType;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StockMovementService
{
    /**
     * @param  array{search?: string|null, low_stock?: bool}  $filters
     */
    public function paginateProducts(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $search = $filters['search'] ?? null;
        $lowStock = (bool) ($filters['low_stock'] ?? false);

        return Product::query()
            ->with(['category:id,name', 'brand:id,name'])
            ->when($search, function ($query) use ($search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('name', 'like', "%{$search}%")
                        ->orWhere('upc', 'like', "%{$search}%")
                        ->orWhere('sku', 'like', "%{$search}%");
                });
            })
            ->when($lowStock, function ($query) {
                $query->whereColumn('stock_quantity', '<=', 'min_stock_level');
            })
            ->orderBy('name')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function adjust(Product $product, float|string $newQuantity, string $reason, ?User $user): StockMovement
    {
        $reason = trim($reason);
        if ($reason === '') {
            throw ValidationException::withMessages([
                'reason' => 'A reason is required for stock adjustments.',
            ]);
        }

        $target = round((float) $newQuantity, 3);
        if ($target < 0) {
            throw ValidationException::withMessages([
                'stock_quantity' => 'Stock quantity cannot be negative.',
            ]);
        }

        return DB::transaction(function () use ($product, $target, $reason, $user) {
            /** @var Product $locked */
            $locked = Product::query()->whereKey($product->id)->lockForUpdate()->firstOrFail();

            $before = round((float) $locked->stock_quantity, 3);
            $after = $target;
            $delta = round($after - $before, 3);

            $locked->stock_quantity = $after;
            $locked->save();

            return StockMovement::query()->create([
                'product_id' => $locked->id,
                'type' => StockMovementType::Adjustment,
                'quantity' => $delta,
                'quantity_before' => $before,
                'quantity_after' => $after,
                'reason' => $reason,
                'user_id' => $user?->id,
            ]);
        });
    }

    public function sale(
        Product $product,
        float|string $quantity,
        ?string $reason,
        ?User $user,
        ?Model $reference = null,
    ): StockMovement {
        $qty = $this->positiveQuantity($quantity, 'quantity');

        return $this->apply($product, StockMovementType::Sale, $qty, $reason, $user, $reference);
    }

    public function saleReversal(
        Product $product,
        float|string $quantity,
        ?string $reason,
        ?User $user,
        ?Model $reference = null,
    ): StockMovement {
        $qty = $this->positiveQuantity($quantity, 'quantity');

        return $this->apply($product, StockMovementType::SaleReversal, $qty, $reason, $user, $reference);
    }

    protected function apply(
        Product $product,
        StockMovementType $type,
        float $quantity,
        ?string $reason,
        ?User $user,
        ?Model $reference = null,
    ): StockMovement {
        return DB::transaction(function () use ($product, $type, $quantity, $reason, $user, $reference) {
            /** @var Product $locked */
            $locked = Product::query()->whereKey($product->id)->lockForUpdate()->firstOrFail();

            $before = round((float) $locked->stock_quantity, 3);

            $after = match ($type) {
                StockMovementType::In, StockMovementType::SaleReversal => round($before + $quantity, 3),
                StockMovementType::Out, StockMovementType::Sale => round($before - $quantity, 3),
                default => $before,
            };

            if ($after < 0) {
                throw ValidationException::withMessages([
                    'quantity' => 'Insufficient stock. Available: '.$before,
                ]);
            }

            $locked->stock_quantity = $after;
            $locked->save();

            return StockMovement::query()->create([
                'product_id' => $locked->id,
                'type' => $type,
                'quantity' => $quantity,
                'quantity_before' => $before,
                'quantity_after' => $after,
                'reason' => $reason,
                'reference_type' => $reference?->getMorphClass(),
                'reference_id' => $reference?->getKey(),
                'user_id' => $user?->id,
            ]);
        });
    }

    protected function positiveQuantity(float|string $quantity, string $field): float
    {
        $qty = round((float) $quantity, 3);

        if ($qty <= 0) {
            throw ValidationException::withMessages([
                $field => 'Quantity must be greater than zero.',
            ]);
        }

        return $qty;
    }
}
