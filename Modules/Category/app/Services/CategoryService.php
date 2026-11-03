<?php

namespace Modules\Category\Services;

use App\Models\Category;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CategoryService
{
    public function paginate(?string $search = null, int $perPage = 15): LengthAwarePaginator
    {
        return Category::query()
            ->with('parent:id,name')
            ->when($search, function ($query) use ($search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('name', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhere('slug', 'like', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * @return Collection<int, Category>
     */
    public function options(?int $excludeId = null): Collection
    {
        return Category::query()
            ->when($excludeId, fn ($query) => $query->whereKeyNot($excludeId))
            ->orderBy('name')
            ->get(['id', 'name', 'parent_id']);
    }

    /**
     * @param  array{name: string, description?: string|null, parent_id?: int|null, is_active?: bool}  $data
     */
    public function create(array $data): Category
    {
        return Category::query()->create([
            'name' => $data['name'],
            'slug' => $this->uniqueSlug($data['name']),
            'description' => $data['description'] ?? null,
            'parent_id' => $data['parent_id'] ?? null,
            'is_active' => $data['is_active'] ?? true,
        ]);
    }

    /**
     * @param  array{name: string, description?: string|null, parent_id?: int|null, is_active?: bool}  $data
     */
    public function update(Category $category, array $data): Category
    {
        if (! empty($data['parent_id']) && (int) $data['parent_id'] === (int) $category->id) {
            throw ValidationException::withMessages([
                'parent_id' => 'A category cannot be its own parent.',
            ]);
        }

        $category->fill([
            'name' => $data['name'],
            'slug' => $this->uniqueSlug($data['name'], $category->id),
            'description' => $data['description'] ?? null,
            'parent_id' => $data['parent_id'] ?? null,
            'is_active' => $data['is_active'] ?? $category->is_active,
        ])->save();

        return $category->refresh()->load('parent:id,name');
    }

    public function delete(Category $category): void
    {
        if ($category->children()->exists()) {
            throw ValidationException::withMessages([
                'category' => 'Cannot delete a category that has child categories.',
            ]);
        }

        if ($category->products()->exists()) {
            throw ValidationException::withMessages([
                'category' => 'Cannot delete a category that has products.',
            ]);
        }

        $category->delete();
    }

    protected function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name) ?: 'category';
        $slug = $base;
        $counter = 1;

        while (
            Category::withTrashed()
                ->where('slug', $slug)
                ->when($ignoreId, fn ($query) => $query->whereKeyNot($ignoreId))
                ->exists()
        ) {
            $slug = $base.'-'.$counter;
            $counter++;
        }

        return $slug;
    }
}
