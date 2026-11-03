@extends('layouts.app')

@section('content')
    <div>
        <x-common.page-breadcrumb pageTitle="Offer Usage Report" />

        <div class="rounded-2xl border border-gray-200 bg-white p-5 lg:p-6 dark:border-gray-800 dark:bg-white/3">
            <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Offer Usage Report</h3>
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('report.index') }}" class="inline-flex items-center justify-center rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 dark:border-gray-700 dark:text-gray-300">All reports</a>
                    @include('report::partials.export-buttons')
                </div>
            </div>

            @include('report::partials.filters')

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                    <thead>
                        <tr class="text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">
                            <th class="px-3 py-3">Date</th>
                            <th class="px-3 py-3">Offer</th>
                            <th class="px-3 py-3">Order</th>
                            <th class="px-3 py-3">Customer</th>
                            <th class="px-3 py-3 text-right">Discount</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                        @forelse ($rows as $row)
                            <tr class="text-sm text-gray-700 dark:text-gray-300">
                                <td class="px-3 py-3">{{ $row->created_at?->format('Y-m-d H:i') }}</td>
                                <td class="px-3 py-3">{{ $row->offer?->name ?? '—' }}</td>
                                <td class="px-3 py-3">{{ $row->order?->order_number ?? '—' }}</td>
                                <td class="px-3 py-3">{{ $row->customer?->name ?? '—' }}</td>
                                <td class="px-3 py-3 text-right">{{ \App\Support\Money::format($row->discount_amount) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-3 py-6 text-center text-sm text-gray-500">No offer usages found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">{{ $rows->links() }}</div>
        </div>
    </div>
@endsection
