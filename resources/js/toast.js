const ICONS = {
    success: 'success',
    error: 'error',
    warning: 'warning',
    info: 'info',
};

function ensureContainer() {
    let el = document.getElementById('smartbiz-toast-container');
    if (el) {
        return el;
    }

    el = document.createElement('div');
    el.id = 'smartbiz-toast-container';
    el.className = 'fixed top-4 right-4 z-[99999] flex w-full max-w-sm flex-col gap-2 px-4 sm:px-0';
    document.body.appendChild(el);

    return el;
}

function toneClasses(type) {
    const map = {
        success: 'border-success-500 bg-success-50 text-success-700 dark:bg-success-500/15 dark:text-success-400',
        error: 'border-error-500 bg-error-50 text-error-700 dark:bg-error-500/15 dark:text-error-400',
        warning: 'border-warning-500 bg-warning-50 text-warning-700 dark:bg-warning-500/15 dark:text-warning-400',
        info: 'border-brand-500 bg-brand-50 text-brand-700 dark:bg-brand-500/15 dark:text-brand-400',
    };

    return map[type] || map.info;
}

export function toast(message, type = 'info', duration = 4000) {
    const container = ensureContainer();
    const item = document.createElement('div');
    item.setAttribute('role', 'status');
    item.className = `pointer-events-auto rounded-lg border px-4 py-3 text-sm shadow-theme-md ${toneClasses(type)}`;
    item.dataset.toastType = ICONS[type] || 'info';
    item.textContent = message;

    container.appendChild(item);

    window.setTimeout(() => {
        item.classList.add('opacity-0', 'transition-opacity', 'duration-300');
        window.setTimeout(() => item.remove(), 300);
    }, duration);
}

window.SmartBizToast = toast;

export default toast;
