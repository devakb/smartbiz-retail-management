@extends('layouts.app')

@section('content')
    {{-- Defined inline so it exists before Alpine (deferred module) initializes. --}}
    <script>
        window.__posConfig = @js([
            'customers' => $customers,
            'walkInId' => $walkInId,
            'categories' => $categories,
            'brands' => $brands,
            'currency' => $currency,
            'routes' => $routes,
            'csrf' => csrf_token(),
        ]);
    </script>
    <div
        x-data="posApp(window.__posConfig)"
        class="-mx-4 -mt-4 lg:-mx-6 lg:-mt-6"
    >
        <div class="border-b border-gray-200 bg-white px-4 py-3 dark:border-gray-800 dark:bg-white/3 sm:px-6">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h1 class="text-xl font-semibold text-gray-800 dark:text-white/90">Point of Sale</h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Scan UPC or search products — totals recalculate on checkout.</p>
                </div>
                <div class="flex items-center gap-2">
                    <span class="rounded-lg bg-gray-100 px-3 py-1.5 text-xs font-medium text-gray-600 dark:bg-white/5 dark:text-gray-300"
                        x-text="cartCount + ' item' + (cartCount === 1 ? '' : 's')"></span>
                    <button type="button" @click="clearCart()"
                        class="inline-flex items-center justify-center rounded-lg border border-gray-300 px-3 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/5"
                        :disabled="lines.length === 0">
                        Clear
                    </button>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-0 xl:grid-cols-12">
            {{-- Left: scanner + search --}}
            <div class="space-y-4 border-b border-gray-200 p-4 dark:border-gray-800 sm:p-6 xl:col-span-7 xl:border-b-0 xl:border-r">
                <div>
                    <label for="pos-scanner" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                        Scanner / UPC
                    </label>
                    <input
                        id="pos-scanner"
                        type="text"
                        x-ref="scanner"
                        x-model="scannerInput"
                        @keydown.enter.prevent="scanUpc()"
                        autocomplete="off"
                        autofocus
                        placeholder="Scan barcode and press Enter"
                        class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-12 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-base text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"
                    />
                    <p class="mt-1.5 text-xs text-gray-500">Focus stays on the scanner for rapid checkout.</p>
                </div>

                <div class="grid grid-cols-1 gap-3 md:grid-cols-12">
                    <div class="md:col-span-5">
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Search</label>
                        <input
                            type="search"
                            x-model="searchQuery"
                            @input.debounce.300ms="searchProducts()"
                            placeholder="Name, SKU, or UPC"
                            class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"
                        />
                    </div>
                    <div class="md:col-span-3">
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Category</label>
                        <select x-model="categoryId" @change="searchProducts()"
                            class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-3 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                            <option value="">All</option>
                            <template x-for="cat in categories" :key="cat.id">
                                <option :value="cat.id" x-text="cat.name"></option>
                            </template>
                        </select>
                    </div>
                    <div class="md:col-span-3">
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Brand</label>
                        <select x-model="brandId" @change="searchProducts()"
                            class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-3 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                            <option value="">All</option>
                            <template x-for="brand in brands" :key="brand.id">
                                <option :value="brand.id" x-text="brand.name"></option>
                            </template>
                        </select>
                    </div>
                    <div class="flex items-end md:col-span-1">
                        <button type="button" @click="searchProducts()"
                            class="bg-brand-500 hover:bg-brand-600 inline-flex h-11 w-full items-center justify-center rounded-lg px-3 text-sm font-medium text-white transition">
                            Go
                        </button>
                    </div>
                </div>

                <div class="overflow-hidden rounded-xl border border-gray-200 dark:border-gray-800">
                    <div class="max-h-[28rem] overflow-y-auto">
                        <template x-if="searching">
                            <p class="p-4 text-sm text-gray-500">Searching…</p>
                        </template>
                        <template x-if="!searching && searchResults.length === 0">
                            <p class="p-4 text-sm text-gray-500">No products match. Scan a UPC or refine search.</p>
                        </template>
                        <ul class="divide-y divide-gray-100 dark:divide-gray-800">
                            <template x-for="product in searchResults" :key="product.id">
                                <li class="flex items-center justify-between gap-3 px-4 py-3 hover:bg-gray-50 dark:hover:bg-white/5">
                                    <div class="min-w-0">
                                        <p class="truncate text-sm font-medium text-gray-800 dark:text-white/90" x-text="product.name"></p>
                                        <p class="truncate text-xs text-gray-500">
                                            <span x-text="product.sku || product.upc"></span>
                                            · Stock <span x-text="product.stock_quantity"></span>
                                        </p>
                                    </div>
                                    <div class="flex shrink-0 items-center gap-3">
                                        <span class="text-sm font-semibold text-gray-800 dark:text-white/90" x-text="money(product.selling_price)"></span>
                                        <button type="button" @click="addProduct(product)"
                                            class="inline-flex items-center justify-center rounded-lg border border-gray-300 px-3 py-1.5 text-xs font-medium text-gray-700 transition hover:bg-white dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/5">
                                            Add
                                        </button>
                                    </div>
                                </li>
                            </template>
                        </ul>
                    </div>
                </div>

                <div
                    x-show="flashMessage"
                    x-cloak
                    class="rounded-lg border px-4 py-3 text-sm"
                    :class="flashTone === 'error'
                        ? 'border-error-500 bg-error-50 text-error-700 dark:bg-error-500/15 dark:text-error-400'
                        : flashTone === 'success'
                            ? 'border-success-500 bg-success-50 text-success-700 dark:bg-success-500/15 dark:text-success-400'
                            : 'border-brand-500 bg-brand-50 text-brand-700 dark:bg-brand-500/15 dark:text-brand-400'"
                    x-text="flashMessage"
                ></div>
            </div>

            {{-- Right: cart --}}
            <div class="flex flex-col bg-white p-4 dark:bg-white/3 sm:p-6 xl:col-span-5">
                <div class="mb-4">
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Customer</label>
                    <select x-model="customerId"
                        class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                        <template x-for="customer in customers" :key="customer.id">
                            <option :value="customer.id"
                                x-text="customer.name + (customer.is_walk_in ? ' (Walk-in)' : '')"></option>
                        </template>
                    </select>
                </div>

                <div class="mb-4 flex gap-2">
                    <div class="flex-1">
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Coupon / offer</label>
                        <input type="text" x-model="couponCode" placeholder="Optional code"
                            class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" />
                    </div>
                    <div class="flex items-end gap-2">
                        <button type="button" @click="applyOffer()" :disabled="applyingOffer || lines.length === 0"
                            class="inline-flex h-11 items-center justify-center rounded-lg border border-gray-300 px-4 text-sm font-medium text-gray-700 transition hover:bg-gray-50 disabled:opacity-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/5">
                            Apply
                        </button>
                        <button type="button" @click="clearOffer()" x-show="appliedOffer"
                            class="inline-flex h-11 items-center justify-center rounded-lg border border-gray-300 px-3 text-sm text-error-500 dark:border-gray-700">
                            Clear
                        </button>
                    </div>
                </div>
                <p x-show="appliedOffer" class="mb-3 text-xs text-success-600 dark:text-success-400"
                    x-text="appliedOffer ? ('Applied: ' + appliedOffer.name + ' (−' + money(discountAmount) + ')') : ''"></p>

                <div class="mb-3 flex-1 overflow-hidden rounded-xl border border-gray-200 dark:border-gray-800">
                    <div class="max-h-[22rem] overflow-y-auto">
                        <template x-if="lines.length === 0">
                            <p class="p-6 text-center text-sm text-gray-500">Cart is empty. Scan or add products.</p>
                        </template>
                        <ul class="divide-y divide-gray-100 dark:divide-gray-800">
                            <template x-for="(line, index) in lines" :key="line.product_id">
                                <li class="px-4 py-3">
                                    <div class="flex items-start justify-between gap-2">
                                        <div class="min-w-0">
                                            <p class="truncate text-sm font-medium text-gray-800 dark:text-white/90" x-text="line.name"></p>
                                            <p class="text-xs text-gray-500">
                                                <span x-text="money(line.unit_price)"></span>
                                                · Tax <span x-text="line.tax_percent"></span>%
                                            </p>
                                        </div>
                                        <button type="button" @click="removeLine(index)"
                                            class="text-xs font-medium text-error-500 hover:underline">Remove</button>
                                    </div>
                                    <div class="mt-2 flex items-center justify-between gap-3">
                                        <div class="inline-flex items-center rounded-lg border border-gray-300 dark:border-gray-700">
                                            <button type="button" @click="changeQty(index, -1)"
                                                class="px-3 py-1.5 text-sm text-gray-700 dark:text-gray-300">−</button>
                                            <input type="number" min="0.001" step="0.001" x-model.number="line.quantity"
                                                @change="normalizeQty(index)"
                                                class="w-16 border-x border-gray-300 bg-transparent py-1.5 text-center text-sm dark:border-gray-700 dark:text-white/90" />
                                            <button type="button" @click="changeQty(index, 1)"
                                                class="px-3 py-1.5 text-sm text-gray-700 dark:text-gray-300">+</button>
                                        </div>
                                        <span class="text-sm font-semibold text-gray-800 dark:text-white/90" x-text="money(lineTotal(line))"></span>
                                    </div>
                                </li>
                            </template>
                        </ul>
                    </div>
                </div>

                <div class="space-y-2 rounded-xl border border-gray-200 bg-gray-50 p-4 text-sm dark:border-gray-800 dark:bg-white/5">
                    <div class="flex justify-between text-gray-600 dark:text-gray-300">
                        <span>Subtotal</span>
                        <span x-text="money(displaySubtotal)"></span>
                    </div>
                    <div class="flex justify-between text-gray-600 dark:text-gray-300">
                        <span>Discount</span>
                        <span x-text="money(discountAmount)"></span>
                    </div>
                    <div class="flex justify-between text-gray-600 dark:text-gray-300">
                        <span>Tax</span>
                        <span x-text="money(displayTax)"></span>
                    </div>
                    <div class="flex justify-between border-t border-gray-200 pt-2 text-base font-semibold text-gray-800 dark:border-gray-700 dark:text-white/90">
                        <span>Grand total</span>
                        <span x-text="money(displayTotal)"></span>
                    </div>
                    <p class="text-xs text-gray-500">Display only — server recalculates on checkout.</p>
                </div>

                <button type="button" @click="openCheckout()" :disabled="lines.length === 0"
                    class="bg-brand-500 hover:bg-brand-600 mt-4 inline-flex h-12 w-full items-center justify-center rounded-lg text-base font-semibold text-white transition disabled:cursor-not-allowed disabled:opacity-50">
                    Checkout
                </button>
            </div>
        </div>

        {{-- Checkout modal --}}
        <div
            x-show="checkoutOpen"
            x-cloak
            class="fixed inset-0 z-[100000] flex items-center justify-center p-4"
            @keydown.escape.window="!submitting && (checkoutOpen = false)"
        >
            <div class="absolute inset-0 bg-gray-900/50" @click="!submitting && (checkoutOpen = false)"></div>
            <div class="relative w-full max-w-md rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-lg dark:border-gray-800 dark:bg-gray-900 sm:p-6">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Complete payment</h3>
                <p class="mt-1 text-sm text-gray-500">
                    Total due: <span class="font-semibold text-gray-800 dark:text-white/90" x-text="money(displayTotal)"></span>
                </p>

                <div class="mt-4 space-y-4">
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Payment method</label>
                        <div class="grid grid-cols-3 gap-2">
                            <template x-for="method in ['cash', 'card', 'upi']" :key="method">
                                <button type="button" @click="paymentMethod = method"
                                    class="rounded-lg border px-3 py-2 text-sm font-medium capitalize transition"
                                    :class="paymentMethod === method
                                        ? 'border-brand-500 bg-brand-50 text-brand-700 dark:bg-brand-500/15 dark:text-brand-400'
                                        : 'border-gray-300 text-gray-700 dark:border-gray-700 dark:text-gray-300'">
                                    <span x-text="method.toUpperCase()"></span>
                                </button>
                            </template>
                        </div>
                    </div>

                    <div x-show="paymentMethod === 'cash'">
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Amount received</label>
                        <input type="number" min="0" step="0.01" x-model.number="amountReceived"
                            class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" />
                        <p class="mt-1.5 text-sm text-gray-600 dark:text-gray-300">
                            Change: <span class="font-semibold" x-text="money(changeAmount)"></span>
                        </p>
                    </div>

                    <div x-show="paymentMethod === 'card' || paymentMethod === 'upi'">
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                            Reference <span class="text-error-500">*</span>
                        </label>
                        <input type="text" x-model="paymentReference" placeholder="Txn / approval ref"
                            class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" />
                    </div>
                </div>

                <div class="mt-6 flex gap-3">
                    <button type="button" @click="checkoutOpen = false" :disabled="submitting"
                        class="inline-flex h-11 flex-1 items-center justify-center rounded-lg border border-gray-300 text-sm font-medium text-gray-700 transition hover:bg-gray-50 disabled:opacity-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/5">
                        Cancel
                    </button>
                    <button type="button" @click="submitCheckout()" :disabled="submitting"
                        class="bg-brand-500 hover:bg-brand-600 inline-flex h-11 flex-1 items-center justify-center rounded-lg text-sm font-semibold text-white transition disabled:cursor-not-allowed disabled:opacity-60">
                        <span x-text="submitting ? 'Processing…' : 'Pay & print'"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        window.posApp = function posApp(config) {
            return {
                customers: config.customers || [],
                categories: config.categories || [],
                brands: config.brands || [],
                currency: config.currency || { symbol: '₹', decimals: 2, position: 'before' },
                routes: config.routes || {},
                csrf: config.csrf,

                scannerInput: '',
                searchQuery: '',
                categoryId: '',
                brandId: '',
                searchResults: [],
                searching: false,
                lines: [],
                customerId: config.walkInId ? String(config.walkInId) : '',
                couponCode: '',
                appliedOffer: null,
                discountAmount: 0,
                applyingOffer: false,

                checkoutOpen: false,
                paymentMethod: 'cash',
                amountReceived: 0,
                paymentReference: '',
                checkoutToken: '',
                submitting: false,

                flashMessage: '',
                flashTone: 'info',

                init() {
                    this.$nextTick(() => this.focusScanner());
                    this.searchProducts();
                },

                get cartCount() {
                    return this.lines.reduce((sum, line) => sum + Number(line.quantity || 0), 0);
                },

                get displaySubtotal() {
                    return this.round2(this.lines.reduce((sum, line) => {
                        return sum + (Number(line.unit_price) * Number(line.quantity));
                    }, 0));
                },

                get displayTax() {
                    return this.round2(this.lines.reduce((sum, line) => {
                        const base = Number(line.unit_price) * Number(line.quantity);
                        return sum + (base * (Number(line.tax_percent) / 100));
                    }, 0));
                },

                get displayTotal() {
                    return this.round2(Math.max(0, this.displaySubtotal - Number(this.discountAmount || 0) + this.displayTax));
                },

                get changeAmount() {
                    if (this.paymentMethod !== 'cash') return 0;
                    return this.round2(Math.max(0, Number(this.amountReceived || 0) - this.displayTotal));
                },

                money(amount) {
                    const decimals = Number(this.currency.decimals ?? 2);
                    const formatted = Number(amount || 0).toLocaleString(undefined, {
                        minimumFractionDigits: decimals,
                        maximumFractionDigits: decimals,
                    });
                    const symbol = this.currency.symbol || '₹';
                    return this.currency.position === 'after' ? formatted + symbol : symbol + formatted;
                },

                round2(value) {
                    return Math.round((Number(value) + Number.EPSILON) * 100) / 100;
                },

                lineTotal(line) {
                    const base = Number(line.unit_price) * Number(line.quantity);
                    const tax = base * (Number(line.tax_percent) / 100);
                    return this.round2(base + tax);
                },

                focusScanner() {
                    this.$refs.scanner?.focus();
                },

                notify(message, tone = 'info') {
                    this.flashMessage = message;
                    this.flashTone = tone;
                    if (window.SmartBizToast) {
                        window.SmartBizToast(message, tone === 'error' ? 'error' : tone === 'success' ? 'success' : 'info');
                    }
                },

                async scanUpc() {
                    const upc = String(this.scannerInput || '').trim().replace(/\s+/g, '');
                    this.scannerInput = '';
                    if (!upc) {
                        this.focusScanner();
                        return;
                    }

                    try {
                        const response = await fetch(this.routes.scan + '?upc=' + encodeURIComponent(upc), {
                            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                            credentials: 'same-origin',
                        });
                        const payload = await response.json();
                        if (!response.ok || !payload.success) {
                            const msg = payload.message
                                || payload.errors?.upc?.[0]
                                || 'Unable to scan product.';
                            this.notify(msg, 'error');
                            this.focusScanner();
                            return;
                        }
                        this.addProduct(payload.data.product, true);
                        this.notify('Added ' + payload.data.product.name, 'success');
                    } catch (e) {
                        this.notify('Scan failed. Check connection.', 'error');
                    }
                    this.focusScanner();
                },

                async searchProducts() {
                    this.searching = true;
                    try {
                        const params = new URLSearchParams();
                        if (this.searchQuery) params.set('search', this.searchQuery);
                        if (this.categoryId) params.set('category_id', this.categoryId);
                        if (this.brandId) params.set('brand_id', this.brandId);
                        const response = await fetch(this.routes.search + '?' + params.toString(), {
                            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                            credentials: 'same-origin',
                        });
                        const payload = await response.json();
                        this.searchResults = payload.data?.products || [];
                    } catch (e) {
                        this.searchResults = [];
                    } finally {
                        this.searching = false;
                    }
                },

                addProduct(product, fromScan = false) {
                    if (!product?.id) return;
                    if (product.is_active === false) {
                        this.notify('This product is inactive and cannot be sold.', 'error');
                        return;
                    }
                    if (Number(product.stock_quantity) <= 0) {
                        this.notify('This product is out of stock.', 'error');
                        return;
                    }

                    const existing = this.lines.find((line) => Number(line.product_id) === Number(product.id));
                    if (existing) {
                        const nextQty = this.roundQty(Number(existing.quantity) + 1);
                        if (nextQty > Number(product.stock_quantity) && Number(product.stock_quantity) > 0) {
                            this.notify('Cannot exceed available stock (' + product.stock_quantity + ').', 'error');
                            return;
                        }
                        existing.quantity = nextQty;
                        existing.stock_quantity = product.stock_quantity;
                    } else {
                        this.lines.push({
                            product_id: product.id,
                            name: product.name,
                            upc: product.upc,
                            sku: product.sku,
                            unit_price: Number(product.selling_price),
                            tax_percent: Number(product.tax_percent),
                            quantity: 1,
                            category_id: product.category_id,
                            stock_quantity: product.stock_quantity,
                        });
                    }

                    if (!fromScan) {
                        this.notify('Added ' + product.name, 'success');
                    }
                    this.clearOffer(true);
                },

                changeQty(index, delta) {
                    const line = this.lines[index];
                    if (!line) return;
                    const next = this.roundQty(Number(line.quantity) + delta);
                    if (next <= 0) {
                        this.removeLine(index);
                        return;
                    }
                    if (line.stock_quantity != null && next > Number(line.stock_quantity)) {
                        this.notify('Cannot exceed available stock (' + line.stock_quantity + ').', 'error');
                        return;
                    }
                    line.quantity = next;
                    this.clearOffer(true);
                },

                normalizeQty(index) {
                    const line = this.lines[index];
                    if (!line) return;
                    let qty = this.roundQty(Number(line.quantity));
                    if (!qty || qty <= 0) {
                        this.removeLine(index);
                        return;
                    }
                    if (line.stock_quantity != null && qty > Number(line.stock_quantity)) {
                        qty = this.roundQty(Number(line.stock_quantity));
                        this.notify('Quantity capped to available stock.', 'error');
                    }
                    line.quantity = qty;
                    this.clearOffer(true);
                },

                removeLine(index) {
                    this.lines.splice(index, 1);
                    this.clearOffer(true);
                    this.focusScanner();
                },

                clearCart() {
                    this.lines = [];
                    this.clearOffer(true);
                    this.focusScanner();
                },

                roundQty(value) {
                    return Math.round((Number(value) + Number.EPSILON) * 1000) / 1000;
                },

                clearOffer(silent = false) {
                    this.appliedOffer = null;
                    this.discountAmount = 0;
                    if (!silent) {
                        this.couponCode = '';
                    }
                },

                async applyOffer() {
                    if (!this.couponCode || this.lines.length === 0) return;
                    this.applyingOffer = true;
                    try {
                        const body = {
                            customer_id: this.customerId || null,
                            subtotal: this.displaySubtotal,
                            coupon_code: this.couponCode,
                            items: this.lines.map((line) => ({
                                product_id: line.product_id,
                                category_id: line.category_id,
                                line_subtotal: this.round2(Number(line.unit_price) * Number(line.quantity)),
                            })),
                        };
                        const response = await fetch(this.routes.validateOffer, {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json',
                                'Content-Type': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-TOKEN': this.csrf,
                            },
                            credentials: 'same-origin',
                            body: JSON.stringify(body),
                        });
                        const payload = await response.json();
                        if (!response.ok || !payload.success) {
                            this.clearOffer(true);
                            this.notify(payload.message || payload.errors?.offer?.[0] || 'Offer not valid.', 'error');
                            return;
                        }
                        this.appliedOffer = payload.data.offer;
                        this.discountAmount = Number(payload.data.discount_amount || 0);
                        this.notify(payload.message || 'Offer applied.', 'success');
                    } catch (e) {
                        this.notify('Could not validate offer.', 'error');
                    } finally {
                        this.applyingOffer = false;
                    }
                },

                newCheckoutToken() {
                    if (window.crypto?.randomUUID) {
                        return window.crypto.randomUUID();
                    }
                    return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, (c) => {
                        const r = Math.random() * 16 | 0;
                        const v = c === 'x' ? r : (r & 0x3 | 0x8);
                        return v.toString(16);
                    });
                },

                openCheckout() {
                    if (this.lines.length === 0) return;
                    this.paymentMethod = 'cash';
                    this.amountReceived = this.displayTotal;
                    this.paymentReference = '';
                    this.checkoutToken = this.newCheckoutToken();
                    this.checkoutOpen = true;
                },

                async submitCheckout() {
                    if (this.submitting) return;

                    if (this.paymentMethod === 'cash') {
                        if (Number(this.amountReceived) < this.displayTotal) {
                            this.notify('Amount received must cover the total.', 'error');
                            return;
                        }
                    } else if (!String(this.paymentReference || '').trim()) {
                        this.notify('Payment reference is required.', 'error');
                        return;
                    }

                    this.submitting = true;
                    try {
                        const payload = {
                            checkout_token: this.checkoutToken,
                            customer_id: Number(this.customerId),
                            coupon_code: this.appliedOffer ? (this.couponCode || this.appliedOffer.coupon_code) : null,
                            offer_id: this.appliedOffer?.id || null,
                            items: this.lines.map((line) => ({
                                product_id: line.product_id,
                                quantity: line.quantity,
                            })),
                            subtotal: this.displaySubtotal,
                            tax_amount: this.displayTax,
                            discount_amount: this.discountAmount,
                            total: this.displayTotal,
                            payment: {
                                method: this.paymentMethod,
                                amount_received: this.paymentMethod === 'cash' ? this.amountReceived : this.displayTotal,
                                reference: this.paymentMethod === 'cash' ? null : String(this.paymentReference).trim(),
                            },
                        };

                        const response = await fetch(this.routes.checkout, {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json',
                                'Content-Type': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-TOKEN': this.csrf,
                            },
                            credentials: 'same-origin',
                            body: JSON.stringify(payload),
                        });
                        const result = await response.json();

                        if (!response.ok || !result.success) {
                            const firstError = result.errors
                                ? Object.values(result.errors).flat()[0]
                                : null;
                            this.notify(firstError || result.message || 'Checkout failed.', 'error');
                            if (!result.errors?.checkout_token) {
                                this.checkoutToken = this.newCheckoutToken();
                            }
                            return;
                        }

                        this.notify(result.message || 'Sale completed.', 'success');
                        this.checkoutOpen = false;
                        this.clearCart();
                        this.couponCode = '';
                        this.focusScanner();

                        const printUrl = result.data?.print_url;
                        if (printUrl) {
                            window.open(printUrl, '_blank');
                        }
                    } catch (e) {
                        this.notify('Checkout failed. Please try again.', 'error');
                        this.checkoutToken = this.newCheckoutToken();
                    } finally {
                        this.submitting = false;
                    }
                },
            };
        };
    </script>
@endpush
