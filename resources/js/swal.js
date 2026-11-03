import Swal from 'sweetalert2';

window.Swal = Swal;

export async function confirmAction(message, options = {}) {
    const result = await Swal.fire({
        title: options.title || 'Are you sure?',
        text: message,
        icon: options.icon || 'warning',
        showCancelButton: true,
        confirmButtonText: options.confirmText || 'Yes, continue',
        cancelButtonText: options.cancelText || 'Cancel',
        reverseButtons: true,
        customClass: {
            popup: 'rounded-2xl',
            confirmButton: 'bg-brand-500 hover:bg-brand-600 rounded-lg px-4 py-2 text-sm font-medium text-white',
            cancelButton: 'rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700',
        },
        buttonsStyling: false,
    });

    return result.isConfirmed;
}

window.SmartBizSwal = {
    confirm: confirmAction,
    fire: (...args) => Swal.fire(...args),
};

export default confirmAction;
