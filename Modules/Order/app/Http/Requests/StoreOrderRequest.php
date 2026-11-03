<?php

namespace Modules\Order\Http\Requests;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('orders.create') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'customer_id' => ['required', 'integer', 'exists:customers,id'],
            'status' => ['sometimes', 'string', Rule::in([OrderStatus::Draft->value, OrderStatus::Confirmed->value])],
            'notes' => ['nullable', 'string'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            // Intentionally ignored — totals are computed server-side from product prices.
            'subtotal' => ['sometimes'],
            'tax_amount' => ['sometimes'],
            'total' => ['sometimes'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.quantity' => ['required', 'numeric', 'gt:0'],
            'items.*.discount_amount' => ['nullable', 'numeric', 'min:0'],
            'items.*.unit_price' => ['sometimes'],
            'items.*.line_total' => ['sometimes'],
            'payment' => ['sometimes', 'nullable', 'array'],
            'payment.method' => ['required_with:payment', Rule::enum(PaymentMethod::class)],
            'payment.amount' => ['required_with:payment', 'numeric', 'gt:0'],
            'payment.amount_received' => ['nullable', 'numeric', 'min:0'],
            'payment.reference' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function orderPayload(): array
    {
        $validated = $this->validated();

        // Never trust browser-computed money fields.
        unset($validated['subtotal'], $validated['tax_amount'], $validated['total']);

        if (isset($validated['items']) && is_array($validated['items'])) {
            $validated['items'] = array_map(function (array $item) {
                unset($item['unit_price'], $item['line_total'], $item['tax_percent']);

                return $item;
            }, $validated['items']);
        }

        return $validated;
    }
}
