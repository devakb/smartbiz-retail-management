<script>
    document.addEventListener('DOMContentLoaded', function () {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

        function toast(message, type = 'info') {
            if (window.SmartBizToast) {
                window.SmartBizToast(message, type);
            }
        }

        function clearFieldErrors(form) {
            form.querySelectorAll('.is-invalid').forEach((el) => {
                el.classList.remove('is-invalid', 'border-error-500');
            });
            form.querySelectorAll('.js-field-error').forEach((el) => el.remove());
        }

        function applyFieldErrors(form, errors = {}) {
            clearFieldErrors(form);

            Object.entries(errors).forEach(([field, messages]) => {
                const message = Array.isArray(messages) ? messages[0] : messages;
                const input = form.querySelector(`[name="${field}"]`);
                if (!input) {
                    return;
                }

                input.classList.add('is-invalid', 'border-error-500');
                const error = document.createElement('p');
                error.className = 'js-field-error mt-1 text-xs text-error-500';
                error.textContent = message;
                input.insertAdjacentElement('afterend', error);
            });
        }

        function closeDialogFor(form) {
            const alpineData = window.Alpine?.$data?.(form);
            if (alpineData && typeof alpineData.close === 'function') {
                alpineData.close();
            }
        }

        function selectCreatedOption(selectId, id, name) {
            const select = document.getElementById(selectId);
            if (!select) {
                return;
            }

            const value = String(id);
            const existing = Array.from(select.options).find((option) => option.value === value);
            if (!existing) {
                const option = new Option(name, value, true, true);
                select.add(option);
            }

            if (select.tomselect) {
                if (!select.tomselect.options[value]) {
                    select.tomselect.addOption({ value, text: name });
                }
                select.tomselect.addItem(value, true);
            } else {
                select.value = value;
            }
        }

        function appendParentOption(id, name) {
            const parentSelect = document.getElementById('quick_category_parent_id');
            if (!parentSelect) {
                return;
            }

            const value = String(id);
            if (Array.from(parentSelect.options).some((option) => option.value === value)) {
                return;
            }

            parentSelect.add(new Option(name, value));
        }

        async function submitQuickAdd(form, { entityKey, selectId, onCreated }) {
            clearFieldErrors(form);

            if (form.dataset.submitting === '1') {
                return;
            }

            const submitButton = form.querySelector('[type="submit"]');
            const originalText = submitButton?.textContent || '';

            form.dataset.submitting = '1';
            if (submitButton) {
                submitButton.disabled = true;
                submitButton.textContent = @json(__('Please wait...'));
            }

            try {
                const response = await window.axios({
                    url: form.action,
                    method: 'POST',
                    data: new FormData(form),
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });

                const payload = response.data || {};
                const entity = payload.data?.[entityKey];

                if (entity?.id && entity?.name) {
                    selectCreatedOption(selectId, entity.id, entity.name);
                    onCreated?.(entity);
                    form.reset();
                    const activeInput = form.querySelector('[name="is_active"]');
                    if (activeInput) {
                        activeInput.value = '1';
                    }
                    closeDialogFor(form);
                }

                toast(payload.message || @json(__('Saved successfully.')), 'success');
            } catch (error) {
                const status = error.response?.status;
                const payload = error.response?.data || {};

                if (status === 422) {
                    applyFieldErrors(form, payload.errors || {});
                    toast(payload.message || @json(__('Please fix the highlighted errors.')), 'warning');
                } else if (status === 403) {
                    toast(payload.message || @json(__('You are not authorized to perform this action.')), 'error');
                } else {
                    toast(payload.message || @json(__('Request failed.')), 'error');
                }
            } finally {
                form.dataset.submitting = '0';
                if (submitButton) {
                    submitButton.disabled = false;
                    submitButton.textContent = originalText;
                }
            }
        }

        document.getElementById('quick-add-category-form')?.addEventListener('submit', function (event) {
            event.preventDefault();
            submitQuickAdd(this, {
                entityKey: 'category',
                selectId: 'category_id',
                onCreated(category) {
                    appendParentOption(category.id, category.name);
                },
            });
        });

        document.getElementById('quick-add-brand-form')?.addEventListener('submit', function (event) {
            event.preventDefault();
            submitQuickAdd(this, {
                entityKey: 'brand',
                selectId: 'brand_id',
            });
        });
    });
</script>
