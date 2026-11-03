import TomSelect from 'tom-select';
import 'tom-select/dist/css/tom-select.css';

window.TomSelect = TomSelect;

const instances = new WeakMap();

export function initTomSelect(root = document) {
    root.querySelectorAll('select.js-tom-select').forEach((el) => {
        if (instances.has(el) || el.tomselect) {
            return;
        }

        const placeholder = el.getAttribute('data-placeholder') || el.querySelector('option[value=""]')?.textContent || 'Select…';
        const allowClear = el.hasAttribute('data-allow-clear');
        const isMultiple = el.hasAttribute('multiple');
        const searchable = el.hasAttribute('data-searchable');

        const ts = new TomSelect(el, {
            allowEmptyOption: true,
            create: false,
            maxOptions: null,
            maxItems: isMultiple ? null : 1,
            plugins: allowClear || isMultiple ? ['clear_button'] : [],
            placeholder,
            // Filter selects look like native dropdowns; opt-in to search with data-searchable
            controlInput: searchable || isMultiple ? undefined : null,
            hideSelected: isMultiple,
            closeAfterSelect: !isMultiple,
            render: {
                no_results() {
                    return '<div class="no-results px-3 py-2 text-sm text-gray-500">No results found</div>';
                },
            },
        });

        el.classList.add('tomselected');
        instances.set(el, ts);
    });
}

window.SmartBizTomSelect = { init: initTomSelect };

export default initTomSelect;
