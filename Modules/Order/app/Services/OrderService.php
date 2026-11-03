<?php

namespace Modules\Order\Services;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\StockMovementType;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Modules\Inventory\Services\StockMovementService;

class OrderService
{
    public function __construct(private readonly StockMovementService $stockMovementService) {}

    /**
     * @param  array{
     *     status?: string|null,
     *     payment_status?: string|null,
     *     customer_id?: int|null,
     *     date_from?: string|null,
     *     date_to?: string|null,
     *     search?: string|null
     * }  $filters
     */
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return Order::query()
            ->with(['customer:id,name', 'user:id,name'])
            ->when(! empty($filters['status']), fn ($q) => $q->where('status', $filters['status']))
            ->when(! empty($filters['payment_status']), fn ($q) => $q->where('payment_status', $filters['payment_status']))
            ->when(! empty($filters['customer_id']), fn ($q) => $q->where('customer_id', $filters['customer_id']))
            ->when(! empty($filters['date_from']), fn ($q) => $q->whereDate('created_at', '>=', $filters['date_from']))
            ->when(! empty($filters['date_to']), fn ($q) => $q->whereDate('created_at', '<=', $filters['date_to']))
            ->when(! empty($filters['search']), function ($q) use ($filters) {
                $search = $filters['search'];
                $q->where(function ($inner) use ($search) {
                    $inner->where('order_number', 'like', "%{$search}%")
                        ->orWhere('invoice_number', 'like', "%{$search}%");
                });
            })
            ->latest('id')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * @param  array{
     *     customer_id: int,
     *     status?: string,
     *     notes?: string|null,
     *     discount_amount?: float|int|string,
     *     items: list<array{product_id: int, quantity: float|int|string, discount_amount?: float|int|string}>,
     *     payment?: array{method: string, amount: float|int|string, amount_received?: float|int|string|null, reference?: string|null}|null
     * }  $data
     */
    public function create(array $data, User $user): Order
    {
        return DB::transaction(function () use ($data, $user) {
            $customer = Customer::query()->whereKey($data['customer_id'])->lockForUpdate()->firstOrFail();

            if (! $customer->is_active) {
                throw ValidationException::withMessages([
                    'customer_id' => 'Selected customer is inactive.',
                ]);
            }

            $status = OrderStatus::tryFrom((string) ($data['status'] ?? OrderStatus::Confirmed->value))
                ?? OrderStatus::Confirmed;

            if ($status === OrderStatus::Cancelled) {
                throw ValidationException::withMessages([
                    'status' => 'Cannot create an order as cancelled.',
                ]);
            }

            $built = $this->buildItemsFromProducts($data['items'] ?? []);
            $orderDiscount = round((float) ($data['discount_amount'] ?? 0), 2);

            if ($orderDiscount < 0) {
                throw ValidationException::withMessages([
                    'discount_amount' => 'Discount cannot be negative.',
                ]);
            }

            $subtotal = $built['subtotal'];
            $taxAmount = $built['tax_amount'];
            $total = round(max(0, $subtotal - $orderDiscount + $taxAmount), 2);

            $order = Order::query()->create([
                'order_number' => $this->generateOrderNumber(),
                'invoice_number' => $status === OrderStatus::Confirmed ? $this->generateInvoiceNumber() : null,
                'customer_id' => $customer->id,
                'user_id' => $user->id,
                'offer_id' => null,
                'status' => $status,
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

            if ($status === OrderStatus::Confirmed) {
                $this->deductStockOnce($order, $user);
            }

            if (! empty($data['payment'])) {
                $this->recordPayment($order, $data['payment'], $user);
            } else {
                $this->refreshPaymentStatus($order);
            }

            return $order->fresh(['customer', 'items', 'payments', 'user']);
        });
    }

    public function transitionStatus(Order $order, OrderStatus $newStatus, User $user): Order
    {
        return DB::transaction(function () use ($order, $newStatus, $user) {
            /** @var Order $locked */
            $locked = Order::query()->whereKey($order->id)->lockForUpdate()->firstOrFail();

            if ($locked->status === $newStatus) {
                return $locked->fresh(['customer', 'items', 'payments', 'user']);
            }

            if ($locked->status === OrderStatus::Cancelled) {
                throw ValidationException::withMessages([
                    'status' => 'Cancelled orders cannot change status.',
                ]);
            }

            if ($newStatus === OrderStatus::Cancelled) {
                return $this->cancel($locked, $user);
            }

            if ($locked->status === OrderStatus::Draft && $newStatus === OrderStatus::Confirmed) {
                if (! $locked->invoice_number) {
                    $locked->invoice_number = $this->generateInvoiceNumber();
                }
                $locked->status = OrderStatus::Confirmed;
                $locked->save();
                $this->deductStockOnce($locked, $user);

                return $locked->fresh(['customer', 'items', 'payments', 'user']);
            }

            throw ValidationException::withMessages([
                'status' => "Cannot transition from {$locked->status->value} to {$newStatus->value}.",
            ]);
        });
    }

    public function cancel(Order $order, User $user): Order
    {
        return DB::transaction(function () use ($order, $user) {
            /** @var Order $locked */
            $locked = Order::query()->whereKey($order->id)->lockForUpdate()->firstOrFail();

            if ($locked->status === OrderStatus::Cancelled) {
                return $locked->fresh(['customer', 'items', 'payments', 'user']);
            }

            if ($this->hasStockDeducted($locked)) {
                $this->reverseStock($locked, $user);
            }

            $locked->status = OrderStatus::Cancelled;
            $locked->save();

            return $locked->fresh(['customer', 'items', 'payments', 'user']);
        });
    }

    /**
     * @param  array{method: string, amount: float|int|string, amount_received?: float|int|string|null, reference?: string|null, status?: string|null}  $data
     */
    public function recordPayment(Order $order, array $data, ?User $user = null): Payment
    {
        return DB::transaction(function () use ($order, $data) {
            /** @var Order $locked */
            $locked = Order::query()->whereKey($order->id)->lockForUpdate()->firstOrFail();

            if ($locked->status === OrderStatus::Cancelled) {
                throw ValidationException::withMessages([
                    'order' => 'Cannot record payment on a cancelled order.',
                ]);
            }

            $method = PaymentMethod::tryFrom((string) $data['method'])
                ?? throw ValidationException::withMessages(['method' => 'Invalid payment method.']);

            $amount = round((float) $data['amount'], 2);
            if ($amount <= 0) {
                throw ValidationException::withMessages([
                    'amount' => 'Payment amount must be greater than zero.',
                ]);
            }

            $amountReceived = isset($data['amount_received']) && $data['amount_received'] !== null && $data['amount_received'] !== ''
                ? round((float) $data['amount_received'], 2)
                : $amount;

            $change = max(0, round($amountReceived - $amount, 2));

            $payment = Payment::query()->create([
                'order_id' => $locked->id,
                'method' => $method,
                'amount' => $amount,
                'amount_received' => $amountReceived,
                'change_amount' => $change,
                'reference' => $data['reference'] ?? null,
                'status' => PaymentStatus::Completed,
                'paid_at' => now(),
            ]);

            $this->refreshPaymentStatus($locked);

            return $payment;
        });
    }

    public function refreshPaymentStatus(Order $order): Order
    {
        $paid = (float) $order->payments()
            ->whereIn('status', [PaymentStatus::Completed->value, PaymentStatus::Paid->value])
            ->sum('amount');

        $total = (float) $order->total;

        $status = match (true) {
            $paid <= 0 => PaymentStatus::Unpaid,
            $paid + 0.00001 < $total => PaymentStatus::Partial,
            default => PaymentStatus::Paid,
        };

        $order->payment_status = $status;
        $order->save();

        return $order;
    }

    public function generateOrderNumber(): string
    {
        return $this->generateUniqueNumber('ORD', 'order_number');
    }

    public function generateInvoiceNumber(): string
    {
        return $this->generateUniqueNumber('INV', 'invoice_number');
    }

    /**
     * @param  list<array{product_id: int, quantity: float|int|string, discount_amount?: float|int|string}>  $items
     * @return array{items: list<array<string, mixed>>, subtotal: float, tax_amount: float}
     */
    public function buildItemsFromProducts(array $items): array
    {
        if ($items === []) {
            throw ValidationException::withMessages([
                'items' => 'At least one item is required.',
            ]);
        }

        $built = [];
        $subtotal = 0.0;
        $taxAmount = 0.0;

        foreach ($items as $index => $row) {
            $productId = (int) ($row['product_id'] ?? 0);
            $qty = round((float) ($row['quantity'] ?? 0), 3);
            $lineDiscount = round((float) ($row['discount_amount'] ?? 0), 2);

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

            if ($lineDiscount < 0) {
                throw ValidationException::withMessages([
                    "items.{$index}.discount_amount" => 'Discount cannot be negative.',
                ]);
            }

            $product = Product::query()
                ->with('taxClass:id,rate')
                ->whereKey($productId)
                ->where('is_active', true)
                ->first();

            if (! $product) {
                throw ValidationException::withMessages([
                    "items.{$index}.product_id" => 'Product not found or inactive.',
                ]);
            }

            $unitPrice = round((float) $product->selling_price, 2);
            $taxPercent = round($product->taxRate(), 2);
            $lineBase = round(($unitPrice * $qty) - $lineDiscount, 2);

            if ($lineBase < 0) {
                throw ValidationException::withMessages([
                    "items.{$index}.discount_amount" => 'Discount exceeds line amount.',
                ]);
            }

            $lineTax = round($lineBase * ($taxPercent / 100), 2);
            $lineTotal = round($lineBase + $lineTax, 2);

            $built[] = [
                'product_id' => $product->id,
                'product_name' => $product->name,
                'upc' => $product->upc,
                'sku' => $product->sku,
                'unit_price' => $unitPrice,
                'tax_percent' => $taxPercent,
                'discount_amount' => $lineDiscount,
                'quantity' => $qty,
                'line_total' => $lineTotal,
            ];

            $subtotal = round($subtotal + $lineBase, 2);
            $taxAmount = round($taxAmount + $lineTax, 2);
        }

        return [
            'items' => $built,
            'subtotal' => $subtotal,
            'tax_amount' => $taxAmount,
        ];
    }

    protected function deductStockOnce(Order $order, User $user): void
    {
        if ($this->hasStockDeducted($order)) {
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
                'Sale for order '.$order->order_number,
                $user,
                $order,
            );
        }
    }

    protected function reverseStock(Order $order, User $user): void
    {
        $order->loadMissing('items');

        foreach ($order->items as $item) {
            if (! $item->product_id) {
                continue;
            }

            $product = Product::query()->whereKey($item->product_id)->first();
            if (! $product) {
                continue;
            }

            $this->stockMovementService->saleReversal(
                $product,
                $item->quantity,
                'Sale reversal for order '.$order->order_number,
                $user,
                $order,
            );
        }
    }

    protected function hasStockDeducted(Order $order): bool
    {
        return StockMovement::query()
            ->where('reference_type', $order->getMorphClass())
            ->where('reference_id', $order->id)
            ->where('type', StockMovementType::Sale)
            ->exists();
    }

    protected function generateUniqueNumber(string $prefix, string $column): string
    {
        $date = now()->format('Ymd');

        do {
            $candidate = sprintf('%s-%s-%04d', $prefix, $date, random_int(1, 9999));
        } while (Order::withTrashed()->where($column, $candidate)->exists());

        return $candidate;
    }
}
