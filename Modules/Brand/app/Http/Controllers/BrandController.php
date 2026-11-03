<?php

namespace Modules\Brand\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Modules\Brand\Http\Requests\StoreBrandRequest;
use Modules\Brand\Http\Requests\UpdateBrandRequest;
use Modules\Brand\Services\BrandService;

class BrandController extends Controller
{
    public function __construct(private readonly BrandService $brandService) {}

    public function index(Request $request): View
    {
        $search = $request->string('search')->toString();

        return view('brand::index', [
            'title' => 'Brands',
            'brands' => $this->brandService->paginate($search ?: null),
            'search' => $search,
            'hasFilters' => $search !== '',
        ]);
    }

    public function create(): View
    {
        return view('brand::create', [
            'title' => 'Create brand',
        ]);
    }

    public function store(StoreBrandRequest $request): JsonResponse
    {
        $brand = $this->brandService->create($request->validated());

        return ApiResponse::success('Brand created successfully.', [
            'brand' => $this->transform($brand),
            'redirect' => route('brand.index'),
        ], 201);
    }

    public function edit(Brand $brand): View
    {
        return view('brand::edit', [
            'title' => 'Edit brand',
            'brand' => $brand,
        ]);
    }

    public function update(UpdateBrandRequest $request, Brand $brand): JsonResponse
    {
        $brand = $this->brandService->update($brand, $request->validated());

        return ApiResponse::success('Brand updated successfully.', [
            'brand' => $this->transform($brand),
            'redirect' => route('brand.index'),
        ]);
    }

    public function destroy(Brand $brand): JsonResponse
    {
        $this->brandService->delete($brand);

        return ApiResponse::success('Brand deleted successfully.', [
            'redirect' => route('brand.index'),
        ]);
    }

    /**
     * @return array{id: int, name: string, slug: string, description: string|null, logo: string|null, logo_url: string|null, is_active: bool}
     */
    protected function transform(Brand $brand): array
    {
        return [
            'id' => $brand->id,
            'name' => $brand->name,
            'slug' => $brand->slug,
            'description' => $brand->description,
            'logo' => $brand->logo,
            'logo_url' => $brand->logo ? Storage::disk('public')->url($brand->logo) : null,
            'is_active' => (bool) $brand->is_active,
        ];
    }
}
