@php
    $authUser = auth()->user();
@endphp

<x-ui.dropdown-menu>
    <x-ui.dropdown-menu.trigger class="dropdown-toggle flex items-center text-gray-700 dark:text-gray-400">
        <span class="me-3 flex h-11 w-11 items-center justify-center overflow-hidden rounded-lg bg-brand-500/10 text-sm font-semibold text-brand-600 dark:text-brand-400">
            {{ strtoupper(substr($authUser?->name ?? 'U', 0, 1)) }}
        </span>

        <span class="me-1 block text-sm font-medium">{{ $authUser?->name ?? 'User' }}</span>

        <x-svg.chevron-down class="size-5 text-gray-500 duration-200 dark:text-gray-400" x-bind:class="open ? 'rotate-180' : 'rotate-0'" />
    </x-ui.dropdown-menu.trigger>

    <x-ui.dropdown-menu.content align="end" :side-offset="12" class="w-65 p-3">
        <div class="px-3 py-2">
            <span class="block text-sm font-medium text-gray-700 dark:text-gray-400">{{ $authUser?->name }}</span>
            <span class="mt-0.5 block text-xs text-gray-500 dark:text-gray-400">{{ $authUser?->email }}</span>
        </div>

        <x-ui.dropdown-menu.separator />

        <x-ui.dropdown-menu.item to="{{ route('profile') }}">
            <x-svg.user-circle class="size-6" />
            Profile
        </x-ui.dropdown-menu.item>

        <x-ui.dropdown-menu.separator />

        <form method="POST" action="{{ route('logout') }}" class="w-full">
            @csrf
            <button type="submit" role="menuitem" data-slot="dropdown-menu-item" data-variant="destructive"
                class="group/dropdown-menu-item relative flex w-full cursor-pointer items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium text-red-500 outline-hidden transition-colors duration-200 select-none hover:bg-red-50 hover:text-red-600 focus:bg-red-50 focus:text-red-600 dark:text-red-400 dark:hover:bg-red-500/10 dark:hover:text-red-400 [&_svg]:pointer-events-none [&_svg]:size-5 [&_svg]:shrink-0"
                x-on:click="typeof closeAll === 'function' && closeAll()">
                <x-svg.log-out />
                Logout
            </button>
        </form>
    </x-ui.dropdown-menu.content>
</x-ui.dropdown-menu>
