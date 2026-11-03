<?php

namespace Modules\Category\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('categories.create') ?? false;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_active' => $this->boolean('is_active'),
            'parent_id' => $this->filled('parent_id') ? $this->input('parent_id') : null,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $parentId = $this->input('parent_id');

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('categories', 'name')->where(function ($query) use ($parentId) {
                    if ($parentId === null) {
                        $query->whereNull('parent_id');
                    } else {
                        $query->where('parent_id', $parentId);
                    }

                    $query->whereNull('deleted_at');
                }),
            ],
            'description' => ['nullable', 'string'],
            'parent_id' => ['nullable', 'integer', 'exists:categories,id'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
