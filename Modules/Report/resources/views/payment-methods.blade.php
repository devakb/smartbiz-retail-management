@extends('layouts.app')

@section('content')
    <div>
        <x-common.page-breadcrumb pageTitle="Payment Method Report" />

        <div class="rounded-2xl border border-gray-200 bg-white p-5 lg:p-6 dark:border-gray-800 dark:bg-white/3">
            <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Payment Method Report</h3>
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
                            <th class="px-3 py-3">Method</th>
                            <th class="px-3 py-3 text-right">Payments</th>
                            <th class="px-3 py-3 text-right">Total amount</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                        @forelse ($rows as $row)
                            <tr class="text-sm text-gray-700 dark:text-gray-300">
                                <td class="px-3 py-3">{{ strtoupper(is_object($row->method) ? $row->method->value : $row->method) }}</td>
                                <td class="px-3 py-3 text-right">{{ $row->payments_count }}</td>
                                <td class="px-3 py-3 text-right">{{ \App\Support\Money::format($row->total_amount) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="px-3 py-6 text-center text-sm text-gray-500">No payments found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
