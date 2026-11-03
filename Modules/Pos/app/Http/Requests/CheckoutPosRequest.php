<?php

namespace Modules\Pos\Http\Requests;

use App\Enums\PaymentMethod;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CheckoutPosRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('pos.access') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $method = $this->input('payment.method');

        return [
            'checkout_token' => ['required', 'string', 'uuid'],
            'customer_id' => ['required', 'integer', 'exists:customers,id'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'coupon_code' => ['nullable', 'string', 'max:100'],
            'coupon' => ['nullable', 'string', 'max:100'],
            'offer_id' => ['nullable', 'integer', 'exists:offers,id'],
            // Client totals are display-only; ignored server-side.
            'subtotal' => ['sometimes'],
            'tax_amount' => ['sometimes'],
            'discount_amount' => ['sometimes'],
            'total' => ['sometimes'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.quantity' => ['required', 'numeric', 'gt:0'],
            'items.*.discount_amount' => ['nullable', 'numeric', 'min:0'],
            'items.*.unit_price' => ['sometimes'],
            'items.*.line_total' => ['sometimes'],
            'payment' => ['required', 'array'],
            'payment.method' => ['required', Rule::enum(PaymentMethod::class)],
            'payment.amount' => ['sometimes'],
            'payment.amount_received' => [
                Rule::requiredIf(fn () => $method === PaymentMethod::Cash->value),
                'nullable',
                'numeric',
                'min:0',
            ],
            'payment.reference' => [
                Rule::requiredIf(fn () => in_array($method, [PaymentMethod::Card->value, PaymentMethod::Upi->value], true)),
                'nullable',
                'string',
                'max:255',
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function checkoutPayload(): array
    {
        $validated = $this->validated();

        unset(
            $validated['subtotal'],
            $validated['tax_amount'],
            $validated['discount_amount'],
            $validated['total'],
        );

        if (isset($validated['items']) && is_array($validated['items'])) {
            $validated['items'] = array_map(function (array $item) {
                unset($item['unit_price'], $item['line_total'], $item['tax_percent']);

                return $item;
            }, $validated['items']);
        }

        if (isset($validated['payment']) && is_array($validated['payment'])) {
            unset($validated['payment']['amount'], $validated['payment']['change_amount']);
        }

        return $validated;
    }
}
