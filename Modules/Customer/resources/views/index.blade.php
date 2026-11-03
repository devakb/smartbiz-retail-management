@extends('layouts.app')

@section('content')
    <div>
        <x-common.list-header
            title="Customers"
            description="Manage customer profiles, contacts, and outstanding balances."
        >
            <x-slot:actions>
                @can('customers.manage')
                    <x-common.btn-primary href="{{ route('customer.create') }}">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 5v14M5 12h14" stroke-linecap="round" />
                        </svg>
                        Add Customer
                    </x-common.btn-primary>
                @endcan
            </x-slot:actions>
        </x-common.list-header>

        <x-common.list-toolbar :action="route('customer.index')" :reset-url="route('customer.index')">
            <x-common.search-field name="search" :value="$search" placeholder="Search name, email, phone..." />
            <x-common.select-field name="is_active" placeholder="All Statuses" :allow-clear="true">
                <option value="">All Statuses</option>
                <option value="1" @selected((string) $isActive === '1')>Active</option>
                <option value="0" @selected((string) $isActive === '0')>Inactive</option>
            </x-common.select-field>
        </x-common.list-toolbar>

        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/3">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                    <thead class="bg-gray-50 dark:bg-white/5">
                        <tr class="text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">
                            <th class="px-4 py-3">Name</th>
                            <th class="px-4 py-3">Email</th>
                            <th class="px-4 py-3">Phone</th>
                            <th class="px-4 py-3">Outstanding</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3 text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse ($customers as $customer)
                            <tr class="text-sm text-gray-700 dark:text-gray-300">
                                <td class="px-4 py-3 whitespace-nowrap font-medium text-gray-800 dark:text-white/90">
                                    {{ $customer->name }}
                                    @if ($customer->is_walk_in)
                                        <span class="ms-1 text-xs text-gray-500">(Walk-in)</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">{{ $customer->email ?: '—' }}</td>
                                <td class="px-4 py-3 whitespace-nowrap">{{ $customer->phone ?: '—' }}</td>
                                <td class="px-4 py-3 whitespace-nowrap">{{ \App\Support\Money::format($customer->outstanding_balance) }}</td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <x-common.status-badge :active="(bool) $customer->is_active" />
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-end">
                                    <div class="inline-flex items-center gap-2">
                                        <a href="{{ route('customer.show', $customer) }}"
                                            class="text-brand-500 hover:text-brand-600 text-sm font-medium">View</a>
                                        <a href="{{ route('customer.edit', $customer) }}"
                                            class="text-brand-500 hover:text-brand-600 text-sm font-medium">Edit</a>
                                        @unless ($customer->is_walk_in)
                                            <form method="POST" action="{{ route('customer.destroy', $customer) }}" data-ajax="true"
                                                data-confirm="Delete this customer?" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-error-500 hover:text-error-600 text-sm font-medium">Delete</button>
                                            </form>
                                        @endunless
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <x-common.empty-state
                                colspan="6"
                                title="No customers found"
                                description="Try adjusting your filters or add a new customer to get started."
                            >
                                <x-slot:actions>
                                    @can('customers.manage')
                                        <x-common.btn-primary href="{{ route('customer.create') }}">Add Customer</x-common.btn-primary>
                                    @endcan
                                    @if ($hasFilters)
                                        <x-common.btn-secondary href="{{ route('customer.index') }}">Clear Filters</x-common.btn-secondary>
                                    @endif
                                </x-slot:actions>
                            </x-common.empty-state>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($customers->hasPages())
                <div class="border-t border-gray-200 px-4 py-3 dark:border-gray-800">{{ $customers->links() }}</div>
            @endif
        </div>
    </div>
@endsection
