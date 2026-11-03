<?php

namespace Modules\Brand\Services;

use App\Models\Brand;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BrandService
{
    public function paginate(?string $search = null, int $perPage = 15): LengthAwarePaginator
    {
        return Brand::query()
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
     * @param  array{name: string, description?: string|null, logo?: UploadedFile|null, is_active?: bool}  $data
     */
    public function create(array $data): Brand
    {
        return Brand::query()->create([
            'name' => $data['name'],
            'slug' => $this->uniqueSlug($data['name']),
            'description' => $data['description'] ?? null,
            'logo' => isset($data['logo']) && $data['logo'] instanceof UploadedFile
                ? $this->storeLogo($data['logo'])
                : null,
            'is_active' => $data['is_active'] ?? true,
        ]);
    }

    /**
     * @param  array{name: string, description?: string|null, logo?: UploadedFile|null, is_active?: bool}  $data
     */
    public function update(Brand $brand, array $data): Brand
    {
        $payload = [
            'name' => $data['name'],
            'slug' => $this->uniqueSlug($data['name'], $brand->id),
            'description' => $data['description'] ?? null,
            'is_active' => $data['is_active'] ?? $brand->is_active,
        ];

        if (isset($data['logo']) && $data['logo'] instanceof UploadedFile) {
            $this->deleteLogo($brand->logo);
            $payload['logo'] = $this->storeLogo($data['logo']);
        }

        $brand->fill($payload)->save();

        return $brand->refresh();
    }

    public function delete(Brand $brand): void
    {
        $this->deleteLogo($brand->logo);
        $brand->delete();
    }

    protected function storeLogo(UploadedFile $file): string
    {
        return $file->store('brands', 'public');
    }

    protected function deleteLogo(?string $path): void
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }

    protected function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name) ?: 'brand';
        $slug = $base;
        $counter = 1;

        while (
            Brand::withTrashed()
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
