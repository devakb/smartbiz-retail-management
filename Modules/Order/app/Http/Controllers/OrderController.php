<?php

namespace Modules\Order\Http\Controllers;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Support\ApiResponse;
use App\Support\AuditLogger;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Modules\Order\Http\Requests\StoreOrderRequest;
use Modules\Order\Http\Requests\StorePaymentRequest;
use Modules\Order\Http\Requests\UpdateOrderStatusRequest;
use Modules\Order\Services\OrderService;

class OrderController extends Controller
{
    public function __construct(private readonly OrderService $orderService) {}

    public function index(Request $request): View
    {
        $filters = [
            'status' => $request->string('status')->toString() ?: null,
            'payment_status' => $request->string('payment_status')->toString() ?: null,
            'customer_id' => $request->input('customer_id') ?: null,
            'date_from' => $request->string('date_from')->toString() ?: null,
            'date_to' => $request->string('date_to')->toString() ?: null,
            'search' => $request->string('search')->toString() ?: null,
        ];

        return view('order::index', [
            'title' => 'Orders',
            'orders' => $this->orderService->paginate($filters),
            'filters' => $filters,
            'customers' => Customer::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'statuses' => OrderStatus::cases(),
            'paymentStatuses' => [
                PaymentStatus::Unpaid,
                PaymentStatus::Partial,
                PaymentStatus::Paid,
            ],
            'stats' => [
                'total' => Order::query()->count(),
                'confirmed' => Order::query()->where('status', OrderStatus::Confirmed)->count(),
                'draft' => Order::query()->where('status', OrderStatus::Draft)->count(),
                'cancelled' => Order::query()->where('status', OrderStatus::Cancelled)->count(),
            ],
            'hasFilters' => collect($filters)->filter(fn ($value) => filled($value))->isNotEmpty(),
        ]);
    }

    public function create(): View
    {
        return view('order::create', [
            'title' => 'Create order',
            'customers' => Customer::query()->where('is_active', true)->orderByDesc('is_walk_in')->orderBy('name')->get(['id', 'name', 'is_walk_in']),
            'products' => Product::query()
                ->with('taxClass:id,rate')
                ->where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name', 'upc', 'sku', 'selling_price', 'tax_class_id', 'stock_quantity'])
                ->map(fn (Product $product) => [
                    'id' => $product->id,
                    'name' => $product->name,
                    'upc' => $product->upc,
                    'sku' => $product->sku,
                    'selling_price' => (float) $product->selling_price,
                    'tax_percent' => $product->taxRate(),
                    'stock_quantity' => (float) $product->stock_quantity,
                ])
                ->values(),
            'statuses' => [OrderStatus::Draft, OrderStatus::Confirmed],
        ]);
    }

    public function store(StoreOrderRequest $request): JsonResponse
    {
        $order = $this->orderService->create($request->orderPayload(), $request->user());

        return ApiResponse::success('Order created successfully.', [
            'order' => $this->transform($order),
            'redirect' => route('order.show', $order),
        ], 201);
    }

    public function show(Order $order): View
    {
        $order->load(['customer', 'user:id,name', 'items.product:id,name', 'payments']);

        return view('order::show', [
            'title' => 'Order '.$order->order_number,
            'order' => $order,
            'statuses' => OrderStatus::cases(),
        ]);
    }

    public function updateStatus(UpdateOrderStatusRequest $request, Order $order): JsonResponse
    {
        $status = OrderStatus::from($request->validated('status'));
        $order = $this->orderService->transitionStatus($order, $status, $request->user());

        return ApiResponse::success('Order status updated.', [
            'order' => $this->transform($order),
            'redirect' => route('order.show', $order),
        ]);
    }

    public function cancel(Request $request, Order $order): JsonResponse
    {
        abort_unless($request->user()?->can('orders.cancel') || $request->user()?->can('orders.update'), 403);

        $order = $this->orderService->cancel($order, $request->user());

        AuditLogger::log('order.cancelled', $order, [
            'order_number' => $order->order_number,
            'status' => $order->status->value,
        ]);

        return ApiResponse::success('Order cancelled successfully.', [
            'order' => $this->transform($order),
            'redirect' => route('order.show', $order),
        ]);
    }

    public function storePayment(StorePaymentRequest $request, Order $order): JsonResponse
    {
        $payment = $this->orderService->recordPayment($order, $request->validated(), $request->user());
        $order = $order->fresh(['customer', 'items', 'payments', 'user']);

        return ApiResponse::success('Payment recorded.', [
            'payment' => [
                'id' => $payment->id,
                'amount' => $payment->amount,
                'method' => $payment->method->value,
            ],
            'order' => $this->transform($order),
            'redirect' => route('order.show', $order),
        ], 201);
    }

    public function invoice(Order $order): View
    {
        $order->load(['customer', 'user:id,name', 'items', 'payments']);

        return view('order::invoice', [
            'title' => 'Invoice '.($order->invoice_number ?: $order->order_number),
            'order' => $order,
        ]);
    }

    public function invoicePdf(Order $order): Response
    {
        $order->load(['customer', 'user:id,name', 'items', 'payments']);

        $pdf = Pdf::loadView('order::invoice-pdf', [
            'order' => $order,
        ]);

        $filename = ($order->invoice_number ?: $order->order_number).'.pdf';

        return $pdf->download($filename);
    }

    /**
     * @return array<string, mixed>
     */
    protected function transform(Order $order): array
    {
        $order->loadMissing(['customer:id,name', 'items', 'payments']);

        return [
            'id' => $order->id,
            'order_number' => $order->order_number,
            'invoice_number' => $order->invoice_number,
            'customer_id' => $order->customer_id,
            'customer' => $order->customer?->name,
            'status' => $order->status->value,
            'payment_status' => $order->payment_status->value,
            'subtotal' => $order->subtotal,
            'discount_amount' => $order->discount_amount,
            'tax_amount' => $order->tax_amount,
            'total' => $order->total,
            'items' => $order->items->map(fn ($item) => [
                'product_id' => $item->product_id,
                'product_name' => $item->product_name,
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_price,
                'line_total' => $item->line_total,
            ])->values(),
        ];
    }
}
