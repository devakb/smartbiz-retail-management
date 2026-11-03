<?php

namespace Modules\Offer\Services;

use App\Enums\OfferScope;
use App\Enums\OfferType;
use App\Models\Offer;
use App\Models\OfferUsage;
use App\Models\Order;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OfferService
{
    public function paginate(?string $search = null, int $perPage = 15): LengthAwarePaginator
    {
        return Offer::query()
            ->withCount(['products', 'categories', 'usages'])
            ->when($search, function ($query) use ($search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('name', 'like', "%{$search}%")
                        ->orWhere('coupon_code', 'like', "%{$search}%");
                });
            })
            ->latest('id')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Offer
    {
        return DB::transaction(function () use ($data) {
            $offer = Offer::query()->create($this->payloadFrom($data));
            $this->syncRelations($offer, $data);

            return $offer->load(['products:id,name', 'categories:id,name']);
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Offer $offer, array $data): Offer
    {
        return DB::transaction(function () use ($offer, $data) {
            $offer->fill($this->payloadFrom($data, $offer))->save();
            $this->syncRelations($offer, $data);

            return $offer->refresh()->load(['products:id,name', 'categories:id,name']);
        });
    }

    public function delete(Offer $offer): void
    {
        $offer->delete();
    }

    public function activate(Offer $offer): Offer
    {
        $offer->forceFill(['is_active' => true])->save();

        return $offer->refresh();
    }

    public function deactivate(Offer $offer): Offer
    {
        $offer->forceFill(['is_active' => false])->save();

        return $offer->refresh();
    }

    /**
     * Validate an offer against an order context and calculate the discount.
     *
     * @param  array{
     *     customer_id?: int|null,
     *     subtotal: float|int|string,
     *     items?: list<array{product_id?: int|null, category_id?: int|null, line_subtotal: float|int|string}>,
     *     coupon?: string|null,
     *     code?: string|null,
     *     coupon_code?: string|null,
     *     offer_id?: int|null
     * }  $context
     * @return array{offer_id: int, discount_amount: float, offer: Offer}
     */
    public function validateAndCalculate(array $context): array
    {
        $offer = $this->resolveOffer($context);
        $now = Carbon::now();
        $subtotal = round((float) ($context['subtotal'] ?? 0), 2);
        $customerId = isset($context['customer_id']) && $context['customer_id'] !== ''
            ? (int) $context['customer_id']
            : null;
        /** @var list<array{product_id?: int|null, category_id?: int|null, line_subtotal: float|int|string}> $items */
        $items = $context['items'] ?? [];

        if (! $offer->is_active) {
            throw ValidationException::withMessages([
                'offer' => 'This offer is inactive.',
            ]);
        }

        if ($offer->starts_at && $now->lt($offer->starts_at)) {
            throw ValidationException::withMessages([
                'offer' => 'This offer has not started yet.',
            ]);
        }

        if ($offer->ends_at && $now->gt($offer->ends_at)) {
            throw ValidationException::withMessages([
                'offer' => 'This offer has expired.',
            ]);
        }

        if ($offer->min_purchase !== null && $subtotal < (float) $offer->min_purchase) {
            throw ValidationException::withMessages([
                'offer' => 'Order does not meet the minimum purchase amount for this offer.',
            ]);
        }

        if ($offer->usage_limit !== null) {
            $used = $offer->usages()->count();
            if ($used >= (int) $offer->usage_limit) {
                throw ValidationException::withMessages([
                    'offer' => 'This offer has reached its usage limit.',
                ]);
            }
        }

        if ($offer->per_customer_limit !== null && $customerId !== null) {
            $customerUsed = $offer->usages()->where('customer_id', $customerId)->count();
            if ($customerUsed >= (int) $offer->per_customer_limit) {
                throw ValidationException::withMessages([
                    'offer' => 'This customer has reached the usage limit for this offer.',
                ]);
            }
        }

        $eligibleSubtotal = $this->eligibleSubtotal($offer, $subtotal, $items);

        if ($eligibleSubtotal <= 0) {
            throw ValidationException::withMessages([
                'offer' => 'No eligible items match this offer.',
            ]);
        }

        $discount = $this->computeDiscount($offer, $eligibleSubtotal);

        if ($discount <= 0) {
            throw ValidationException::withMessages([
                'offer' => 'This offer does not produce a discount for the current order.',
            ]);
        }

        return [
            'offer_id' => $offer->id,
            'discount_amount' => $discount,
            'offer' => $offer,
        ];
    }

    public function recordUsage(
        Offer $offer,
        Order $order,
        float|string $discountAmount,
        ?int $customerId = null,
    ): OfferUsage {
        return OfferUsage::query()->create([
            'offer_id' => $offer->id,
            'order_id' => $order->id,
            'customer_id' => $customerId ?? $order->customer_id,
            'discount_amount' => round((float) $discountAmount, 2),
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function payloadFrom(array $data, ?Offer $offer = null): array
    {
        return [
            'name' => $data['name'],
            'coupon_code' => $this->nullableString($data['coupon_code'] ?? null),
            'type' => $data['type'],
            'value' => $data['value'],
            'min_purchase' => $data['min_purchase'] ?? null,
            'max_discount' => $data['max_discount'] ?? null,
            'usage_limit' => $data['usage_limit'] ?? null,
            'per_customer_limit' => $data['per_customer_limit'] ?? null,
            'starts_at' => $data['starts_at'] ?? null,
            'ends_at' => $data['ends_at'] ?? null,
            'scope' => $data['scope'] ?? ($offer?->scope?->value ?? OfferScope::Order->value),
            'is_active' => $data['is_active'] ?? ($offer?->is_active ?? true),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function syncRelations(Offer $offer, array $data): void
    {
        $scope = OfferScope::from($data['scope'] ?? $offer->scope->value);

        if ($scope === OfferScope::Product) {
            $offer->products()->sync($data['product_ids'] ?? []);
            $offer->categories()->sync([]);
        } elseif ($scope === OfferScope::Category) {
            $offer->categories()->sync($data['category_ids'] ?? []);
            $offer->products()->sync([]);
        } else {
            $offer->products()->sync([]);
            $offer->categories()->sync([]);
        }
    }

    /**
     * @param  array{
     *     coupon?: string|null,
     *     code?: string|null,
     *     coupon_code?: string|null,
     *     offer_id?: int|null
     * }  $context
     */
    protected function resolveOffer(array $context): Offer
    {
        if (! empty($context['offer_id'])) {
            $offer = Offer::query()->find((int) $context['offer_id']);
            if (! $offer) {
                throw ValidationException::withMessages([
                    'offer' => 'Offer not found.',
                ]);
            }

            return $offer;
        }

        $code = $this->nullableString(
            $context['coupon_code']
                ?? $context['coupon']
                ?? $context['code']
                ?? null
        );

        if ($code === null) {
            throw ValidationException::withMessages([
                'offer' => 'Provide an offer id or coupon code.',
            ]);
        }

        $offer = Offer::query()
            ->whereRaw('LOWER(coupon_code) = ?', [mb_strtolower($code)])
            ->first();

        if (! $offer) {
            throw ValidationException::withMessages([
                'offer' => 'Invalid coupon code.',
            ]);
        }

        return $offer;
    }

    /**
     * @param  list<array{product_id?: int|null, category_id?: int|null, line_subtotal: float|int|string}>  $items
     */
    protected function eligibleSubtotal(Offer $offer, float $subtotal, array $items): float
    {
        if ($offer->scope === OfferScope::Order) {
            return $subtotal;
        }

        if ($offer->scope === OfferScope::Product) {
            $productIds = array_map(
                'intval',
                $offer->relationLoaded('products')
                    ? $offer->products->modelKeys()
                    : $offer->products()->pluck('products.id')->all()
            );

            return $this->sumMatchingItems($items, function (array $item) use ($productIds) {
                $productId = isset($item['product_id']) ? (int) $item['product_id'] : null;

                return $productId !== null && in_array($productId, $productIds, true);
            });
        }

        $categoryIds = array_map(
            'intval',
            $offer->relationLoaded('categories')
                ? $offer->categories->modelKeys()
                : $offer->categories()->pluck('categories.id')->all()
        );

        return $this->sumMatchingItems($items, function (array $item) use ($categoryIds) {
            $categoryId = isset($item['category_id']) ? (int) $item['category_id'] : null;

            return $categoryId !== null && in_array($categoryId, $categoryIds, true);
        });
    }

    /**
     * @param  list<array{product_id?: int|null, category_id?: int|null, line_subtotal: float|int|string}>  $items
     * @param  callable(array{product_id?: int|null, category_id?: int|null, line_subtotal: float|int|string}): bool  $matcher
     */
    protected function sumMatchingItems(array $items, callable $matcher): float
    {
        $total = 0.0;

        foreach ($items as $item) {
            if ($matcher($item)) {
                $total += (float) ($item['line_subtotal'] ?? 0);
            }
        }

        return round($total, 2);
    }

    protected function computeDiscount(Offer $offer, float $eligibleSubtotal): float
    {
        $discount = match ($offer->type) {
            OfferType::Percentage => round($eligibleSubtotal * ((float) $offer->value / 100), 2),
            OfferType::Fixed => round(min((float) $offer->value, $eligibleSubtotal), 2),
        };

        if ($offer->max_discount !== null) {
            $discount = min($discount, (float) $offer->max_discount);
        }

        return round(min($discount, $eligibleSubtotal), 2);
    }

    protected function nullableString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $trimmed = trim((string) $value);

        return $trimmed === '' ? null : $trimmed;
    }
}
