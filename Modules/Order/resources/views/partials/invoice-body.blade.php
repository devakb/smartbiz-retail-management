<div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/3">
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:justify-between">
        <div>
            <h2 class="text-xl font-bold text-gray-900 dark:text-white">SmartBiz</h2>
            <p class="text-sm text-gray-500">Tax Invoice</p>
        </div>
        <div class="text-sm text-gray-700 dark:text-gray-300 sm:text-right">
            <p><strong>Invoice:</strong> {{ $order->invoice_number ?: '—' }}</p>
            <p><strong>Order:</strong> {{ $order->order_number }}</p>
            <p><strong>Date:</strong> {{ $order->created_at?->format('Y-m-d') }}</p>
            <p><strong>Status:</strong> {{ ucfirst($order->status->value) }}</p>
        </div>
    </div>

    <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 text-sm">
        <div>
            <p class="mb-1 font-semibold text-gray-800 dark:text-white/90">Bill to</p>
            <p class="text-gray-700 dark:text-gray-300">{{ $order->customer?->name }}</p>
            @if ($order->customer?->billing_address)
                <p class="whitespace-pre-line text-gray-500">{{ $order->customer->billing_address }}</p>
            @endif
            @if ($order->customer?->phone)
                <p class="text-gray-500">{{ $order->customer->phone }}</p>
            @endif
            @if ($order->customer?->email)
                <p class="text-gray-500">{{ $order->customer->email }}</p>
            @endif
        </div>
        <div>
            <p class="mb-1 font-semibold text-gray-800 dark:text-white/90">Payment</p>
            <p class="text-gray-700 dark:text-gray-300">{{ ucfirst($order->payment_status->value) }}</p>
        </div>
    </div>

    <table class="mb-6 min-w-full divide-y divide-gray-200 text-sm dark:divide-gray-800">
        <thead>
            <tr class="text-left text-xs font-medium tracking-wider text-gray-500 uppercase">
                <th class="py-2 pr-3">Item</th>
                <th class="py-2 pr-3 text-right">Qty</th>
                <th class="py-2 pr-3 text-right">Price</th>
                <th class="py-2 text-right">Total</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
            @foreach ($order->items as $item)
                <tr class="text-gray-700 dark:text-gray-300">
                    <td class="py-2 pr-3">
                        <div class="font-medium">{{ $item->product_name }}</div>
                        <div class="text-xs text-gray-500">{{ $item->upc }} {{ $item->sku ? '/ '.$item->sku : '' }}</div>
                    </td>
                    <td class="py-2 pr-3 text-right">{{ $item->quantity }}</td>
                    <td class="py-2 pr-3 text-right">{{ \App\Support\Money::format($item->unit_price) }}</td>
                    <td class="py-2 text-right">{{ \App\Support\Money::format($item->line_total) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="ml-auto w-full max-w-xs space-y-2 text-sm">
        <div class="flex justify-between">
            <span class="text-gray-500">Subtotal</span>
            <span>{{ \App\Support\Money::format($order->subtotal) }}</span>
        </div>
        <div class="flex justify-between">
            <span class="text-gray-500">Discount</span>
            <span>{{ \App\Support\Money::format($order->discount_amount) }}</span>
        </div>
        <div class="flex justify-between">
            <span class="text-gray-500">Tax</span>
            <span>{{ \App\Support\Money::format($order->tax_amount) }}</span>
        </div>
        <div class="flex justify-between border-t border-gray-200 pt-2 text-base font-semibold dark:border-gray-800">
            <span>Total</span>
            <span>{{ \App\Support\Money::format($order->total) }}</span>
        </div>
    </div>
</div>
