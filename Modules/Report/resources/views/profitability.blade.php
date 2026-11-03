@extends('layouts.app')

@section('content')
    <div>
        <x-common.page-breadcrumb pageTitle="Profitability Report" />

        <div class="rounded-2xl border border-gray-200 bg-white p-5 lg:p-6 dark:border-gray-800 dark:bg-white/3">
            <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Profitability Report</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Selling revenue minus product cost on confirmed sold items.</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('report.index') }}" class="inline-flex items-center justify-center rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 dark:border-gray-700 dark:text-gray-300">All reports</a>
                    @include('report::partials.export-buttons')
                </div>
            </div>

            @include('report::partials.filters')

            <div class="mb-5 grid grid-cols-2 gap-3 md:grid-cols-4">
                <div class="rounded-xl border border-gray-200 p-4 dark:border-gray-800">
                    <p class="text-xs text-gray-500">Qty sold</p>
                    <p class="mt-1 font-semibold text-gray-800 dark:text-white/90">{{ $summary['qty_sold'] }}</p>
                </div>
                <div class="rounded-xl border border-gray-200 p-4 dark:border-gray-800">
                    <p class="text-xs text-gray-500">Revenue</p>
                    <p class="mt-1 font-semibold text-gray-800 dark:text-white/90">{{ \App\Support\Money::format($summary['revenue']) }}</p>
                </div>
                <div class="rounded-xl border border-gray-200 p-4 dark:border-gray-800">
                    <p class="text-xs text-gray-500">Cost</p>
                    <p class="mt-1 font-semibold text-gray-800 dark:text-white/90">{{ \App\Support\Money::format($summary['cost']) }}</p>
                </div>
                <div class="rounded-xl border border-gray-200 p-4 dark:border-gray-800">
                    <p class="text-xs text-gray-500">Profit</p>
                    <p class="mt-1 font-semibold text-gray-800 dark:text-white/90">{{ \App\Support\Money::format($summary['profit']) }}</p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                    <thead>
                        <tr class="text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">
                            <th class="px-3 py-3">Product</th>
                            <th class="px-3 py-3 text-right">Qty sold</th>
                            <th class="px-3 py-3 text-right">Revenue</th>
                            <th class="px-3 py-3 text-right">Cost</th>
                            <th class="px-3 py-3 text-right">Profit</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                        @forelse ($rows as $row)
                            <tr class="text-sm text-gray-700 dark:text-gray-300">
                                <td class="px-3 py-3">{{ $row->name }}</td>
                                <td class="px-3 py-3 text-right">{{ $row->qty_sold }}</td>
                                <td class="px-3 py-3 text-right">{{ \App\Support\Money::format($row->revenue) }}</td>
                                <td class="px-3 py-3 text-right">{{ \App\Support\Money::format($row->cost) }}</td>
                                <td class="px-3 py-3 text-right">{{ \App\Support\Money::format($row->profit) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-3 py-6 text-center text-sm text-gray-500">No profitability data.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">{{ $rows->links() }}</div>
        </div>
    </div>
@endsection
