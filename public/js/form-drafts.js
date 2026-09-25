/* ReproCare field-draft auto-save.
 *
 * Long clinical forms (TCL, assessments, referrals) lose everything on a
 * 419 session timeout or an interrupted field visit. This script snapshots
 * every eligible form into localStorage on input (throttled) and restores
 * it on reload, so no typed data is ever lost.
 *
 * Rules:
 *  - Passwords, files, hidden fields, _token/_method never persist.
 *  - Forms with [data-no-draft] are skipped (e.g. pure search/filter forms
 *    can opt out by adding the attribute).
 *  - Drafts clear automatically on submit (stale clinical values must
 *    never prefill a later form); the keep-alive below makes 419s during
 *    an open form nearly impossible, and the PWA outbox covers offline.
 *  - One draft per form key (method + path + form id/name).
 */
(function () {
    'use strict';

    var PREFIX = 'reprocare-form-draft:';
    var SAVE_DELAY_MS = 2500;

    function formKey(form) {
        var id = form.getAttribute('id') || form.getAttribute('name') || '';
        var action = '';
        try {
            action = new URL(form.action || window.location.href, window.location.origin).pathname;
        } catch (e) {
            action = window.location.pathname;
        }
        return PREFIX + (form.method || 'get').toLowerCase() + ':' + action + ':' + id;
    }

    function isSavable(el) {
        if (!el.name || el.disabled || el.readOnly) return false;
        if (el.type === 'password' || el.type === 'file' || el.type === 'hidden' || el.type === 'submit' || el.type === 'button') return false;
        if (el.name === '_token' || el.name === '_method') return false;
        return /^(INPUT|TEXTAREA|SELECT)$/.test(el.tagName);
    }

    function snapshot(form) {
        var data = {};
        var els = form.querySelectorAll('input, textarea, select');
        for (var i = 0; i < els.length; i++) {
            var el = els[i];
            if (!isSavable(el)) continue;
            if (el.type === 'checkbox' || el.type === 'radio') {
                data[el.name + '::' + (el.value || 'on')] = el.checked ? '1' : '0';
            } else {
                data[el.name] = el.value;
            }
        }
        try {
            localStorage.setItem(formKey(form), JSON.stringify({ saved_at: Date.now(), data: data }));
        } catch (e) { /* storage full/blocked — drafting is best-effort */ }
    }

    function restore(form) {
        var raw = null;
        try { raw = localStorage.getItem(formKey(form)); } catch (e) { return false; }
        if (!raw) return false;
        var parsed;
        try { parsed = JSON.parse(raw); } catch (e) { return false; }
        if (!parsed || !parsed.data) return false;

        var restored = 0;
        var els = form.querySelectorAll('input, textarea, select');
        for (var i = 0; i < els.length; i++) {
            var el = els[i];
            if (!isSavable(el)) continue;
            if (el.type === 'checkbox' || el.type === 'radio') {
                var k = el.name + '::' + (el.value || 'on');
                if (k in parsed.data) {
                    var shouldCheck = parsed.data[k] === '1';
                    if (el.checked !== shouldCheck) { el.checked = shouldCheck; restored++; }
                }
            } else if (parsed.data[el.name] !== undefined && !el.value) {
                el.value = parsed.data[el.name];
                if (parsed.data[el.name] !== '') restored++;
            }
        }
        return restored > 0;
    }

    function clearDraft(form) {
        try { localStorage.removeItem(formKey(form)); } catch (e) {}
    }

    function toast(message) {
        var el = document.createElement('div');
        el.textContent = message;
        el.style.cssText = 'position:fixed;bottom:24px;left:50%;transform:translateX(-50%);z-index:9999;'
            + 'background:#111827;color:#fff;font-size:.85rem;font-weight:600;'
            + 'padding:.7rem 1.2rem;border-radius:12px;box-shadow:0 8px 24px rgba(0,0,0,.25);max-width:90vw;text-align:center;';
        document.body.appendChild(el);
        setTimeout(function () { el.remove(); }, 4200);
    }

    function arm(form) {
        if (form.hasAttribute('data-no-draft')) return;
        // Skip GET/search forms — only state-changing forms need drafts.
        if ((form.method || 'get').toLowerCase() !== 'post') return;

        var timer = null;
        var queueSave = function () {
            clearTimeout(timer);
            timer = setTimeout(function () { snapshot(form); }, SAVE_DELAY_MS);
        };
        form.addEventListener('input', queueSave);
        form.addEventListener('change', queueSave);
        window.addEventListener('beforeunload', function () { snapshot(form); });

        if (restore(form)) {
            queueSave();
            toast('Unsaved draft restored — your interrupted entries are back.');
        }

        form.addEventListener('submit', function () { clearDraft(form); });
    }

    document.addEventListener('DOMContentLoaded', function () {
        var forms = document.querySelectorAll('form');
        var armed = 0;
        for (var i = 0; i < forms.length; i++) {
            var f = forms[i];
            if (f.hasAttribute('data-no-draft')) continue;
            if ((f.method || 'get').toLowerCase() !== 'post') continue;
            arm(f);
            armed++;
        }
        // Session keep-alive: while a state-changing form is open, ping
        // every 9 minutes so the session/CSRF token cannot expire mid-form
        // (the classic 419-on-submit after an interruption). Offline devices
        // simply skip the ping and rely on the PWA outbox instead.
        if (armed > 0) {
            setInterval(function () {
                if (!navigator.onLine) return;
                fetch('/api/device/session-check', { credentials: 'same-origin', headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                    .catch(function () {});
            }, 9 * 60 * 1000);
        }
    });
})();
