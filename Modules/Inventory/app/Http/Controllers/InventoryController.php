<?php

namespace Modules\Inventory\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Support\ApiResponse;
use App\Support\AuditLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\Inventory\Http\Requests\UpdateInventoryRequest;
use Modules\Inventory\Services\StockMovementService;

class InventoryController extends Controller
{
    public function __construct(private readonly StockMovementService $stockMovementService) {}

    public function index(Request $request): View
    {
        $filters = [
            'search' => $request->string('search')->toString() ?: null,
            'low_stock' => $request->boolean('low_stock'),
        ];

        return view('inventory::index', [
            'title' => 'Inventory',
            'products' => $this->stockMovementService->paginateProducts($filters),
            'search' => $filters['search'] ?? '',
            'lowStock' => $filters['low_stock'],
            'stats' => [
                'total' => Product::query()->count(),
                'ok' => Product::query()->where('stock_quantity', '>', 0)
                    ->whereColumn('stock_quantity', '>', 'min_stock_level')->count(),
                'low' => Product::query()->where('stock_quantity', '>', 0)
                    ->whereColumn('stock_quantity', '<=', 'min_stock_level')->count(),
                'out' => Product::query()->where('stock_quantity', '<=', 0)->count(),
            ],
            'hasFilters' => filled($filters['search'] ?? null) || $filters['low_stock'],
        ]);
    }

    public function edit(Product $product): View
    {
        return view('inventory::edit', [
            'title' => 'Edit stock',
            'product' => $product->load(['category:id,name', 'brand:id,name']),
        ]);
    }

    public function update(UpdateInventoryRequest $request, Product $product): JsonResponse
    {
        $product->min_stock_level = $request->input('min_stock_level');
        $product->save();

        $movement = $this->stockMovementService->adjust(
            $product,
            $request->input('stock_quantity'),
            $request->string('reason')->toString(),
            $request->user(),
        );

        AuditLogger::log('inventory.updated', $product, [
            'movement_id' => $movement->id,
            'quantity' => $movement->quantity,
            'quantity_before' => $movement->quantity_before,
            'quantity_after' => $movement->quantity_after,
            'min_stock_level' => $product->min_stock_level,
            'reason' => $movement->reason,
        ]);

        return ApiResponse::success('Inventory updated successfully.', [
            'product' => [
                'id' => $product->id,
                'stock_quantity' => $product->fresh()->stock_quantity,
                'min_stock_level' => $product->min_stock_level,
            ],
            'redirect' => route('inventory.index'),
        ]);
    }
}
