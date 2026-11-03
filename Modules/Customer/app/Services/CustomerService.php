<?php

namespace Modules\Customer\Services;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Customer;
use App\Models\Order;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class CustomerService
{
    /**
     * @param  array{search?: string|null, is_active?: bool|null}  $filters
     */
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $search = $filters['search'] ?? null;

        return Customer::query()
            ->when($search, function ($query) use ($search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->when(
                array_key_exists('is_active', $filters) && $filters['is_active'] !== null && $filters['is_active'] !== '',
                fn ($query) => $query->where('is_active', (bool) $filters['is_active'])
            )
            ->orderByDesc('is_walk_in')
            ->orderBy('name')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Customer
    {
        return Customer::query()->create([
            'name' => $data['name'],
            'email' => $data['email'] ?? null,
            'phone' => $data['phone'] ?? null,
            'billing_address' => $data['billing_address'] ?? null,
            'shipping_address' => $data['shipping_address'] ?? null,
            'is_walk_in' => false,
            'is_active' => $data['is_active'] ?? true,
            'outstanding_balance' => 0,
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Customer $customer, array $data): Customer
    {
        if ($customer->is_walk_in && array_key_exists('is_active', $data) && ! $data['is_active']) {
            throw ValidationException::withMessages([
                'is_active' => 'The Walk-in Customer cannot be deactivated.',
            ]);
        }

        $customer->fill([
            'name' => $data['name'] ?? $customer->name,
            'email' => array_key_exists('email', $data) ? $data['email'] : $customer->email,
            'phone' => array_key_exists('phone', $data) ? $data['phone'] : $customer->phone,
            'billing_address' => array_key_exists('billing_address', $data) ? $data['billing_address'] : $customer->billing_address,
            'shipping_address' => array_key_exists('shipping_address', $data) ? $data['shipping_address'] : $customer->shipping_address,
            'is_active' => array_key_exists('is_active', $data) ? (bool) $data['is_active'] : $customer->is_active,
        ])->save();

        return $customer->refresh();
    }

    public function delete(Customer $customer): void
    {
        if ($customer->is_walk_in) {
            throw ValidationException::withMessages([
                'customer' => 'The Walk-in Customer cannot be deleted.',
            ]);
        }

        $customer->delete();
    }

    /**
     * @return array{orders: Collection<int, Order>, outstanding: float, order_count: int, total_spent: float}
     */
    public function purchaseSummary(Customer $customer): array
    {
        $orders = $customer->orders()
            ->with(['payments', 'items'])
            ->latest('id')
            ->limit(50)
            ->get();

        $outstanding = 0.0;
        $totalSpent = 0.0;

        foreach ($orders as $order) {
            if ($order->status === OrderStatus::Cancelled) {
                continue;
            }

            $paid = (float) $order->payments
                ->whereIn('status', [PaymentStatus::Completed, PaymentStatus::Paid])
                ->sum('amount');

            $total = (float) $order->total;
            $totalSpent += min($paid, $total);
            $outstanding += max(0, round($total - $paid, 2));
        }

        $customer->outstanding_balance = round($outstanding, 2);
        $customer->saveQuietly();

        return [
            'orders' => $orders,
            'outstanding' => round($outstanding, 2),
            'order_count' => $orders->where('status', '!=', OrderStatus::Cancelled)->count(),
            'total_spent' => round($totalSpent, 2),
        ];
    }
}
