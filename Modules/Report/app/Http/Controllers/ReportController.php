<?php

namespace Modules\Report\Http\Controllers;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\StockMovementType;
use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Offer;
use App\Models\Product;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Maatwebsite\Excel\Excel as ExcelWriter;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Report\Exports\ReportRowsExport;
use Modules\Report\Services\ReportService;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function __construct(private readonly ReportService $reportService) {}

    public function index(): View
    {
        return view('report::index', [
            'title' => 'Reports',
            'reports' => $this->reportCatalog(),
        ]);
    }

    public function sales(Request $request): View
    {
        $filters = $this->filters($request);

        return view('report::sales', [
            'title' => 'Sales Report',
            'filters' => $filters,
            'rows' => $this->reportService->salesReport($filters),
            'summary' => $this->reportService->salesSummary($filters),
            'filterOptions' => $this->filterOptions(),
            'showFilters' => ['date', 'customer', 'product', 'category', 'brand', 'payment_method', 'order_status'],
            'exportBase' => 'report.sales',
        ]);
    }

    public function stockMovements(Request $request): View
    {
        $filters = $this->filters($request);

        return view('report::stock-movements', [
            'title' => 'Stock Movement Report',
            'filters' => $filters,
            'rows' => $this->reportService->stockMovementReport($filters),
            'filterOptions' => $this->filterOptions(),
            'movementTypes' => StockMovementType::cases(),
            'showFilters' => ['date', 'product', 'category', 'brand', 'movement_type'],
            'exportBase' => 'report.stock-movements',
        ]);
    }

    public function inventory(Request $request): View
    {
        $filters = $this->filters($request);

        return view('report::inventory', [
            'title' => 'Inventory Report',
            'filters' => $filters,
            'rows' => $this->reportService->inventoryReport($filters),
            'filterOptions' => $this->filterOptions(),
            'showFilters' => ['product', 'category', 'brand'],
            'exportBase' => 'report.inventory',
        ]);
    }

    public function lowStock(Request $request): View
    {
        $filters = $this->filters($request);

        return view('report::low-stock', [
            'title' => 'Low Stock Report',
            'filters' => $filters,
            'rows' => $this->reportService->lowStockReport($filters),
            'filterOptions' => $this->filterOptions(),
            'showFilters' => ['product', 'category', 'brand'],
            'exportBase' => 'report.low-stock',
        ]);
    }

    public function productPerformance(Request $request): View
    {
        $filters = $this->filters($request);

        return view('report::product-performance', [
            'title' => 'Product Performance Report',
            'filters' => $filters,
            'rows' => $this->reportService->productPerformanceReport($filters),
            'filterOptions' => $this->filterOptions(),
            'showFilters' => ['date', 'product', 'category', 'brand', 'customer'],
            'exportBase' => 'report.product-performance',
        ]);
    }

    public function customerPurchases(Request $request): View
    {
        $filters = $this->filters($request);

        return view('report::customer-purchases', [
            'title' => 'Customer Purchase Report',
            'filters' => $filters,
            'rows' => $this->reportService->customerPurchaseReport($filters),
            'filterOptions' => $this->filterOptions(),
            'showFilters' => ['date', 'customer', 'order_status'],
            'exportBase' => 'report.customer-purchases',
        ]);
    }

    public function paymentMethods(Request $request): View
    {
        $filters = $this->filters($request);

        return view('report::payment-methods', [
            'title' => 'Payment Method Report',
            'filters' => $filters,
            'rows' => $this->reportService->paymentMethodReport($filters),
            'filterOptions' => $this->filterOptions(),
            'showFilters' => ['date', 'customer', 'payment_method', 'order_status'],
            'exportBase' => 'report.payment-methods',
        ]);
    }

    public function offerUsage(Request $request): View
    {
        $filters = $this->filters($request);

        return view('report::offer-usage', [
            'title' => 'Offer Usage Report',
            'filters' => $filters,
            'rows' => $this->reportService->offerUsageReport($filters),
            'filterOptions' => $this->filterOptions(),
            'offers' => Offer::query()->orderBy('name')->get(['id', 'name', 'coupon_code']),
            'showFilters' => ['date', 'customer', 'offer'],
            'exportBase' => 'report.offer-usage',
        ]);
    }

    public function profitability(Request $request): View
    {
        $filters = $this->filters($request);

        return view('report::profitability', [
            'title' => 'Profitability Report',
            'filters' => $filters,
            'rows' => $this->reportService->profitabilityReport($filters),
            'summary' => $this->reportService->profitabilitySummary($filters),
            'filterOptions' => $this->filterOptions(),
            'showFilters' => ['date', 'product', 'category', 'brand', 'customer'],
            'exportBase' => 'report.profitability',
        ]);
    }

    public function exportSales(Request $request, string $format): BinaryFileResponse|StreamedResponse|Response
    {
        $filters = $this->filters($request);

        if ($format === 'pdf') {
            $rows = $this->reportService->salesReport($filters, 500);
            $summary = $this->reportService->salesSummary($filters);
            $pdf = Pdf::loadView('report::pdf.sales', [
                'rows' => $rows,
                'summary' => $summary,
                'filters' => $filters,
            ]);

            return $pdf->download('sales-report.pdf');
        }

        $headings = ['Order #', 'Invoice #', 'Customer', 'Date', 'Status', 'Payment', 'Subtotal', 'Discount', 'Tax', 'Total'];
        $generator = function () use ($filters) {
            foreach ($this->reportService->salesCursor($filters) as $order) {
                yield [
                    $order->order_number,
                    $order->invoice_number,
                    $order->customer?->name,
                    optional($order->created_at)?->format('Y-m-d H:i'),
                    $order->status->value,
                    $order->payment_status->value,
                    $order->subtotal,
                    $order->discount_amount,
                    $order->tax_amount,
                    $order->total,
                ];
            }
        };

        return $this->export($format, 'sales-report', $headings, $generator);
    }

    public function exportStockMovements(Request $request, string $format): BinaryFileResponse|StreamedResponse
    {
        $filters = $this->filters($request);
        $headings = ['Date', 'Product', 'UPC', 'Type', 'Qty', 'Before', 'After', 'Reason', 'User'];
        $generator = function () use ($filters) {
            foreach ($this->reportService->stockMovementCursor($filters) as $row) {
                yield [
                    optional($row->created_at)?->format('Y-m-d H:i'),
                    $row->product?->name,
                    $row->product?->upc,
                    $row->type->value,
                    $row->quantity,
                    $row->quantity_before,
                    $row->quantity_after,
                    $row->reason,
                    $row->user?->name,
                ];
            }
        };

        return $this->export($format, 'stock-movements', $headings, $generator);
    }

    public function exportInventory(Request $request, string $format): BinaryFileResponse|StreamedResponse
    {
        $filters = $this->filters($request);
        $headings = ['Product', 'UPC', 'SKU', 'Category', 'Brand', 'Stock', 'Min', 'MRP', 'Selling', 'Value'];
        $generator = function () use ($filters) {
            foreach ($this->reportService->inventoryCursor($filters) as $row) {
                yield [
                    $row->name,
                    $row->upc,
                    $row->sku,
                    $row->category?->name,
                    $row->brand?->name,
                    $row->stock_quantity,
                    $row->min_stock_level,
                    $row->mrp,
                    $row->selling_price,
                    round((float) $row->stock_quantity * (float) $row->mrp, 2),
                ];
            }
        };

        return $this->export($format, 'inventory-report', $headings, $generator);
    }

    public function exportLowStock(Request $request, string $format): BinaryFileResponse|StreamedResponse
    {
        $filters = $this->filters($request);
        $headings = ['Product', 'UPC', 'SKU', 'Category', 'Brand', 'Stock', 'Min', 'Deficit'];
        $generator = function () use ($filters) {
            foreach ($this->reportService->lowStockCursor($filters) as $row) {
                yield [
                    $row->name,
                    $row->upc,
                    $row->sku,
                    $row->category?->name,
                    $row->brand?->name,
                    $row->stock_quantity,
                    $row->min_stock_level,
                    round((float) $row->min_stock_level - (float) $row->stock_quantity, 3),
                ];
            }
        };

        return $this->export($format, 'low-stock-report', $headings, $generator);
    }

    public function exportProductPerformance(Request $request, string $format): BinaryFileResponse|StreamedResponse
    {
        $filters = $this->filters($request);
        $headings = ['Product', 'UPC', 'SKU', 'Category', 'Brand', 'Qty Sold', 'Orders', 'Revenue'];
        $generator = function () use ($filters) {
            foreach ($this->reportService->productPerformanceCursor($filters) as $row) {
                yield [
                    $row->name,
                    $row->upc,
                    $row->sku,
                    $row->category?->name,
                    $row->brand?->name,
                    $row->qty_sold,
                    $row->orders_count,
                    $row->revenue,
                ];
            }
        };

        return $this->export($format, 'product-performance', $headings, $generator);
    }

    public function exportCustomerPurchases(Request $request, string $format): BinaryFileResponse|StreamedResponse
    {
        $filters = $this->filters($request);
        $headings = ['Customer', 'Email', 'Phone', 'Orders', 'Total Spent', 'Last Order'];
        $generator = function () use ($filters) {
            foreach ($this->reportService->customerPurchaseCursor($filters) as $row) {
                yield [
                    $row->name,
                    $row->email,
                    $row->phone,
                    $row->orders_count,
                    $row->total_spent,
                    $row->last_order_at,
                ];
            }
        };

        return $this->export($format, 'customer-purchases', $headings, $generator);
    }

    public function exportPaymentMethods(Request $request, string $format): BinaryFileResponse|StreamedResponse
    {
        $filters = $this->filters($request);
        $headings = ['Method', 'Payments', 'Total Amount'];
        $rows = $this->reportService->paymentMethodReport($filters);

        return $this->export($format, 'payment-methods', $headings, function () use ($rows) {
            foreach ($rows as $row) {
                yield [
                    is_object($row->method) ? $row->method->value : $row->method,
                    $row->payments_count,
                    $row->total_amount,
                ];
            }
        });
    }

    public function exportOfferUsage(Request $request, string $format): BinaryFileResponse|StreamedResponse
    {
        $filters = $this->filters($request);
        $headings = ['Date', 'Offer', 'Coupon', 'Order', 'Customer', 'Discount'];
        $generator = function () use ($filters) {
            foreach ($this->reportService->offerUsageCursor($filters) as $row) {
                yield [
                    optional($row->created_at)?->format('Y-m-d H:i'),
                    $row->offer?->name,
                    $row->offer?->coupon_code,
                    $row->order?->order_number,
                    $row->customer?->name,
                    $row->discount_amount,
                ];
            }
        };

        return $this->export($format, 'offer-usage', $headings, $generator);
    }

    public function exportProfitability(Request $request, string $format): BinaryFileResponse|StreamedResponse
    {
        $filters = $this->filters($request);
        $headings = ['Product', 'UPC', 'SKU', 'Qty Sold', 'Revenue', 'Cost', 'Profit'];
        $generator = function () use ($filters) {
            foreach ($this->reportService->profitabilityCursor($filters) as $row) {
                yield [
                    $row->name,
                    $row->upc,
                    $row->sku,
                    $row->qty_sold,
                    $row->revenue,
                    $row->cost,
                    $row->profit,
                ];
            }
        };

        return $this->export($format, 'profitability', $headings, $generator);
    }

    /**
     * @param  callable(): \Generator<int, list<mixed>>  $generator
     * @param  list<string>  $headings
     */
    protected function export(string $format, string $basename, array $headings, callable $generator): BinaryFileResponse|StreamedResponse
    {
        $format = Str::lower($format);

        if ($format === 'csv') {
            return $this->streamCsv($basename.'.csv', $headings, $generator);
        }

        $writerType = $format === 'xlsx' ? ExcelWriter::XLSX : ExcelWriter::CSV;

        return Excel::download(
            new ReportRowsExport($headings, $generator),
            $basename.'.'.($format === 'xlsx' ? 'xlsx' : 'csv'),
            $writerType
        );
    }

    /**
     * @param  list<string>  $headings
     * @param  callable(): \Generator<int, list<mixed>>  $generator
     */
    protected function streamCsv(string $filename, array $headings, callable $generator): StreamedResponse
    {
        return response()->streamDownload(function () use ($headings, $generator) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $headings);
            foreach ($generator() as $row) {
                fputcsv($handle, $row);
            }
            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function filters(Request $request): array
    {
        return [
            'date_from' => $request->string('date_from')->toString() ?: null,
            'date_to' => $request->string('date_to')->toString() ?: null,
            'product_id' => $request->integer('product_id') ?: null,
            'category_id' => $request->integer('category_id') ?: null,
            'brand_id' => $request->integer('brand_id') ?: null,
            'customer_id' => $request->integer('customer_id') ?: null,
            'payment_method' => $request->string('payment_method')->toString() ?: null,
            'order_status' => $request->string('order_status')->toString() ?: null,
            'movement_type' => $request->string('movement_type')->toString() ?: null,
            'offer_id' => $request->integer('offer_id') ?: null,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function filterOptions(): array
    {
        return [
            'products' => Product::query()->orderBy('name')->get(['id', 'name', 'upc']),
            'categories' => Category::query()->orderBy('name')->get(['id', 'name']),
            'brands' => Brand::query()->orderBy('name')->get(['id', 'name']),
            'customers' => Customer::query()->orderBy('name')->get(['id', 'name']),
            'paymentMethods' => PaymentMethod::cases(),
            'orderStatuses' => OrderStatus::cases(),
        ];
    }

    /**
     * @return list<array{key: string, name: string, route: string, description: string}>
     */
    protected function reportCatalog(): array
    {
        return [
            ['key' => 'sales', 'name' => 'Sales Report', 'route' => 'report.sales', 'description' => 'Orders, totals, and payment context by date and filters.'],
            ['key' => 'stock-movements', 'name' => 'Stock Movement Report', 'route' => 'report.stock-movements', 'description' => 'In/out/adjustment/sale ledger with product filters.'],
            ['key' => 'inventory', 'name' => 'Inventory Report', 'route' => 'report.inventory', 'description' => 'Current stock levels and inventory value by product.'],
            ['key' => 'low-stock', 'name' => 'Low Stock Report', 'route' => 'report.low-stock', 'description' => 'Products at or below minimum stock level.'],
            ['key' => 'product-performance', 'name' => 'Product Performance Report', 'route' => 'report.product-performance', 'description' => 'Quantity sold and revenue by product.'],
            ['key' => 'customer-purchases', 'name' => 'Customer Purchase Report', 'route' => 'report.customer-purchases', 'description' => 'Orders and spend by customer.'],
            ['key' => 'payment-methods', 'name' => 'Payment Method Report', 'route' => 'report.payment-methods', 'description' => 'Cash, card, and UPI totals.'],
            ['key' => 'offer-usage', 'name' => 'Offer Usage Report', 'route' => 'report.offer-usage', 'description' => 'Coupon and offer redemptions.'],
            ['key' => 'profitability', 'name' => 'Profitability Report', 'route' => 'report.profitability', 'description' => 'Selling revenue minus product cost on sold items.'],
        ];
    }
}
