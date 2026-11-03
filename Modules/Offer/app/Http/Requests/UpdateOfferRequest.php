<?php

namespace Modules\Offer\Http\Requests;

use App\Enums\OfferScope;
use App\Enums\OfferType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateOfferRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('offers.manage') ?? false;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_active' => $this->boolean('is_active'),
            'coupon_code' => $this->filled('coupon_code') ? trim((string) $this->input('coupon_code')) : null,
            'product_ids' => array_values(array_filter((array) $this->input('product_ids', []))),
            'category_ids' => array_values(array_filter((array) $this->input('category_ids', []))),
            'min_purchase' => $this->filled('min_purchase') ? $this->input('min_purchase') : null,
            'max_discount' => $this->filled('max_discount') ? $this->input('max_discount') : null,
            'usage_limit' => $this->filled('usage_limit') ? $this->input('usage_limit') : null,
            'per_customer_limit' => $this->filled('per_customer_limit') ? $this->input('per_customer_limit') : null,
            'starts_at' => $this->filled('starts_at') ? $this->input('starts_at') : null,
            'ends_at' => $this->filled('ends_at') ? $this->input('ends_at') : null,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $offer = $this->route('offer');
        $offerId = is_object($offer) ? $offer->id : $offer;

        return [
            'name' => ['required', 'string', 'max:255'],
            'coupon_code' => [
                'nullable',
                'string',
                'max:64',
                Rule::unique('offers', 'coupon_code')->whereNull('deleted_at')->ignore($offerId),
            ],
            'type' => ['required', Rule::enum(OfferType::class)],
            'value' => ['required', 'numeric', 'min:0.01'],
            'min_purchase' => ['nullable', 'numeric', 'min:0'],
            'max_discount' => ['nullable', 'numeric', 'min:0'],
            'usage_limit' => ['nullable', 'integer', 'min:1'],
            'per_customer_limit' => ['nullable', 'integer', 'min:1'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'scope' => ['required', Rule::enum(OfferScope::class)],
            'is_active' => ['sometimes', 'boolean'],
            'product_ids' => ['nullable', 'array'],
            'product_ids.*' => ['integer', 'exists:products,id'],
            'category_ids' => ['nullable', 'array'],
            'category_ids.*' => ['integer', 'exists:categories,id'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $type = $this->input('type');
            $value = (float) $this->input('value', 0);

            if ($type === OfferType::Percentage->value && $value > 100) {
                $validator->errors()->add('value', 'Percentage value cannot exceed 100.');
            }

            $scope = $this->input('scope');

            if ($scope === OfferScope::Product->value && blank($this->input('product_ids'))) {
                $validator->errors()->add('product_ids', 'Select at least one product for product-scoped offers.');
            }

            if ($scope === OfferScope::Category->value && blank($this->input('category_ids'))) {
                $validator->errors()->add('category_ids', 'Select at least one category for category-scoped offers.');
            }
        });
    }
}
