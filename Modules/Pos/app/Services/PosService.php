<?php

namespace Modules\Pos\Services;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\StockMovementType;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Modules\Inventory\Services\StockMovementService;
use Modules\Offer\Services\OfferService;
use Modules\Order\Services\OrderService;
use Modules\Product\Services\UpcService;

class PosService
{
    private const CHECKOUT_TOKEN_TTL_SECONDS = 600;

    public function __construct(
        private readonly OrderService $orderService,
        private readonly StockMovementService $stockMovementService,
        private readonly OfferService $offerService,
        private readonly UpcService $upcService,
    ) {}

    /**
     * @return array{product: array<string, mixed>}
     */
    public function scan(string $upc): array
    {
        $normalized = $this->upcService->normalize($upc);

        if ($normalized === '') {
            throw ValidationException::withMessages([
                'upc' => 'Scan a UPC to add a product.',
            ]);
        }

        $product = Product::query()
            ->with(['category:id,name', 'brand:id,name', 'taxClass:id,rate'])
            ->where('upc', $normalized)
            ->first();

        if (! $product) {
            throw ValidationException::withMessages([
                'upc' => 'Product not found for this UPC.',
            ]);
        }

        if (! $product->is_active) {
            throw ValidationException::withMessages([
                'upc' => 'This product is inactive and cannot be sold.',
            ]);
        }

        if ((float) $product->stock_quantity <= 0) {
            throw ValidationException::withMessages([
                'upc' => 'This product is out of stock.',
            ]);
        }

        return [
            'product' => $this->transformProduct($product),
        ];
    }

    /**
     * @param  array{search?: string|null, category_id?: int|null, brand_id?: int|null}  $filters
     * @return Collection<int, array<string, mixed>>
     */
    public function searchProducts(array $filters = [], int $limit = 40): Collection
    {
        $search = trim((string) ($filters['search'] ?? ''));

        return Product::query()
            ->with(['category:id,name', 'brand:id,name', 'taxClass:id,rate'])
            ->where('is_active', true)
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('name', 'like', "%{$search}%")
                        ->orWhere('upc', 'like', "%{$search}%")
                        ->orWhere('sku', 'like', "%{$search}%");
                });
            })
            ->when(! empty($filters['category_id']), fn ($q) => $q->where('category_id', $filters['category_id']))
            ->when(! empty($filters['brand_id']), fn ($q) => $q->where('brand_id', $filters['brand_id']))
            ->orderBy('name')
            ->limit($limit)
            ->get()
            ->map(fn (Product $product) => $this->transformProduct($product))
            ->values();
    }

    /**
     * @param  array{
     *     customer_id: int,
     *     checkout_token: string,
     *     items: list<array{product_id: int, quantity: float|int|string, discount_amount?: float|int|string}>,
     *     coupon_code?: string|null,
     *     offer_id?: int|null,
     *     notes?: string|null,
     *     payment: array{
     *         method: string,
     *         amount_received?: float|int|string|null,
     *         reference?: string|null
     *     }
     * }  $data
     * @return array{order: Order, print_url: string}
     */
    public function checkout(array $data, User $user): array
    {
        $token = trim((string) ($data['checkout_token'] ?? ''));
        if ($token === '') {
            throw ValidationException::withMessages([
                'checkout_token' => 'Checkout token is required.',
            ]);
        }

        $cacheKey = $this->checkoutTokenKey($token);

        if (Cache::has($cacheKey)) {
            throw ValidationException::withMessages([
                'checkout_token' => 'This checkout has already been processed.',
            ]);
        }

        // Claim the token up-front so concurrent duplicate submits are rejected.
        if (! Cache::add($cacheKey, 'processing', self::CHECKOUT_TOKEN_TTL_SECONDS)) {
            throw ValidationException::withMessages([
                'checkout_token' => 'This checkout has already been processed.',
            ]);
        }

        try {
            $result = DB::transaction(function () use ($data, $user) {
                $customer = Customer::query()
                    ->whereKey($data['customer_id'])
                    ->lockForUpdate()
                    ->firstOrFail();

                if (! $customer->is_active) {
                    throw ValidationException::withMessages([
                        'customer_id' => 'Selected customer is inactive.',
                    ]);
                }

                $this->assertStockAvailable($data['items'] ?? []);

                $built = $this->orderService->buildItemsFromProducts($data['items'] ?? []);
                $subtotal = $built['subtotal'];
                $taxAmount = $built['tax_amount'];

                $offerId = null;
                $orderDiscount = 0.0;
                $offer = null;

                $hasOffer = ! empty($data['offer_id'])
                    || filled($data['coupon_code'] ?? null)
                    || filled($data['coupon'] ?? null);

                if ($hasOffer) {
                    $offerItems = [];
                    foreach ($built['items'] as $item) {
                        $product = Product::query()->whereKey($item['product_id'])->first();
                        $lineBase = round(((float) $item['unit_price'] * (float) $item['quantity']) - (float) $item['discount_amount'], 2);
                        $offerItems[] = [
                            'product_id' => $item['product_id'],
                            'category_id' => $product?->category_id,
                            'line_subtotal' => $lineBase,
                        ];
                    }

                    $validatedOffer = $this->offerService->validateAndCalculate([
                        'customer_id' => $customer->id,
                        'subtotal' => $subtotal,
                        'items' => $offerItems,
                        'offer_id' => $data['offer_id'] ?? null,
                        'coupon_code' => $data['coupon_code'] ?? $data['coupon'] ?? null,
                    ]);

                    $offerId = $validatedOffer['offer_id'];
                    $orderDiscount = $validatedOffer['discount_amount'];
                    $offer = $validatedOffer['offer'];
                }

                $total = round(max(0, $subtotal - $orderDiscount + $taxAmount), 2);

                $paymentPayload = $this->normalizePayment($data['payment'] ?? [], $total);

                $order = Order::query()->create([
                    'order_number' => $this->orderService->generateOrderNumber(),
                    'invoice_number' => $this->orderService->generateInvoiceNumber(),
                    'customer_id' => $customer->id,
                    'user_id' => $user->id,
                    'offer_id' => $offerId,
                    'status' => OrderStatus::Confirmed,
                    'payment_status' => PaymentStatus::Unpaid,
                    'subtotal' => $subtotal,
                    'discount_amount' => $orderDiscount,
                    'tax_amount' => $taxAmount,
                    'total' => $total,
                    'notes' => $data['notes'] ?? null,
                ]);

                foreach ($built['items'] as $item) {
                    $order->items()->create($item);
                }

                $this->orderService->recordPayment($order, $paymentPayload, $user);
                $this->deductStockOnce($order, $user);

                if ($offer !== null) {
                    $this->offerService->recordUsage($offer, $order, $orderDiscount, $customer->id);
                }

                $order = $order->fresh(['customer', 'items', 'payments', 'user', 'offer']);

                return [
                    'order' => $order,
                    'print_url' => route('order.invoice', $order),
                ];
            });
        } catch (\Throwable $e) {
            Cache::forget($cacheKey);
            throw $e;
        }

        Cache::put($cacheKey, $result['order']->id, self::CHECKOUT_TOKEN_TTL_SECONDS);

        return $result;
    }

    /**
     * @param  list<array{product_id: int, quantity: float|int|string}>  $items
     */
    protected function assertStockAvailable(array $items): void
    {
        if ($items === []) {
            throw ValidationException::withMessages([
                'items' => 'At least one item is required.',
            ]);
        }

        $qtyByProduct = [];
        foreach ($items as $index => $row) {
            $productId = (int) ($row['product_id'] ?? 0);
            $qty = round((float) ($row['quantity'] ?? 0), 3);

            if ($productId <= 0) {
                throw ValidationException::withMessages([
                    "items.{$index}.product_id" => 'Product is required.',
                ]);
            }

            if ($qty <= 0) {
                throw ValidationException::withMessages([
                    "items.{$index}.quantity" => 'Quantity must be greater than zero.',
                ]);
            }

            $qtyByProduct[$productId] = round(($qtyByProduct[$productId] ?? 0) + $qty, 3);
        }

        $ids = collect(array_keys($qtyByProduct))->sort()->values();

        foreach ($ids as $productId) {
            /** @var Product|null $product */
            $product = Product::query()->whereKey($productId)->lockForUpdate()->first();

            if (! $product) {
                throw ValidationException::withMessages([
                    'items' => "Product #{$productId} was not found.",
                ]);
            }

            if (! $product->is_active) {
                throw ValidationException::withMessages([
                    'items' => "{$product->name} is inactive and cannot be sold.",
                ]);
            }

            $needed = $qtyByProduct[$productId];
            $available = round((float) $product->stock_quantity, 3);

            if ($available < $needed) {
                throw ValidationException::withMessages([
                    'items' => "Insufficient stock for {$product->name}. Available: {$available}",
                ]);
            }
        }
    }

    /**
     * @param  array{method?: string, amount_received?: float|int|string|null, reference?: string|null, amount?: float|int|string|null}  $payment
     * @return array{method: string, amount: float, amount_received: float, reference: string|null}
     */
    protected function normalizePayment(array $payment, float $total): array
    {
        $method = PaymentMethod::tryFrom((string) ($payment['method'] ?? ''))
            ?? throw ValidationException::withMessages(['payment.method' => 'Invalid payment method.']);

        if ($total <= 0) {
            throw ValidationException::withMessages([
                'payment' => 'Order total must be greater than zero.',
            ]);
        }

        $reference = isset($payment['reference']) ? trim((string) $payment['reference']) : '';
        $reference = $reference === '' ? null : $reference;

        if ($method === PaymentMethod::Cash) {
            if (! array_key_exists('amount_received', $payment) || $payment['amount_received'] === null || $payment['amount_received'] === '') {
                throw ValidationException::withMessages([
                    'payment.amount_received' => 'Amount received is required for cash payments.',
                ]);
            }

            $amountReceived = round((float) $payment['amount_received'], 2);
            if ($amountReceived < $total) {
                throw ValidationException::withMessages([
                    'payment.amount_received' => 'Amount received must cover the order total.',
                ]);
            }

            return [
                'method' => $method->value,
                'amount' => $total,
                'amount_received' => $amountReceived,
                'reference' => $reference,
            ];
        }

        if ($reference === null) {
            throw ValidationException::withMessages([
                'payment.reference' => 'Payment reference is required for card and UPI.',
            ]);
        }

        return [
            'method' => $method->value,
            'amount' => $total,
            'amount_received' => $total,
            'reference' => $reference,
        ];
    }

    protected function deductStockOnce(Order $order, User $user): void
    {
        $alreadyDeducted = StockMovement::query()
            ->where('reference_type', $order->getMorphClass())
            ->where('reference_id', $order->id)
            ->where('type', StockMovementType::Sale)
            ->exists();

        if ($alreadyDeducted) {
            return;
        }

        $order->loadMissing('items');

        foreach ($order->items as $item) {
            if (! $item->product_id) {
                continue;
            }

            $product = Product::query()->whereKey($item->product_id)->first();
            if (! $product) {
                continue;
            }

            $this->stockMovementService->sale(
                $product,
                $item->quantity,
                'POS sale for order '.$order->order_number,
                $user,
                $order,
            );
        }
    }

    protected function checkoutTokenKey(string $token): string
    {
        return 'pos:checkout:'.$token;
    }

    /**
     * @return array<string, mixed>
     */
    protected function transformProduct(Product $product): array
    {
        return [
            'id' => $product->id,
            'name' => $product->name,
            'upc' => $product->upc,
            'sku' => $product->sku,
            'selling_price' => (float) $product->selling_price,
            'tax_percent' => $product->taxRate(),
            'stock_quantity' => (float) $product->stock_quantity,
            'category_id' => $product->category_id,
            'brand_id' => $product->brand_id,
            'category' => $product->category?->name,
            'brand' => $product->brand?->name,
            'is_active' => (bool) $product->is_active,
            'unit' => $product->unit,
        ];
    }
}
