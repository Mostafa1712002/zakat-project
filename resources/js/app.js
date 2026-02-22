import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

const isDataTable = (tableElement) => {
    if (tableElement.dataset.noResponsive === 'true') {
        return false;
    }

    if (tableElement.classList.contains('table') || tableElement.classList.contains('items-table')) {
        return true;
    }

    return Boolean(tableElement.querySelector('thead'));
};

const wrapResponsiveTables = () => {
    document.querySelectorAll('main table').forEach((tableElement) => {
        if (!isDataTable(tableElement)) {
            return;
        }

        if (tableElement.closest('.table-container') || tableElement.closest('.table-container-auto')) {
            return;
        }

        const wrapper = document.createElement('div');
        wrapper.className = 'table-container table-container-auto';
        tableElement.parentNode.insertBefore(wrapper, tableElement);
        wrapper.appendChild(tableElement);
    });
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', wrapResponsiveTables);
} else {
    wrapResponsiveTables();
}

Alpine.start();
