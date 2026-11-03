import toast from './toast';
import { confirmAction } from './swal';

function getCsrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
}

export function jsonResponse(payload = {}, extras = {}) {
    return {
        success: Boolean(payload.success),
        message: payload.message || '',
        data: payload.data || {},
        errors: payload.errors || {},
        ...extras,
    };
}

export function applyValidationErrors($form, errors = {}) {
    if (!$form || typeof $form.find !== 'function') {
        return;
    }

    $form.find('.is-invalid').removeClass('is-invalid');
    $form.find('.js-field-error').remove();

    Object.entries(errors).forEach(([field, messages]) => {
        const message = Array.isArray(messages) ? messages[0] : messages;
        const $input = $form.find(`[name="${field}"], [name="${field}[]"]`).first();
        if (!$input.length) {
            return;
        }

        $input.addClass('is-invalid border-error-500');
        $input.after(`<p class="js-field-error mt-1 text-xs text-error-500">${message}</p>`);
    });
}

export function bindAjaxForm(selector, options = {}) {
    const $ = window.jQuery;
    if (!$) {
        return;
    }

    $(document).on('submit', selector, async function (event) {
        event.preventDefault();

        const $form = $(this);
        const validator = $form.data('validator');

        if (validator && !$form.valid()) {
            return;
        }

        const confirmMessage = $form.data('confirm');
        if (confirmMessage) {
            const confirmed = await confirmAction(String(confirmMessage), {
                title: $form.data('confirm-title') || 'Please confirm',
            });
            if (!confirmed) {
                return;
            }
        }

        if ($form.data('submitting')) {
            return;
        }

        const $submit = $form.find('[type="submit"]');
        const originalText = $submit.text();
        const method = ($form.attr('method') || 'POST').toUpperCase();
        const url = $form.attr('action');
        const formData = new FormData(this);

        $form.data('submitting', true);
        $submit.prop('disabled', true);
        if (options.loadingText) {
            $submit.text(options.loadingText);
        }

        window.axios({
            url,
            method,
            data: formData,
            headers: {
                'X-CSRF-TOKEN': getCsrfToken(),
                Accept: 'application/json',
            },
        })
            .then((response) => {
                const payload = jsonResponse(response.data);
                if (payload.message) {
                    toast(payload.message, payload.success ? 'success' : 'error');
                }
                if (typeof options.onSuccess === 'function') {
                    options.onSuccess(payload, $form, response);
                }
            })
            .catch((error) => {
                const status = error.response?.status;
                const payload = jsonResponse(error.response?.data || {
                    success: false,
                    message: 'Something went wrong. Please try again.',
                });

                if (status === 422) {
                    applyValidationErrors($form, payload.errors);
                    toast(payload.message || 'Please fix the highlighted errors.', 'warning');
                } else if (status === 403) {
                    toast(payload.message || 'You are not authorized to perform this action.', 'error');
                } else if (status === 419) {
                    toast('Your session expired. Please refresh and try again.', 'warning');
                } else {
                    toast(payload.message || 'Request failed.', 'error');
                }

                if (typeof options.onError === 'function') {
                    options.onError(payload, $form, error);
                }
            })
            .finally(() => {
                $form.data('submitting', false);
                $submit.prop('disabled', false).text(originalText);
            });
    });
}

window.SmartBizAjaxForm = {
    bind: bindAjaxForm,
    applyValidationErrors,
    jsonResponse,
};

export default bindAjaxForm;
