<div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
    <div>
        <label for="name" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
            Name<span class="text-error-500">*</span>
        </label>
        <input type="text" id="name" name="name" value="{{ old('name', $customer?->name) }}" required maxlength="255"
            class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" />
    </div>

    <div>
        <label for="email" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Email</label>
        <input type="email" id="email" name="email" value="{{ old('email', $customer?->email) }}" maxlength="255"
            class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" />
    </div>

    <div>
        <label for="phone" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Phone</label>
        <input type="text" id="phone" name="phone" value="{{ old('phone', $customer?->phone) }}" maxlength="50"
            class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" />
    </div>

    <div class="sm:col-span-2">
        <label for="billing_address" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Billing address</label>
        <textarea id="billing_address" name="billing_address" rows="3"
            class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">{{ old('billing_address', $customer?->billing_address) }}</textarea>
    </div>

    <div class="sm:col-span-2">
        <label for="shipping_address" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Shipping address</label>
        <textarea id="shipping_address" name="shipping_address" rows="3"
            class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">{{ old('shipping_address', $customer?->shipping_address) }}</textarea>
    </div>

    <div class="sm:col-span-2">
        <label class="inline-flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-400">
            <input type="hidden" name="is_active" value="0" />
            <input type="checkbox" name="is_active" value="1"
                @checked(old('is_active', $customer?->is_active ?? true))
                @disabled($customer?->is_walk_in)
                class="text-brand-500 focus:ring-brand-500/20 h-4 w-4 rounded border-gray-300 dark:border-gray-700" />
            Active
            @if ($customer?->is_walk_in)
                <span class="text-xs text-gray-500">(Walk-in cannot be deactivated)</span>
            @endif
        </label>
    </div>
</div>
