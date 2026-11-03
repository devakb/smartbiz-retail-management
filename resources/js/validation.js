import $ from 'jquery';
import 'jquery-validation';

window.$ = window.jQuery = $;

$.validator.setDefaults({
    errorElement: 'p',
    errorClass: 'js-field-error mt-1 text-xs text-error-500',
    highlight(element) {
        $(element).addClass('is-invalid border-error-500');
    },
    unhighlight(element) {
        $(element).removeClass('is-invalid border-error-500');
    },
    errorPlacement(error, element) {
        error.insertAfter(element);
    },
});

export function initFormValidation(selector, rules = {}, messages = {}) {
    const $form = $(selector);
    if (!$form.length) {
        return null;
    }

    return $form.validate({
        rules,
        messages,
    });
}

window.SmartBizValidation = {
    init: initFormValidation,
};

export default initFormValidation;
