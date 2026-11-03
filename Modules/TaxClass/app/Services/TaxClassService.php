<?php

namespace Modules\TaxClass\Services;

use App\Models\TaxClass;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class TaxClassService
{
    public function paginate(?string $search = null, int $perPage = 15): LengthAwarePaginator
    {
        return TaxClass::query()
            ->when($search, function ($query) use ($search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('name', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->orderBy('rate')
            ->orderBy('name')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * @return Collection<int, TaxClass>
     */
    public function search(?string $term = null, int $limit = 20): Collection
    {
        return TaxClass::query()
            ->where('is_active', true)
            ->when($term, fn ($query) => $query->where('name', 'like', "%{$term}%"))
            ->orderBy('rate')
            ->orderBy('name')
            ->limit($limit)
            ->get(['id', 'name', 'rate']);
    }

    /**
     * @param  array{name: string, rate: float|int|string, description?: string|null, is_active?: bool}  $data
     */
    public function create(array $data): TaxClass
    {
        return TaxClass::query()->create([
            'name' => $data['name'],
            'rate' => $data['rate'],
            'description' => $data['description'] ?? null,
            'is_active' => $data['is_active'] ?? true,
        ]);
    }

    /**
     * @param  array{name: string, rate: float|int|string, description?: string|null, is_active?: bool}  $data
     */
    public function update(TaxClass $taxClass, array $data): TaxClass
    {
        $taxClass->fill([
            'name' => $data['name'],
            'rate' => $data['rate'],
            'description' => $data['description'] ?? null,
            'is_active' => $data['is_active'] ?? $taxClass->is_active,
        ])->save();

        return $taxClass->refresh();
    }

    public function archive(TaxClass $taxClass): void
    {
        $taxClass->delete();
    }

    public function restore(TaxClass $taxClass): TaxClass
    {
        $taxClass->restore();

        return $taxClass->refresh();
    }
}
