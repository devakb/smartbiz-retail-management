<?php

namespace Modules\TaxClass\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\TaxClass;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\TaxClass\Http\Requests\StoreTaxClassRequest;
use Modules\TaxClass\Http\Requests\UpdateTaxClassRequest;
use Modules\TaxClass\Services\TaxClassService;

class TaxClassController extends Controller
{
    public function __construct(private readonly TaxClassService $taxClassService) {}

    public function index(Request $request): View
    {
        $search = $request->string('search')->toString();

        return view('taxclass::index', [
            'title' => 'Tax Classes',
            'taxClasses' => $this->taxClassService->paginate($search ?: null),
            'search' => $search,
            'hasFilters' => $search !== '',
        ]);
    }

    public function create(): View
    {
        return view('taxclass::create', [
            'title' => 'Create tax class',
        ]);
    }

    public function store(StoreTaxClassRequest $request): JsonResponse
    {
        $taxClass = $this->taxClassService->create($request->validated());

        return ApiResponse::success('Tax class created successfully.', [
            'tax_class' => $this->transform($taxClass),
            'redirect' => route('tax-class.index'),
        ], 201);
    }

    public function edit(TaxClass $taxClass): View
    {
        return view('taxclass::edit', [
            'title' => 'Edit tax class',
            'taxClass' => $taxClass,
        ]);
    }

    public function update(UpdateTaxClassRequest $request, TaxClass $taxClass): JsonResponse
    {
        $taxClass = $this->taxClassService->update($taxClass, $request->validated());

        return ApiResponse::success('Tax class updated successfully.', [
            'tax_class' => $this->transform($taxClass),
            'redirect' => route('tax-class.index'),
        ]);
    }

    public function destroy(TaxClass $taxClass): JsonResponse
    {
        $this->taxClassService->archive($taxClass);

        return ApiResponse::success('Tax class archived successfully.', [
            'redirect' => route('tax-class.index'),
        ]);
    }

    /**
     * @return array{id: int, name: string, rate: string, description: string|null, is_active: bool}
     */
    protected function transform(TaxClass $taxClass): array
    {
        return [
            'id' => $taxClass->id,
            'name' => $taxClass->name,
            'rate' => (string) $taxClass->rate,
            'description' => $taxClass->description,
            'is_active' => (bool) $taxClass->is_active,
        ];
    }
}
