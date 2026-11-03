<?php

namespace Modules\Report\Services;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Customer;
use App\Models\OfferUsage;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\StockMovement;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ReportService
{
    /**
     * @return array{
     *     total_products: int,
     *     total_customers: int,
     *     total_orders: int,
     *     todays_sales: float,
     *     monthly_sales: float,
     *     inventory_value: float,
     *     low_stock_count: int,
     *     best_sellers: Collection,
     *     recent_orders: Collection,
     *     sales_chart: array{categories: list<string>, data: list<float>}
     * }
     */
    public function dashboardWidgets(): array
    {
        $today = Carbon::today();
        $monthStart = Carbon::now()->startOfMonth();

        $salesBase = Order::query()->where('status', OrderStatus::Confirmed);

        $todaysSales = (float) (clone $salesBase)
            ->whereDate('created_at', $today)
            ->sum('total');

        $monthlySales = (float) (clone $salesBase)
            ->where('created_at', '>=', $monthStart)
            ->sum('total');

        $inventoryValue = (float) Product::query()
            ->selectRaw('COALESCE(SUM(stock_quantity * mrp), 0) as value')
            ->value('value');

        $bestSellers = OrderItem::query()
            ->select([
                'order_items.product_id',
                'order_items.product_name',
                DB::raw('SUM(order_items.quantity) as qty_sold'),
                DB::raw('SUM(order_items.line_total) as revenue'),
            ])
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.status', OrderStatus::Confirmed->value)
            ->whereNull('orders.deleted_at')
            ->groupBy('order_items.product_id', 'order_items.product_name')
            ->orderByDesc('qty_sold')
            ->limit(5)
            ->get();

        $recentOrders = Order::query()
            ->with('customer:id,name')
            ->latest('id')
            ->limit(10)
            ->get(['id', 'order_number', 'customer_id', 'status', 'payment_status', 'total', 'created_at']);

        $chartStart = Carbon::today()->subDays(6);
        $dailySales = Order::query()
            ->selectRaw('DATE(created_at) as sale_date')
            ->selectRaw('COALESCE(SUM(total), 0) as total')
            ->where('status', OrderStatus::Confirmed)
            ->where('created_at', '>=', $chartStart->copy()->startOfDay())
            ->groupBy(DB::raw('DATE(created_at)'))
            ->pluck('total', 'sale_date');

        $categories = [];
        $data = [];
        for ($i = 0; $i < 7; $i++) {
            $day = $chartStart->copy()->addDays($i);
            $key = $day->toDateString();
            $categories[] = $day->format('D');
            $data[] = round((float) ($dailySales[$key] ?? 0), 2);
        }

        return [
            'total_products' => (int) Product::query()->count(),
            'total_customers' => (int) Customer::query()->count(),
            'total_orders' => (int) Order::query()->where('status', '!=', OrderStatus::Cancelled)->count(),
            'todays_sales' => round($todaysSales, 2),
            'monthly_sales' => round($monthlySales, 2),
            'inventory_value' => round($inventoryValue, 2),
            'low_stock_count' => (int) Product::query()->whereColumn('stock_quantity', '<=', 'min_stock_level')->count(),
            'best_sellers' => $bestSellers,
            'recent_orders' => $recentOrders,
            'sales_chart' => [
                'categories' => $categories,
                'data' => $data,
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    public function salesReport(array $filters = [], int $perPage = 25): LengthAwarePaginator
    {
        return $this->salesQuery($filters)
            ->with(['customer:id,name', 'payments:id,order_id,method,amount,status'])
            ->latest('orders.id')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array{orders_count: int, subtotal: float, discount: float, tax: float, total: float}
     */
    public function salesSummary(array $filters = []): array
    {
        $row = $this->salesQuery($filters)
            ->select([
                DB::raw('COUNT(DISTINCT orders.id) as orders_count'),
                DB::raw('COALESCE(SUM(orders.subtotal), 0) as subtotal'),
                DB::raw('COALESCE(SUM(orders.discount_amount), 0) as discount'),
                DB::raw('COALESCE(SUM(orders.tax_amount), 0) as tax'),
                DB::raw('COALESCE(SUM(orders.total), 0) as total'),
            ])
            ->first();

        return [
            'orders_count' => (int) ($row->orders_count ?? 0),
            'subtotal' => round((float) ($row->subtotal ?? 0), 2),
            'discount' => round((float) ($row->discount ?? 0), 2),
            'tax' => round((float) ($row->tax ?? 0), 2),
            'total' => round((float) ($row->total ?? 0), 2),
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return \Generator<int, Order>
     */
    public function salesCursor(array $filters = []): \Generator
    {
        foreach ($this->salesQuery($filters)->with(['customer:id,name'])->orderBy('orders.id')->cursor() as $order) {
            yield $order;
        }
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    public function stockMovementReport(array $filters = [], int $perPage = 25): LengthAwarePaginator
    {
        return $this->stockMovementQuery($filters)
            ->with(['product:id,name,upc,sku', 'user:id,name'])
            ->latest('stock_movements.id')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return \Generator<int, StockMovement>
     */
    public function stockMovementCursor(array $filters = []): \Generator
    {
        foreach ($this->stockMovementQuery($filters)->with(['product:id,name,upc,sku', 'user:id,name'])->orderBy('stock_movements.id')->cursor() as $row) {
            yield $row;
        }
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    public function inventoryReport(array $filters = [], int $perPage = 25): LengthAwarePaginator
    {
        return $this->inventoryQuery($filters)
            ->with(['category:id,name', 'brand:id,name'])
            ->orderBy('products.name')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return \Generator<int, Product>
     */
    public function inventoryCursor(array $filters = []): \Generator
    {
        foreach ($this->inventoryQuery($filters)->with(['category:id,name', 'brand:id,name'])->orderBy('products.id')->cursor() as $row) {
            yield $row;
        }
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    public function lowStockReport(array $filters = [], int $perPage = 25): LengthAwarePaginator
    {
        $filters['low_stock'] = true;

        return $this->inventoryReport($filters, $perPage);
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return \Generator<int, Product>
     */
    public function lowStockCursor(array $filters = []): \Generator
    {
        $filters['low_stock'] = true;

        return $this->inventoryCursor($filters);
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    public function productPerformanceReport(array $filters = [], int $perPage = 25): LengthAwarePaginator
    {
        return $this->productPerformanceQuery($filters)
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return \Generator<int, object>
     */
    public function productPerformanceCursor(array $filters = []): \Generator
    {
        foreach ($this->productPerformanceQuery($filters)->orderBy('products.id')->cursor() as $row) {
            yield $row;
        }
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    public function customerPurchaseReport(array $filters = [], int $perPage = 25): LengthAwarePaginator
    {
        return $this->customerPurchaseQuery($filters)
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return \Generator<int, object>
     */
    public function customerPurchaseCursor(array $filters = []): \Generator
    {
        foreach ($this->customerPurchaseQuery($filters)->orderBy('customers.id')->cursor() as $row) {
            yield $row;
        }
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return Collection<int, object>
     */
    public function paymentMethodReport(array $filters = []): Collection
    {
        return $this->paymentMethodQuery($filters)->get();
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    public function offerUsageReport(array $filters = [], int $perPage = 25): LengthAwarePaginator
    {
        return $this->offerUsageQuery($filters)
            ->with(['offer:id,name,coupon_code', 'order:id,order_number', 'customer:id,name'])
            ->latest('offer_usages.id')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return \Generator<int, OfferUsage>
     */
    public function offerUsageCursor(array $filters = []): \Generator
    {
        foreach ($this->offerUsageQuery($filters)->with(['offer:id,name,coupon_code', 'order:id,order_number', 'customer:id,name'])->orderBy('offer_usages.id')->cursor() as $row) {
            yield $row;
        }
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    public function profitabilityReport(array $filters = [], int $perPage = 25): LengthAwarePaginator
    {
        return $this->profitabilityQuery($filters)
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array{qty_sold: float, revenue: float, cost: float, profit: float}
     */
    public function profitabilitySummary(array $filters = []): array
    {
        $row = $this->profitabilityQuery($filters, aggregateOnly: true)->first();

        $revenue = (float) ($row->revenue ?? 0);
        $cost = (float) ($row->cost ?? 0);

        return [
            'qty_sold' => round((float) ($row->qty_sold ?? 0), 3),
            'revenue' => round($revenue, 2),
            'cost' => round($cost, 2),
            'profit' => round($revenue - $cost, 2),
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return \Generator<int, object>
     */
    public function profitabilityCursor(array $filters = []): \Generator
    {
        foreach ($this->profitabilityQuery($filters)->orderBy('products.id')->cursor() as $row) {
            yield $row;
        }
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    protected function salesQuery(array $filters): Builder
    {
        $query = Order::query()->select('orders.*');

        $this->applyDateRange($query, $filters, 'orders.created_at');

        if (! empty($filters['customer_id'])) {
            $query->where('orders.customer_id', $filters['customer_id']);
        }

        if (! empty($filters['order_status'])) {
            $query->where('orders.status', $filters['order_status']);
        } else {
            $query->where('orders.status', '!=', OrderStatus::Cancelled->value);
        }

        if (! empty($filters['payment_method'])) {
            $query->whereExists(function ($sub) use ($filters) {
                $sub->select(DB::raw(1))
                    ->from('payments')
                    ->whereColumn('payments.order_id', 'orders.id')
                    ->where('payments.method', $filters['payment_method'])
                    ->whereIn('payments.status', [
                        PaymentStatus::Completed->value,
                        PaymentStatus::Paid->value,
                        PaymentStatus::Pending->value,
                    ]);
            });
        }

        if ($this->needsProductJoin($filters)) {
            $query->whereExists(function ($sub) use ($filters) {
                $sub->select(DB::raw(1))
                    ->from('order_items')
                    ->join('products', 'products.id', '=', 'order_items.product_id')
                    ->whereColumn('order_items.order_id', 'orders.id')
                    ->whereNull('products.deleted_at');

                if (! empty($filters['product_id'])) {
                    $sub->where('order_items.product_id', $filters['product_id']);
                }
                if (! empty($filters['category_id'])) {
                    $sub->where('products.category_id', $filters['category_id']);
                }
                if (! empty($filters['brand_id'])) {
                    $sub->where('products.brand_id', $filters['brand_id']);
                }
            });
        }

        return $query;
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    protected function stockMovementQuery(array $filters): Builder
    {
        $query = StockMovement::query()->select('stock_movements.*');

        $this->applyDateRange($query, $filters, 'stock_movements.created_at');

        if (! empty($filters['product_id'])) {
            $query->where('stock_movements.product_id', $filters['product_id']);
        }

        if (! empty($filters['movement_type'])) {
            $query->where('stock_movements.type', $filters['movement_type']);
        }

        if (! empty($filters['category_id']) || ! empty($filters['brand_id'])) {
            $query->whereHas('product', function ($product) use ($filters) {
                if (! empty($filters['category_id'])) {
                    $product->where('category_id', $filters['category_id']);
                }
                if (! empty($filters['brand_id'])) {
                    $product->where('brand_id', $filters['brand_id']);
                }
            });
        }

        return $query;
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    protected function inventoryQuery(array $filters): Builder
    {
        $query = Product::query()->select('products.*');

        if (! empty($filters['product_id'])) {
            $query->where('products.id', $filters['product_id']);
        }
        if (! empty($filters['category_id'])) {
            $query->where('products.category_id', $filters['category_id']);
        }
        if (! empty($filters['brand_id'])) {
            $query->where('products.brand_id', $filters['brand_id']);
        }
        if (! empty($filters['low_stock'])) {
            $query->whereColumn('products.stock_quantity', '<=', 'products.min_stock_level');
        }

        return $query;
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    protected function productPerformanceQuery(array $filters): Builder
    {
        $query = Product::query()
            ->select([
                'products.id',
                'products.name',
                'products.upc',
                'products.sku',
                'products.category_id',
                'products.brand_id',
                DB::raw('COALESCE(SUM(order_items.quantity), 0) as qty_sold'),
                DB::raw('COALESCE(SUM(order_items.line_total), 0) as revenue'),
                DB::raw('COUNT(DISTINCT orders.id) as orders_count'),
            ])
            ->leftJoin('order_items', 'order_items.product_id', '=', 'products.id')
            ->leftJoin('orders', function ($join) use ($filters) {
                $join->on('orders.id', '=', 'order_items.order_id')
                    ->whereNull('orders.deleted_at')
                    ->where('orders.status', OrderStatus::Confirmed->value);

                if (! empty($filters['date_from'])) {
                    $join->where('orders.created_at', '>=', Carbon::parse($filters['date_from'])->startOfDay());
                }
                if (! empty($filters['date_to'])) {
                    $join->where('orders.created_at', '<=', Carbon::parse($filters['date_to'])->endOfDay());
                }
                if (! empty($filters['customer_id'])) {
                    $join->where('orders.customer_id', $filters['customer_id']);
                }
            })
            ->with(['category:id,name', 'brand:id,name'])
            ->groupBy('products.id', 'products.name', 'products.upc', 'products.sku', 'products.category_id', 'products.brand_id')
            ->orderByDesc('qty_sold');

        if (! empty($filters['product_id'])) {
            $query->where('products.id', $filters['product_id']);
        }
        if (! empty($filters['category_id'])) {
            $query->where('products.category_id', $filters['category_id']);
        }
        if (! empty($filters['brand_id'])) {
            $query->where('products.brand_id', $filters['brand_id']);
        }

        return $query;
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    protected function customerPurchaseQuery(array $filters): Builder
    {
        $query = Customer::query()
            ->select([
                'customers.id',
                'customers.name',
                'customers.email',
                'customers.phone',
                DB::raw('COUNT(DISTINCT orders.id) as orders_count'),
                DB::raw('COALESCE(SUM(orders.total), 0) as total_spent'),
                DB::raw('MAX(orders.created_at) as last_order_at'),
            ])
            ->leftJoin('orders', function ($join) use ($filters) {
                $join->on('orders.customer_id', '=', 'customers.id')
                    ->whereNull('orders.deleted_at')
                    ->where('orders.status', '!=', OrderStatus::Cancelled->value);

                if (! empty($filters['date_from'])) {
                    $join->where('orders.created_at', '>=', Carbon::parse($filters['date_from'])->startOfDay());
                }
                if (! empty($filters['date_to'])) {
                    $join->where('orders.created_at', '<=', Carbon::parse($filters['date_to'])->endOfDay());
                }
                if (! empty($filters['order_status'])) {
                    $join->where('orders.status', $filters['order_status']);
                }
            })
            ->groupBy('customers.id', 'customers.name', 'customers.email', 'customers.phone')
            ->orderByDesc('total_spent');

        if (! empty($filters['customer_id'])) {
            $query->where('customers.id', $filters['customer_id']);
        }

        return $query;
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    protected function paymentMethodQuery(array $filters): Builder
    {
        $query = Payment::query()
            ->select([
                'payments.method',
                DB::raw('COUNT(*) as payments_count'),
                DB::raw('COALESCE(SUM(payments.amount), 0) as total_amount'),
            ])
            ->join('orders', 'orders.id', '=', 'payments.order_id')
            ->whereNull('orders.deleted_at')
            ->groupBy('payments.method')
            ->orderByDesc('total_amount');

        $this->applyDateRange($query, $filters, 'payments.created_at');

        if (! empty($filters['payment_method'])) {
            $query->where('payments.method', $filters['payment_method']);
        }
        if (! empty($filters['order_status'])) {
            $query->where('orders.status', $filters['order_status']);
        }
        if (! empty($filters['customer_id'])) {
            $query->where('orders.customer_id', $filters['customer_id']);
        }

        return $query;
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    protected function offerUsageQuery(array $filters): Builder
    {
        $query = OfferUsage::query()->select('offer_usages.*');

        $this->applyDateRange($query, $filters, 'offer_usages.created_at');

        if (! empty($filters['customer_id'])) {
            $query->where('offer_usages.customer_id', $filters['customer_id']);
        }

        if (! empty($filters['offer_id'])) {
            $query->where('offer_usages.offer_id', $filters['offer_id']);
        }

        return $query;
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    protected function profitabilityQuery(array $filters, bool $aggregateOnly = false): Builder
    {
        if ($aggregateOnly) {
            $query = OrderItem::query()
                ->selectRaw('COALESCE(SUM(order_items.quantity), 0) as qty_sold')
                ->selectRaw('COALESCE(SUM(order_items.line_total), 0) as revenue')
                ->selectRaw('COALESCE(SUM(order_items.quantity * products.mrp), 0) as cost')
                ->join('orders', 'orders.id', '=', 'order_items.order_id')
                ->join('products', 'products.id', '=', 'order_items.product_id')
                ->where('orders.status', OrderStatus::Confirmed->value)
                ->whereNull('orders.deleted_at')
                ->whereNull('products.deleted_at');
        } else {
            $query = Product::query()
                ->select([
                    'products.id',
                    'products.name',
                    'products.upc',
                    'products.sku',
                    'products.mrp',
                    'products.selling_price',
                    DB::raw('COALESCE(SUM(order_items.quantity), 0) as qty_sold'),
                    DB::raw('COALESCE(SUM(order_items.line_total), 0) as revenue'),
                    DB::raw('COALESCE(SUM(order_items.quantity * products.mrp), 0) as cost'),
                    DB::raw('COALESCE(SUM(order_items.line_total), 0) - COALESCE(SUM(order_items.quantity * products.mrp), 0) as profit'),
                ])
                ->join('order_items', 'order_items.product_id', '=', 'products.id')
                ->join('orders', 'orders.id', '=', 'order_items.order_id')
                ->where('orders.status', OrderStatus::Confirmed->value)
                ->whereNull('orders.deleted_at')
                ->groupBy('products.id', 'products.name', 'products.upc', 'products.sku', 'products.mrp', 'products.selling_price')
                ->orderByDesc('profit');
        }

        if (! empty($filters['date_from'])) {
            $query->where('orders.created_at', '>=', Carbon::parse($filters['date_from'])->startOfDay());
        }
        if (! empty($filters['date_to'])) {
            $query->where('orders.created_at', '<=', Carbon::parse($filters['date_to'])->endOfDay());
        }
        if (! empty($filters['product_id'])) {
            $query->where('products.id', $filters['product_id']);
        }
        if (! empty($filters['category_id'])) {
            $query->where('products.category_id', $filters['category_id']);
        }
        if (! empty($filters['brand_id'])) {
            $query->where('products.brand_id', $filters['brand_id']);
        }
        if (! empty($filters['customer_id'])) {
            $query->where('orders.customer_id', $filters['customer_id']);
        }

        return $query;
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    protected function applyDateRange(Builder $query, array $filters, string $column): void
    {
        if (! empty($filters['date_from'])) {
            $query->where($column, '>=', Carbon::parse($filters['date_from'])->startOfDay());
        }
        if (! empty($filters['date_to'])) {
            $query->where($column, '<=', Carbon::parse($filters['date_to'])->endOfDay());
        }
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    protected function needsProductJoin(array $filters): bool
    {
        return ! empty($filters['product_id'])
            || ! empty($filters['category_id'])
            || ! empty($filters['brand_id']);
    }
}
