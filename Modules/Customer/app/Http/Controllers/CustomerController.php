<?php

namespace Modules\Customer\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\Customer\Http\Requests\StoreCustomerRequest;
use Modules\Customer\Http\Requests\UpdateCustomerRequest;
use Modules\Customer\Services\CustomerService;

class CustomerController extends Controller
{
    public function __construct(private readonly CustomerService $customerService) {}

    public function index(Request $request): View
    {
        $filters = [
            'search' => $request->string('search')->toString() ?: null,
            'is_active' => $request->has('is_active') && $request->input('is_active') !== ''
                ? $request->boolean('is_active')
                : null,
        ];

        return view('customer::index', [
            'title' => 'Customers',
            'customers' => $this->customerService->paginate($filters),
            'search' => $filters['search'] ?? '',
            'isActive' => $request->input('is_active', ''),
            'hasFilters' => filled($filters['search'] ?? null) || $filters['is_active'] !== null,
        ]);
    }

    public function create(): View
    {
        return view('customer::create', [
            'title' => 'Create customer',
        ]);
    }

    public function store(StoreCustomerRequest $request): JsonResponse
    {
        $customer = $this->customerService->create($request->validated());

        return ApiResponse::success('Customer created successfully.', [
            'customer' => $this->transform($customer),
            'redirect' => route('customer.index'),
        ], 201);
    }

    public function show(Customer $customer): View
    {
        $summary = $this->customerService->purchaseSummary($customer);

        return view('customer::show', [
            'title' => 'Customer details',
            'customer' => $customer->fresh(),
            'orders' => $summary['orders'],
            'outstanding' => $summary['outstanding'],
            'orderCount' => $summary['order_count'],
            'totalSpent' => $summary['total_spent'],
        ]);
    }

    public function edit(Customer $customer): View
    {
        return view('customer::edit', [
            'title' => 'Edit customer',
            'customer' => $customer,
        ]);
    }

    public function update(UpdateCustomerRequest $request, Customer $customer): JsonResponse
    {
        $customer = $this->customerService->update($customer, $request->validated());

        return ApiResponse::success('Customer updated successfully.', [
            'customer' => $this->transform($customer),
            'redirect' => route('customer.index'),
        ]);
    }

    public function destroy(Customer $customer): JsonResponse
    {
        $this->customerService->delete($customer);

        return ApiResponse::success('Customer deleted successfully.', [
            'redirect' => route('customer.index'),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function transform(Customer $customer): array
    {
        return [
            'id' => $customer->id,
            'name' => $customer->name,
            'email' => $customer->email,
            'phone' => $customer->phone,
            'billing_address' => $customer->billing_address,
            'shipping_address' => $customer->shipping_address,
            'is_walk_in' => (bool) $customer->is_walk_in,
            'is_active' => (bool) $customer->is_active,
            'outstanding_balance' => $customer->outstanding_balance,
        ];
    }
}
