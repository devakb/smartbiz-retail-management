document.addEventListener('DOMContentLoaded', () => {
    if (!window.SmartBizValidation) {
        return;
    }

    if (document.querySelector('#profile-form')) {
        window.SmartBizValidation.init('#profile-form', {
            name: { required: true, maxlength: 255 },
            email: { required: true, email: true, maxlength: 255 },
        });
    }

    if (document.querySelector('#password-form')) {
        window.SmartBizValidation.init('#password-form', {
            current_password: { required: true },
            password: { required: true, minlength: 8 },
            password_confirmation: { required: true, equalTo: '#password' },
        });
    }
});
