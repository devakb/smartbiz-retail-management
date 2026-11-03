<?php

namespace Modules\Product\Services;

use App\Models\Product;

class UpcService
{
    public function normalize(?string $upc): string
    {
        return preg_replace('/\s+/', '', trim((string) $upc)) ?? '';
    }

    /**
     * @return array{status: string, upc: string, product: array<string, mixed>|null}
     */
    public function check(?string $upc, ?int $ignoreProductId = null): array
    {
        $normalized = $this->normalize($upc);

        if ($normalized === '') {
            return [
                'status' => 'checking',
                'upc' => $normalized,
                'product' => null,
            ];
        }

        $product = Product::withTrashed()
            ->with(['category:id,name', 'brand:id,name'])
            ->when($ignoreProductId, fn ($query) => $query->whereKeyNot($ignoreProductId))
            ->where('upc', $normalized)
            ->first();

        if ($product) {
            return [
                'status' => 'exists',
                'upc' => $normalized,
                'product' => [
                    'id' => $product->id,
                    'name' => $product->name,
                    'upc' => $product->upc,
                    'sku' => $product->sku,
                    'category' => $product->category?->name,
                    'brand' => $product->brand?->name,
                    'is_active' => (bool) $product->is_active,
                    'deleted' => $product->trashed(),
                    'edit_url' => $product->trashed() ? null : route('product.edit', $product),
                ],
            ];
        }

        return [
            'status' => 'available',
            'upc' => $normalized,
            'product' => null,
        ];
    }

    public function exists(string $upc, ?int $ignoreProductId = null): bool
    {
        return Product::withTrashed()
            ->when($ignoreProductId, fn ($query) => $query->whereKeyNot($ignoreProductId))
            ->where('upc', $this->normalize($upc))
            ->exists();
    }
}
