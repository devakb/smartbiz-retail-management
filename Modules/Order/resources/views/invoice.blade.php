@extends('layouts.app')

@section('content')
    <div>
        <x-common.page-breadcrumb pageTitle="Invoice" />

        <div class="mb-4 flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">
                Invoice {{ $order->invoice_number ?: $order->order_number }}
            </h3>
            <div class="flex gap-2">
                <a href="{{ route('order.invoice.pdf', $order) }}"
                    class="bg-brand-500 hover:bg-brand-600 inline-flex items-center justify-center rounded-lg px-4 py-2.5 text-sm font-medium text-white transition">
                    Download PDF
                </a>
                <a href="{{ route('order.show', $order) }}"
                    class="inline-flex items-center justify-center rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/5">
                    Back to order
                </a>
            </div>
        </div>

        @include('order::partials.invoice-body', ['order' => $order])
    </div>
@endsection
