<?php

namespace Modules\Offer\Http\Controllers;

use App\Enums\OfferScope;
use App\Enums\OfferType;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Offer;
use App\Models\Product;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\Offer\Http\Requests\StoreOfferRequest;
use Modules\Offer\Http\Requests\UpdateOfferRequest;
use Modules\Offer\Services\OfferService;

class OfferController extends Controller
{
    public function __construct(private readonly OfferService $offerService) {}

    public function index(Request $request): View
    {
        $search = $request->string('search')->toString();

        return view('offer::index', [
            'title' => 'Offers',
            'offers' => $this->offerService->paginate($search ?: null),
            'search' => $search,
            'hasFilters' => $search !== '',
        ]);
    }

    public function create(): View
    {
        return view('offer::create', [
            'title' => 'Create offer',
            'offer' => null,
            'types' => OfferType::cases(),
            'scopes' => OfferScope::cases(),
            'products' => Product::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'categories' => Category::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(StoreOfferRequest $request): JsonResponse
    {
        $offer = $this->offerService->create($request->validated());

        return ApiResponse::success('Offer created successfully.', [
            'offer' => $this->transform($offer),
            'redirect' => route('offer.index'),
        ], 201);
    }

    public function edit(Offer $offer): View
    {
        $offer->load(['products:id,name', 'categories:id,name']);

        return view('offer::edit', [
            'title' => 'Edit offer',
            'offer' => $offer,
            'types' => OfferType::cases(),
            'scopes' => OfferScope::cases(),
            'products' => Product::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'categories' => Category::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function update(UpdateOfferRequest $request, Offer $offer): JsonResponse
    {
        $offer = $this->offerService->update($offer, $request->validated());

        return ApiResponse::success('Offer updated successfully.', [
            'offer' => $this->transform($offer),
            'redirect' => route('offer.index'),
        ]);
    }

    public function destroy(Offer $offer): JsonResponse
    {
        $this->offerService->delete($offer);

        return ApiResponse::success('Offer deleted successfully.', [
            'redirect' => route('offer.index'),
        ]);
    }

    public function activate(Offer $offer): JsonResponse
    {
        $offer = $this->offerService->activate($offer);

        return ApiResponse::success('Offer activated.', [
            'offer' => $this->transform($offer),
            'redirect' => route('offer.index'),
        ]);
    }

    public function deactivate(Offer $offer): JsonResponse
    {
        $offer = $this->offerService->deactivate($offer);

        return ApiResponse::success('Offer deactivated.', [
            'offer' => $this->transform($offer),
            'redirect' => route('offer.index'),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function transform(Offer $offer): array
    {
        $offer->loadMissing(['products:id,name', 'categories:id,name']);

        return [
            'id' => $offer->id,
            'name' => $offer->name,
            'coupon_code' => $offer->coupon_code,
            'type' => $offer->type->value,
            'value' => (string) $offer->value,
            'min_purchase' => $offer->min_purchase !== null ? (string) $offer->min_purchase : null,
            'max_discount' => $offer->max_discount !== null ? (string) $offer->max_discount : null,
            'usage_limit' => $offer->usage_limit,
            'per_customer_limit' => $offer->per_customer_limit,
            'starts_at' => optional($offer->starts_at)?->toDateTimeString(),
            'ends_at' => optional($offer->ends_at)?->toDateTimeString(),
            'scope' => $offer->scope->value,
            'is_active' => (bool) $offer->is_active,
            'product_ids' => $offer->products->pluck('id')->values()->all(),
            'category_ids' => $offer->categories->pluck('id')->values()->all(),
        ];
    }
}
