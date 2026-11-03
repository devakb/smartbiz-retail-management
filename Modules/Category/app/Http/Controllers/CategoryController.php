<?php

namespace Modules\Category\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\Category\Http\Requests\StoreCategoryRequest;
use Modules\Category\Http\Requests\UpdateCategoryRequest;
use Modules\Category\Services\CategoryService;

class CategoryController extends Controller
{
    public function __construct(private readonly CategoryService $categoryService) {}

    public function index(Request $request): View
    {
        $search = $request->string('search')->toString();

        return view('category::index', [
            'title' => 'Categories',
            'categories' => $this->categoryService->paginate($search ?: null),
            'search' => $search,
            'hasFilters' => $search !== '',
        ]);
    }

    public function create(): View
    {
        return view('category::create', [
            'title' => 'Create category',
            'parents' => $this->categoryService->options(),
        ]);
    }

    public function store(StoreCategoryRequest $request): JsonResponse
    {
        $category = $this->categoryService->create($request->validated());

        return ApiResponse::success('Category created successfully.', [
            'category' => $this->transform($category),
            'redirect' => route('category.index'),
        ], 201);
    }

    public function edit(Category $category): View
    {
        return view('category::edit', [
            'title' => 'Edit category',
            'category' => $category,
            'parents' => $this->categoryService->options($category->id),
        ]);
    }

    public function update(UpdateCategoryRequest $request, Category $category): JsonResponse
    {
        $category = $this->categoryService->update($category, $request->validated());

        return ApiResponse::success('Category updated successfully.', [
            'category' => $this->transform($category),
            'redirect' => route('category.index'),
        ]);
    }

    public function destroy(Category $category): JsonResponse
    {
        $this->categoryService->delete($category);

        return ApiResponse::success('Category deleted successfully.', [
            'redirect' => route('category.index'),
        ]);
    }

    /**
     * @return array{id: int, name: string, slug: string, description: string|null, parent_id: int|null, is_active: bool}
     */
    protected function transform(Category $category): array
    {
        return [
            'id' => $category->id,
            'name' => $category->name,
            'slug' => $category->slug,
            'description' => $category->description,
            'parent_id' => $category->parent_id,
            'is_active' => (bool) $category->is_active,
        ];
    }
}
