<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Invoice {{ $order->invoice_number ?: $order->order_number }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #111; }
        h1 { font-size: 20px; margin: 0 0 4px; }
        .muted { color: #666; }
        .header, .meta { width: 100%; margin-bottom: 20px; }
        .header td { vertical-align: top; }
        table.items { width: 100%; border-collapse: collapse; margin: 20px 0; }
        table.items th, table.items td { border-bottom: 1px solid #ddd; padding: 8px 4px; text-align: left; }
        table.items th { font-size: 10px; text-transform: uppercase; color: #666; }
        .right { text-align: right; }
        .totals { width: 240px; margin-left: auto; }
        .totals td { padding: 4px 0; }
        .totals .grand td { border-top: 1px solid #111; font-weight: bold; padding-top: 8px; }
    </style>
</head>
<body>
    <table class="header">
        <tr>
            <td>
                <h1>SmartBiz</h1>
                <div class="muted">Tax Invoice</div>
            </td>
            <td class="right">
                <div><strong>Invoice:</strong> {{ $order->invoice_number ?: '—' }}</div>
                <div><strong>Order:</strong> {{ $order->order_number }}</div>
                <div><strong>Date:</strong> {{ $order->created_at?->format('Y-m-d') }}</div>
                <div><strong>Status:</strong> {{ ucfirst($order->status->value) }}</div>
            </td>
        </tr>
    </table>

    <table class="meta">
        <tr>
            <td width="50%">
                <strong>Bill to</strong><br>
                {{ $order->customer?->name }}<br>
                @if ($order->customer?->billing_address)
                    {!! nl2br(e($order->customer->billing_address)) !!}<br>
                @endif
                {{ $order->customer?->phone }}
                @if ($order->customer?->email)
                    <br>{{ $order->customer->email }}
                @endif
            </td>
            <td width="50%">
                <strong>Payment</strong><br>
                {{ ucfirst($order->payment_status->value) }}
            </td>
        </tr>
    </table>

    <table class="items">
        <thead>
            <tr>
                <th>Item</th>
                <th class="right">Qty</th>
                <th class="right">Price</th>
                <th class="right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($order->items as $item)
                <tr>
                    <td>
                        {{ $item->product_name }}<br>
                        <span class="muted">{{ $item->upc }} {{ $item->sku ? '/ '.$item->sku : '' }}</span>
                    </td>
                    <td class="right">{{ $item->quantity }}</td>
                    <td class="right">{{ \App\Support\Money::format($item->unit_price) }}</td>
                    <td class="right">{{ \App\Support\Money::format($item->line_total) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="totals">
        <tr>
            <td>Subtotal</td>
            <td class="right">{{ \App\Support\Money::format($order->subtotal) }}</td>
        </tr>
        <tr>
            <td>Discount</td>
            <td class="right">{{ \App\Support\Money::format($order->discount_amount) }}</td>
        </tr>
        <tr>
            <td>Tax</td>
            <td class="right">{{ \App\Support\Money::format($order->tax_amount) }}</td>
        </tr>
        <tr class="grand">
            <td>Total</td>
            <td class="right">{{ \App\Support\Money::format($order->total) }}</td>
        </tr>
    </table>
</body>
</html>
