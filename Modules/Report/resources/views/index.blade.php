@extends('layouts.app')

@section('content')
    <div>
        <x-common.page-breadcrumb pageTitle="Reports" />

        <div class="mb-5">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Business reports</h3>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Filter by date, product, category, brand, customer, payment method, and status where applicable.</p>
        </div>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
            @foreach ($reports as $report)
                <a href="{{ route($report['route']) }}"
                    class="rounded-2xl border border-gray-200 bg-white p-5 transition hover:border-brand-300 dark:border-gray-800 dark:bg-white/3 dark:hover:border-brand-500">
                    <h4 class="text-base font-semibold text-gray-800 dark:text-white/90">{{ $report['name'] }}</h4>
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">{{ $report['description'] }}</p>
                </a>
            @endforeach
        </div>
    </div>
@endsection
