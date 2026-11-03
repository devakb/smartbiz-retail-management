<?php

namespace Modules\Pos\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Setting;
use App\Support\ApiResponse;
use App\Support\Money;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\Offer\Services\OfferService;
use Modules\Pos\Http\Requests\CheckoutPosRequest;
use Modules\Pos\Services\PosService;

class PosController extends Controller
{
    public function __construct(
        private readonly PosService $posService,
        private readonly OfferService $offerService,
    ) {}

    public function index(): View
    {
        $walkIn = Customer::query()
            ->where('is_walk_in', true)
            ->where('is_active', true)
            ->first();

        $customers = Customer::query()
            ->where('is_active', true)
            ->orderByDesc('is_walk_in')
            ->orderBy('name')
            ->get(['id', 'name', 'is_walk_in']);

        return view('pos::index', [
            'title' => 'POS',
            'customers' => $customers,
            'walkInId' => $walkIn?->id,
            'categories' => Category::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'brands' => Brand::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'currency' => [
                'symbol' => Money::symbol(),
                'code' => Money::code(),
                'decimals' => Money::decimals(),
                'position' => (string) Setting::get('currency_position', 'before'),
            ],
            'routes' => [
                'scan' => route('pos.scan'),
                'search' => route('pos.products.search'),
                'validateOffer' => route('pos.offers.validate'),
                'checkout' => route('pos.checkout'),
            ],
        ]);
    }

    public function scan(Request $request): JsonResponse
    {
        abort_unless($request->user()?->can('pos.access'), 403);

        $result = $this->posService->scan($request->string('upc')->toString());

        return ApiResponse::success('Product scanned.', $result);
    }

    public function searchProducts(Request $request): JsonResponse
    {
        abort_unless($request->user()?->can('pos.access'), 403);

        $products = $this->posService->searchProducts([
            'search' => $request->string('search')->toString() ?: $request->string('q')->toString() ?: null,
            'category_id' => $request->input('category_id') ?: null,
            'brand_id' => $request->input('brand_id') ?: null,
        ]);

        return ApiResponse::success('Products retrieved.', [
            'products' => $products,
        ]);
    }

    public function validateOffer(Request $request): JsonResponse
    {
        abort_unless($request->user()?->can('pos.access'), 403);

        $validated = $request->validate([
            'customer_id' => ['nullable', 'integer', 'exists:customers,id'],
            'subtotal' => ['required', 'numeric', 'min:0'],
            'coupon_code' => ['nullable', 'string', 'max:100'],
            'coupon' => ['nullable', 'string', 'max:100'],
            'offer_id' => ['nullable', 'integer', 'exists:offers,id'],
            'items' => ['nullable', 'array'],
            'items.*.product_id' => ['nullable', 'integer'],
            'items.*.category_id' => ['nullable', 'integer'],
            'items.*.line_subtotal' => ['required_with:items', 'numeric'],
        ]);

        $result = $this->offerService->validateAndCalculate($validated);

        return ApiResponse::success('Offer applied.', [
            'offer_id' => $result['offer_id'],
            'discount_amount' => $result['discount_amount'],
            'offer' => [
                'id' => $result['offer']->id,
                'name' => $result['offer']->name,
                'coupon_code' => $result['offer']->coupon_code,
                'type' => $result['offer']->type->value,
                'value' => (float) $result['offer']->value,
            ],
        ]);
    }

    public function checkout(CheckoutPosRequest $request): JsonResponse
    {
        $result = $this->posService->checkout($request->checkoutPayload(), $request->user());

        return ApiResponse::success('Sale completed successfully.', [
            'order' => $this->transformOrder($result['order']),
            'print_url' => $result['print_url'],
            'redirect' => $result['print_url'],
        ], 201);
    }

    /**
     * @return array<string, mixed>
     */
    protected function transformOrder(Order $order): array
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
            'payments' => $order->payments->map(fn ($payment) => [
                'id' => $payment->id,
                'method' => $payment->method->value,
                'amount' => $payment->amount,
                'amount_received' => $payment->amount_received,
                'change_amount' => $payment->change_amount,
                'reference' => $payment->reference,
            ])->values(),
        ];
    }
}
