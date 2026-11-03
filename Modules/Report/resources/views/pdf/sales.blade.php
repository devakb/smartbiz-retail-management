<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Sales Report</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #111; }
        h1 { font-size: 18px; margin: 0 0 6px; }
        .muted { color: #666; margin-bottom: 16px; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th, td { border-bottom: 1px solid #ddd; padding: 6px 4px; text-align: left; }
        th { font-size: 10px; text-transform: uppercase; color: #666; }
        .right { text-align: right; }
        .summary { margin-top: 16px; width: 280px; margin-left: auto; }
        .summary td { border: 0; padding: 3px 0; }
    </style>
</head>
<body>
    <h1>Sales Report</h1>
    <div class="muted">
        Generated {{ now()->format('Y-m-d H:i') }}
        @if (!empty($filters['date_from']) || !empty($filters['date_to']))
            · {{ $filters['date_from'] ?? '…' }} to {{ $filters['date_to'] ?? '…' }}
        @endif
    </div>

    <table>
        <thead>
            <tr>
                <th>Order #</th>
                <th>Customer</th>
                <th>Date</th>
                <th>Status</th>
                <th class="right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($rows as $order)
                <tr>
                    <td>{{ $order->order_number }}</td>
                    <td>{{ $order->customer?->name ?? '—' }}</td>
                    <td>{{ $order->created_at?->format('Y-m-d') }}</td>
                    <td>{{ ucfirst($order->status->value) }}</td>
                    <td class="right">{{ \App\Support\Money::format($order->total) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="summary">
        <tr>
            <td>Orders</td>
            <td class="right">{{ $summary['orders_count'] }}</td>
        </tr>
        <tr>
            <td>Subtotal</td>
            <td class="right">{{ \App\Support\Money::format($summary['subtotal']) }}</td>
        </tr>
        <tr>
            <td>Discount</td>
            <td class="right">{{ \App\Support\Money::format($summary['discount']) }}</td>
        </tr>
        <tr>
            <td>Tax</td>
            <td class="right">{{ \App\Support\Money::format($summary['tax']) }}</td>
        </tr>
        <tr>
            <td><strong>Total</strong></td>
            <td class="right"><strong>{{ \App\Support\Money::format($summary['total']) }}</strong></td>
        </tr>
    </table>
</body>
</html>
