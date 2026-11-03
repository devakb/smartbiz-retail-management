<?php

use Illuminate\Support\Facades\Route;
use Modules\Report\Http\Controllers\DashboardController;
use Modules\Report\Http\Controllers\ReportController;

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::middleware('permission:reports.view')->prefix('reports')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('report.index');

        Route::get('/sales', [ReportController::class, 'sales'])->name('report.sales');
        Route::get('/sales/export/{format}', [ReportController::class, 'exportSales'])
            ->whereIn('format', ['csv', 'xlsx', 'pdf'])
            ->name('report.sales.export');

        Route::get('/stock-movements', [ReportController::class, 'stockMovements'])->name('report.stock-movements');
        Route::get('/stock-movements/export/{format}', [ReportController::class, 'exportStockMovements'])
            ->whereIn('format', ['csv', 'xlsx'])
            ->name('report.stock-movements.export');

        Route::get('/inventory', [ReportController::class, 'inventory'])->name('report.inventory');
        Route::get('/inventory/export/{format}', [ReportController::class, 'exportInventory'])
            ->whereIn('format', ['csv', 'xlsx'])
            ->name('report.inventory.export');

        Route::get('/low-stock', [ReportController::class, 'lowStock'])->name('report.low-stock');
        Route::get('/low-stock/export/{format}', [ReportController::class, 'exportLowStock'])
            ->whereIn('format', ['csv', 'xlsx'])
            ->name('report.low-stock.export');

        Route::get('/product-performance', [ReportController::class, 'productPerformance'])->name('report.product-performance');
        Route::get('/product-performance/export/{format}', [ReportController::class, 'exportProductPerformance'])
            ->whereIn('format', ['csv', 'xlsx'])
            ->name('report.product-performance.export');

        Route::get('/customer-purchases', [ReportController::class, 'customerPurchases'])->name('report.customer-purchases');
        Route::get('/customer-purchases/export/{format}', [ReportController::class, 'exportCustomerPurchases'])
            ->whereIn('format', ['csv', 'xlsx'])
            ->name('report.customer-purchases.export');

        Route::get('/payment-methods', [ReportController::class, 'paymentMethods'])->name('report.payment-methods');
        Route::get('/payment-methods/export/{format}', [ReportController::class, 'exportPaymentMethods'])
            ->whereIn('format', ['csv', 'xlsx'])
            ->name('report.payment-methods.export');

        Route::get('/offer-usage', [ReportController::class, 'offerUsage'])->name('report.offer-usage');
        Route::get('/offer-usage/export/{format}', [ReportController::class, 'exportOfferUsage'])
            ->whereIn('format', ['csv', 'xlsx'])
            ->name('report.offer-usage.export');

        Route::get('/profitability', [ReportController::class, 'profitability'])->name('report.profitability');
        Route::get('/profitability/export/{format}', [ReportController::class, 'exportProfitability'])
            ->whereIn('format', ['csv', 'xlsx'])
            ->name('report.profitability.export');
    });
});
