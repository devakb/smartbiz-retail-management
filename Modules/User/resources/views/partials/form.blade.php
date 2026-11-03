@php
    $selectedRole = old('role', $user?->roles->first()?->name);
@endphp

<div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
    <div>
        <label for="name" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
            Name<span class="text-error-500">*</span>
        </label>
        <input type="text" id="name" name="name" value="{{ old('name', $user?->name) }}" required
            class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" />
    </div>

    <div>
        <label for="email" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
            Email<span class="text-error-500">*</span>
        </label>
        <input type="email" id="email" name="email" value="{{ old('email', $user?->email) }}" required
            class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" />
    </div>

    <div>
        <label for="password" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
            Password
            @if (! $user)
                <span class="text-error-500">*</span>
            @else
                <span class="text-gray-400">(optional)</span>
            @endif
        </label>
        <input type="password" id="password" name="password" @required(! $user) autocomplete="new-password"
            class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" />
    </div>

    <div>
        <label for="password_confirmation" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
            Confirm password
            @if (! $user)
                <span class="text-error-500">*</span>
            @endif
        </label>
        <input type="password" id="password_confirmation" name="password_confirmation" @required(! $user) autocomplete="new-password"
            class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" />
    </div>

    <div class="sm:col-span-2">
        <label for="role" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
            Role<span class="text-error-500">*</span>
        </label>
        <select id="role" name="role" required
            class="js-tom-select"
            data-placeholder="Select a role">
            <option value="">Select a role</option>
            @foreach ($roles as $role)
                <option value="{{ $role }}" @selected($selectedRole === $role)>{{ $role }}</option>
            @endforeach
        </select>
    </div>
</div>
