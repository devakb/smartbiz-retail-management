<?php

namespace Modules\Product\Services;

use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\QueryException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ProductService
{
    public function __construct(private readonly UpcService $upcService) {}

    /**
     * @return array{total: int, in_stock: int, low_stock: int, out_of_stock: int}
     */
    public function stats(): array
    {
        $base = Product::query();

        return [
            'total' => (clone $base)->count(),
            'in_stock' => (clone $base)->where('stock_quantity', '>', 0)
                ->whereColumn('stock_quantity', '>', 'min_stock_level')
                ->count(),
            'low_stock' => (clone $base)->where('stock_quantity', '>', 0)
                ->whereColumn('stock_quantity', '<=', 'min_stock_level')
                ->count(),
            'out_of_stock' => (clone $base)->where('stock_quantity', '<=', 0)->count(),
        ];
    }

    /**
     * @param  array{search?: string|null, category_id?: int|null, brand_id?: int|null, is_active?: bool|null}  $filters
     */
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $search = $filters['search'] ?? null;

        return Product::query()
            ->with(['category:id,name', 'brand:id,name', 'taxClass:id,name,rate'])
            ->when($search, function ($query) use ($search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('name', 'like', "%{$search}%")
                        ->orWhere('upc', 'like', "%{$search}%")
                        ->orWhere('sku', 'like', "%{$search}%");
                });
            })
            ->when(isset($filters['category_id']) && $filters['category_id'] !== null && $filters['category_id'] !== '',
                fn ($query) => $query->where('category_id', $filters['category_id'])
            )
            ->when(isset($filters['brand_id']) && $filters['brand_id'] !== null && $filters['brand_id'] !== '',
                fn ($query) => $query->where('brand_id', $filters['brand_id'])
            )
            ->when(array_key_exists('is_active', $filters) && $filters['is_active'] !== null && $filters['is_active'] !== '',
                fn ($query) => $query->where('is_active', (bool) $filters['is_active'])
            )
            ->orderBy('name')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Product
    {
        $payload = $this->payloadFrom($data);

        try {
            return Product::query()->create($payload);
        } catch (QueryException $exception) {
            $this->throwDuplicateValidation($exception);
        }
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Product $product, array $data): Product
    {
        $payload = $this->payloadFrom($data, $product);

        try {
            $product->fill($payload)->save();
        } catch (QueryException $exception) {
            $this->throwDuplicateValidation($exception);
        }

        return $product->refresh()->load(['category:id,name', 'brand:id,name', 'taxClass:id,name,rate']);
    }

    public function delete(Product $product): void
    {
        $product->delete();
    }

    public function suggestSku(string $name): string
    {
        $base = Str::upper(Str::slug(Str::limit($name, 20, ''), '-')) ?: 'SKU';
        $base = Str::replace('-', '', $base);
        $base = Str::limit($base, 12, '');

        do {
            $sku = $base.'-'.Str::upper(Str::random(4));
        } while (Product::withTrashed()->where('sku', $sku)->exists());

        return $sku;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function payloadFrom(array $data, ?Product $product = null): array
    {
        $upc = $this->upcService->normalize($data['upc'] ?? '');

        if ($upc === '') {
            throw ValidationException::withMessages([
                'upc' => 'UPC is required.',
            ]);
        }

        $payload = [
            'upc' => $upc,
            'sku' => trim((string) ($data['sku'] ?? '')),
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'category_id' => $data['category_id'],
            'brand_id' => $data['brand_id'] ?? null,
            'tax_class_id' => $data['tax_class_id'] ?? null,
            'mrp' => $data['mrp'] ?? 0,
            'selling_price' => $data['selling_price'] ?? 0,
            'min_stock_level' => $data['min_stock_level'] ?? 0,
            'unit' => $data['unit'] ?? 'pcs',
            'is_active' => $data['is_active'] ?? true,
        ];

        if (isset($data['image']) && $data['image'] instanceof UploadedFile) {
            if ($product?->image) {
                $this->deleteImage($product->image);
            }
            $payload['image'] = $this->storeImage($data['image']);
        }

        if ($product === null) {
            $payload['stock_quantity'] = 0;
        }

        return $payload;
    }

    protected function storeImage(UploadedFile $file): string
    {
        return $file->store('products', 'public');
    }

    protected function deleteImage(?string $path): void
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }

    protected function throwDuplicateValidation(QueryException $exception): never
    {
        $message = $exception->getMessage();

        if (str_contains($message, 'products_upc_unique') || str_contains($message, "for key 'products_upc_unique'")) {
            throw ValidationException::withMessages([
                'upc' => 'This UPC is already in use.',
            ]);
        }

        if (str_contains($message, 'products_sku_unique') || str_contains($message, "for key 'products_sku_unique'")) {
            throw ValidationException::withMessages([
                'sku' => 'This SKU is already in use.',
            ]);
        }

        throw $exception;
    }
}
