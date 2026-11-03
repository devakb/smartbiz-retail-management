<?php

namespace Modules\TaxClass\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTaxClassRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('tax-classes.update') ?? false;
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
        $taxClass = $this->route('taxClass');
        $taxClassId = is_object($taxClass) ? $taxClass->id : $taxClass;

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('tax_classes', 'name')->whereNull('deleted_at')->ignore($taxClassId),
            ],
            'rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'description' => ['nullable', 'string'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
