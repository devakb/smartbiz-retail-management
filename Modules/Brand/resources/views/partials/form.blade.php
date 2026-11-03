<div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
    <div>
        <label for="name" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
            Name<span class="text-error-500">*</span>
        </label>
        <input type="text" id="name" name="name" value="{{ old('name', $brand?->name) }}" required maxlength="255"
            class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" />
    </div>

    <div>
        <label for="logo" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
            Logo
        </label>
        <input type="file" id="logo" name="logo" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
            class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 block w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" />
        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">JPG, PNG, or WebP. Max 2MB.</p>

        @if ($brand?->logo)
            <div class="mt-3">
                <img src="{{ Storage::disk('public')->url($brand->logo) }}" alt="{{ $brand->name }}"
                    class="h-16 w-16 rounded-lg object-cover" />
            </div>
        @endif
    </div>

    <div class="sm:col-span-2">
        <label for="description" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
            Description
        </label>
        <textarea id="description" name="description" rows="4"
            class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">{{ old('description', $brand?->description) }}</textarea>
    </div>

    <div class="sm:col-span-2">
        <label class="inline-flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-400">
            <input type="hidden" name="is_active" value="0" />
            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $brand?->is_active ?? true))
                class="text-brand-500 focus:ring-brand-500/20 h-4 w-4 rounded border-gray-300 dark:border-gray-700" />
            Active
        </label>
    </div>
</div>
