<?php

namespace Modules\Product\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Product\Services\UpcService;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('products.update') ?? false;
    }

    protected function prepareForValidation(): void
    {
        /** @var UpcService $upcService */
        $upcService = app(UpcService::class);

        $this->merge([
            'upc' => $upcService->normalize($this->input('upc')),
            'sku' => $this->filled('sku') ? trim((string) $this->input('sku')) : null,
            'brand_id' => $this->filled('brand_id') ? $this->input('brand_id') : null,
            'tax_class_id' => $this->filled('tax_class_id') ? $this->input('tax_class_id') : null,
            'is_active' => $this->boolean('is_active'),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $productId = $this->route('product')?->id ?? $this->route('product');

        return [
            'upc' => ['required', 'string', 'max:64', Rule::unique('products', 'upc')->ignore($productId)],
            'sku' => ['required', 'string', 'max:64', Rule::unique('products', 'sku')->ignore($productId)],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'brand_id' => ['nullable', 'integer', 'exists:brands,id'],
            'tax_class_id' => ['nullable', 'integer', 'exists:tax_classes,id'],
            'mrp' => ['required', 'numeric', 'min:0'],
            'selling_price' => ['required', 'numeric', 'min:0'],
            'min_stock_level' => ['nullable', 'numeric', 'min:0'],
            'unit' => ['nullable', 'string', 'max:32'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
