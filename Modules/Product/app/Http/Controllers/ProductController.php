<?php

namespace Modules\Product\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\TaxClass;
use App\Support\ApiResponse;
use App\Support\AuditLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Modules\Product\Http\Requests\StoreProductRequest;
use Modules\Product\Http\Requests\UpdateProductRequest;
use Modules\Product\Services\ProductService;
use Modules\Product\Services\UpcService;

class ProductController extends Controller
{
    public function __construct(
        private readonly ProductService $productService,
        private readonly UpcService $upcService,
    ) {}

    public function index(Request $request): View
    {
        $filters = [
            'search' => $request->string('search')->toString() ?: null,
            'category_id' => $request->input('category_id'),
            'brand_id' => $request->input('brand_id'),
            'is_active' => $request->has('is_active') && $request->input('is_active') !== ''
                ? $request->boolean('is_active')
                : null,
        ];

        return view('product::index', [
            'title' => 'Products',
            'products' => $this->productService->paginate($filters),
            'stats' => $this->productService->stats(),
            'search' => $filters['search'] ?? '',
            'categoryId' => $filters['category_id'],
            'brandId' => $filters['brand_id'],
            'isActive' => $request->input('is_active', ''),
            'categories' => Category::query()->orderBy('name')->get(['id', 'name']),
            'brands' => Brand::query()->orderBy('name')->get(['id', 'name']),
            'hasFilters' => filled($filters['search'] ?? null)
                || filled($filters['category_id'] ?? null)
                || filled($filters['brand_id'] ?? null)
                || ($filters['is_active'] !== null),
        ]);
    }

    public function create(): View
    {
        return view('product::create', [
            'title' => 'Create product',
            'categories' => Category::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'brands' => Brand::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'taxClasses' => TaxClass::query()->where('is_active', true)->orderBy('name')->get(['id', 'name', 'rate']),
        ]);
    }

    public function checkUpc(Request $request): JsonResponse
    {
        $result = $this->upcService->check(
            $request->string('upc')->toString(),
            $request->integer('ignore_id') ?: null,
        );

        return ApiResponse::success('UPC checked.', $result);
    }

    public function suggestSku(Request $request): JsonResponse
    {
        $name = $request->string('name')->toString();

        return ApiResponse::success('SKU suggested.', [
            'sku' => $this->productService->suggestSku($name !== '' ? $name : 'PRODUCT'),
        ]);
    }

    public function store(StoreProductRequest $request): JsonResponse
    {
        $product = $this->productService->create($request->validated());

        AuditLogger::log('product.created', $product, [
            'upc' => $product->upc,
            'sku' => $product->sku,
            'name' => $product->name,
        ]);

        return ApiResponse::success('Product created successfully.', [
            'product' => $this->transform($product),
            'redirect' => route('product.index'),
        ], 201);
    }

    public function edit(Product $product): View
    {
        $product->load(['category:id,name', 'brand:id,name', 'taxClass:id,name,rate']);

        return view('product::edit', [
            'title' => 'Edit product',
            'product' => $product,
            'categories' => Category::query()->orderBy('name')->get(['id', 'name']),
            'brands' => Brand::query()->orderBy('name')->get(['id', 'name']),
            'taxClasses' => TaxClass::query()->where('is_active', true)->orderBy('name')->get(['id', 'name', 'rate']),
        ]);
    }

    public function update(UpdateProductRequest $request, Product $product): JsonResponse
    {
        $product = $this->productService->update($product, $request->validated());

        return ApiResponse::success('Product updated successfully.', [
            'product' => $this->transform($product),
            'redirect' => route('product.index'),
        ]);
    }

    public function destroy(Product $product): JsonResponse
    {
        $this->productService->delete($product);

        return ApiResponse::success('Product deleted successfully.', [
            'redirect' => route('product.index'),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function transform(Product $product): array
    {
        $product->loadMissing(['category:id,name', 'brand:id,name', 'taxClass:id,name,rate']);

        return [
            'id' => $product->id,
            'upc' => $product->upc,
            'sku' => $product->sku,
            'name' => $product->name,
            'description' => $product->description,
            'category_id' => $product->category_id,
            'brand_id' => $product->brand_id,
            'tax_class_id' => $product->tax_class_id,
            'category' => $product->category?->name,
            'brand' => $product->brand?->name,
            'tax_class' => $product->taxClass?->name,
            'tax_rate' => $product->taxRate(),
            'mrp' => $product->mrp,
            'selling_price' => $product->selling_price,
            'min_stock_level' => $product->min_stock_level,
            'unit' => $product->unit,
            'image' => $product->image,
            'image_url' => $product->image ? Storage::disk('public')->url($product->image) : null,
            'stock_quantity' => $product->stock_quantity,
            'is_active' => (bool) $product->is_active,
        ];
    }
}
