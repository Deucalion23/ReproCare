/* Shared mode controller. Brand choices come from the city's server settings. */
(() => {
    'use strict';
    const config = window.ReproCareAppearance;
    if (!config) return;
    const root = document.documentElement;
    const read = key => { try { return localStorage.getItem(key); } catch (_) { return null; } };
    const write = (key, value) => { try { localStorage.setItem(key, value); } catch (_) {} };

    function setMode(mode, persist = true) {
        mode = mode === 'dark' ? 'dark' : 'light';
        root.dataset.theme = mode;
        root.dataset.bsTheme = mode;
        root.classList.toggle('dark', mode === 'dark');
        if (persist) {
            write('rc_theme', mode);
            write('rc_appearance_revision', config.current.revision);
        }
        document.querySelectorAll('.theme-switch-input').forEach(el => { el.checked = mode === 'dark'; });
        document.querySelectorAll('.theme-option').forEach(el => {
            el.classList.toggle('active', el.dataset.theme === mode);
        });
        document.dispatchEvent(new CustomEvent('rc:theme-changed', { detail: { mode } }));
    }
    // A city-wide save applies on the next page load. Users can still use the
    // existing local light/dark toggle until the next city-wide appearance save.
    const mode = read('rc_appearance_revision') === config.current.revision
        ? (read('rc_theme') || config.current.mode) : config.current.mode;
    setMode(mode);
    window.setRcTheme = setMode;
    window.currentRcTheme = () => root.dataset.theme;
    window.paintRcThemeControls = () => setMode(root.dataset.theme, false);

    function applyColors(element, selection) {
        for (const slot of ['primary', 'secondary', 'accent']) {
            for (const [variant, value] of Object.entries(config.palette[selection[slot]])) {
                if (variant !== 'label') {
                    element.style.setProperty(`--theme-${slot}${variant === 'hex' ? '' : `-${variant}`}`, value);
                }
            }
        }
        element.dataset.theme = selection.mode;
        element.dataset.bsTheme = selection.mode;
    }
    window.ReproCareAppearance.applyColors = applyColors;

    document.addEventListener('DOMContentLoaded', () => {
        setMode(root.dataset.theme, false);
        const form = document.getElementById('city-appearance-form');
        if (!form) return;
        const preview = document.getElementById('appearance-preview');
        const status = document.getElementById('appearance-preview-status');
        const choice = name => {
            const input = form.querySelector(`input[name="${name}"]:checked`) || form.querySelector(`input[name="${name}"]`);
            return input ? input.value : null;
        };
        function updatePreview(changedPrimary = false) {
            const primary = choice('primary');
            const selectedSecondary = choice('secondary');
            const selection = Object.fromEntries(['primary', 'secondary', 'accent', 'mode'].map(name => [name, choice(name)]));
            if (selection.primary && selection.secondary && selection.accent && selection.mode) {
                if (preview) applyColors(preview, selection);
                if (status) status.textContent = '';
            }
        }
        form.addEventListener('change', event => updatePreview(event.target.name === 'primary'));
        document.getElementById('appearance-reset').addEventListener('click', () => {
            for (const [slot, value] of Object.entries(config.defaults)) {
                const input = form.querySelector(`input[name="${slot}"][value="${value}"]`);
                if (input) {
                    input.disabled = false;
                    input.checked = true;
                }
            }
            updatePreview();
            status.textContent = 'ReproCare defaults selected. Save Appearance to apply them city-wide.';
        });
        updatePreview(true);
        if (location.hash === '#appearance' || form.dataset.hasErrors === 'true') {
            const nav = document.querySelector('[data-appearance-nav]');
            if (nav && typeof window.showSection === 'function') window.showSection('appearance', nav);
        }
    });
})();
