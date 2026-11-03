@extends('layouts.app')

@section('content')
    <div>
        <x-common.list-header
            title="Users"
            description="Manage staff accounts and role assignments."
        >
            <x-slot:actions>
                @can('users.create')
                    <x-common.btn-primary href="{{ route('users.create') }}">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 5v14M5 12h14" stroke-linecap="round" />
                        </svg>
                        Add User
                    </x-common.btn-primary>
                @endcan
            </x-slot:actions>
        </x-common.list-header>

        <x-common.stats-row class="xl:grid-cols-1 sm:grid-cols-1">
            <x-common.stat-card label="Total Users" :value="$stats['total']" tone="brand" />
        </x-common.stats-row>

        <x-common.list-toolbar :action="route('users.index')" :reset-url="route('users.index')">
            <x-common.search-field name="search" :value="$search" placeholder="Search name or email..." />
        </x-common.list-toolbar>

        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/3">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                    <thead class="bg-gray-50 dark:bg-white/5">
                        <tr class="text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">
                            <th class="px-4 py-3">Name</th>
                            <th class="px-4 py-3">Email</th>
                            <th class="px-4 py-3">Role</th>
                            <th class="px-4 py-3 text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse ($users as $user)
                            <tr class="text-sm text-gray-700 dark:text-gray-300" data-user-row="{{ $user->id }}">
                                <td class="px-4 py-3 whitespace-nowrap font-medium text-gray-800 dark:text-white/90">{{ $user->name }}</td>
                                <td class="px-4 py-3 whitespace-nowrap">{{ $user->email }}</td>
                                <td class="px-4 py-3 whitespace-nowrap">{{ $user->roles->first()?->name ?? '—' }}</td>
                                <td class="px-4 py-3 whitespace-nowrap text-end">
                                    <div class="inline-flex items-center gap-2">
                                        @can('users.edit')
                                            <a href="{{ route('users.edit', $user) }}"
                                                class="text-brand-500 hover:text-brand-600 text-sm font-medium">Edit</a>
                                        @endcan
                                        @can('users.delete')
                                            @if (auth()->id() !== $user->id)
                                                <form method="POST" action="{{ route('users.destroy', $user) }}" data-ajax="true"
                                                    data-confirm="Delete this user?" class="inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-error-500 hover:text-error-600 text-sm font-medium">Delete</button>
                                                </form>
                                            @endif
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <x-common.empty-state
                                colspan="4"
                                title="No users found"
                                description="Try adjusting your filters or add a new user to get started."
                            >
                                <x-slot:actions>
                                    @can('users.create')
                                        <x-common.btn-primary href="{{ route('users.create') }}">Add User</x-common.btn-primary>
                                    @endcan
                                    @if ($hasFilters)
                                        <x-common.btn-secondary href="{{ route('users.index') }}">Clear Filters</x-common.btn-secondary>
                                    @endif
                                </x-slot:actions>
                            </x-common.empty-state>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($users->hasPages())
                <div class="border-t border-gray-200 px-4 py-3 dark:border-gray-800">{{ $users->links() }}</div>
            @endif
        </div>
    </div>
@endsection
