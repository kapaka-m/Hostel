import * as bootstrap from 'bootstrap';
import './bootstrap';

window.bootstrap = bootstrap;

const initDropdowns = () => {
    document.querySelectorAll('[data-bs-toggle="dropdown"]').forEach((el) => {
        bootstrap.Dropdown.getOrCreateInstance(el);
    });
};

const initToasts = () => {
    document.querySelectorAll('.toast').forEach((toastEl) => {
        bootstrap.Toast.getOrCreateInstance(toastEl, { delay: 5000 }).show();
    });
};

const setThemeIcon = (toggle, theme) => {
    const icon = toggle.querySelector('[data-theme-icon]') || toggle.querySelector('i');
    if (!icon) return;

    // Clear any existing theme icon classes that may already be applied.
    icon.classList.remove(
        'bi-moon', 'bi-moon-fill', 'bi-moon-stars', 'bi-moon-stars-fill',
        'bi-sun', 'bi-sun-fill'
    );

    // Use a sun icon when the theme is dark, a moon icon when it is light.
    if (theme === 'dark') {
        icon.classList.add('bi-sun');
    } else {
        icon.classList.add('bi-moon-stars');
    }
};

const initThemeToggle = () => {
    const toggle = document.querySelector('[data-theme-toggle]');
    if (!toggle) return;

    const html = document.documentElement;

    // Apply the initial toggle state (fallback to light if no value exists).
    const initial = html.getAttribute('data-bs-theme') || toggle.dataset.themeValue || 'light';
    setThemeIcon(toggle, initial);

    toggle.addEventListener('click', async () => {
        const current = html.getAttribute('data-bs-theme') || 'light';
        const next = current === 'dark' ? 'light' : 'dark';
        const endpoint = toggle.dataset.themeEndpoint;

        html.setAttribute('data-bs-theme', next);
        toggle.dataset.themeValue = next;
        setThemeIcon(toggle, next);

        // Persist the updated theme, if an endpoint exists.
        if (!endpoint || !window.axios) return;

        try {
            await window.axios.post(endpoint, { theme: next });
        } catch (e) {
            // Restore the previous theme when persistence fails.
            html.setAttribute('data-bs-theme', current);
            toggle.dataset.themeValue = current;
            setThemeIcon(toggle, current);
        }
    });
};

const initConfirmModal = () => {
    const modalEl = document.getElementById('confirmModal');
    if (!modalEl || !window.bootstrap) return;

    const modal = new window.bootstrap.Modal(modalEl);
    const textEl = document.getElementById('confirmModalText');
    const yesBtn = document.getElementById('confirmModalYes');

    let targetForm = null;

    document.addEventListener('click', (e) => {
        const btn = e.target.closest('.js-confirm');
        if (!btn) return;

        e.preventDefault(); // Delay the default submit until the user confirms.

        targetForm = btn.closest('form');
        if (!targetForm) return;

        textEl.textContent = btn.dataset.confirm || 'Are you sure?';
        modal.show();
    });

    yesBtn.addEventListener('click', () => {
        if (targetForm) targetForm.submit(); // Submit the form from the modal.
    });

    modalEl.addEventListener('hidden.bs.modal', () => {
        targetForm = null;
    });
};

document.addEventListener('DOMContentLoaded', () => {
    initDropdowns();
    initToasts();
    initThemeToggle();
    initConfirmModal();
});
