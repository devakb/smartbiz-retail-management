<?php

namespace Modules\TaxClass\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTaxClassRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('tax-classes.create') ?? false;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_active' => $this->boolean('is_active'),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('tax_classes', 'name')->whereNull('deleted_at'),
            ],
            'rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'description' => ['nullable', 'string'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
