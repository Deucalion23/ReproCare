/* Shared "pick a person" combobox: one field that searches and drops down a
   live list at the same time. The page keeps its own <select class="rc-combo-store">
   as the value store, so name/old()/@error and server validation are untouched —
   the select is only visible before this script initialises (no-JS fallback).

   Markup contract, inside [data-combo]:
     .rc-combo-input   text input, carries `required` when the field is mandatory
     .rc-combo-toggle  chevron button
     .rc-combo-list    the dropdown panel
     .rc-combo-store   the existing select (options may carry data-search) */
(function () {
    'use strict';

    var PICK_MSG = 'Pick a name from the list';

    function norm(value) { return String(value == null ? '' : value).toLowerCase().trim(); }
    function tidy(value) { return String(value == null ? '' : value).replace(/\s+/g, ' ').trim(); }

    function init(wrap) {
        var input = wrap.querySelector('.rc-combo-input');
        var list = wrap.querySelector('.rc-combo-list');
        var toggle = wrap.querySelector('.rc-combo-toggle');
        var select = wrap.querySelector('.rc-combo-store');
        if (!input || !list || !toggle || !select || wrap.__rcCombo) { return; }
        wrap.__rcCombo = true;

        var items = [];
        for (var i = 0; i < select.options.length; i++) {
            var opt = select.options[i];
            if (!opt.value) { continue; }
            var label = tidy(opt.textContent);
            items.push({ value: opt.value, label: label, search: norm(opt.getAttribute('data-search') || label) });
        }

        // A municipality-wide list can be thousands of names deep; building that
        // many rows on every keystroke is what makes a picker feel slow.
        var MAX_ROWS = 100;
        var selected = null;
        var active = -1;
        var matches = items;
        var shown = items;

        // Chrome refuses to validate an invisible control and silently blocks the
        // whole submit, so the hidden store must never be the required field.
        select.removeAttribute('required');

        var preselected = select.options[select.selectedIndex];
        if (preselected && preselected.value) {
            selected = { value: preselected.value, label: tidy(preselected.textContent) };
            input.value = selected.label;
        }

        wrap.classList.add('is-ready');

        function render(term) {
            var needle = norm(term);
            matches = !needle
                ? items
                : items.filter(function (item) {
                    return item.search.indexOf(needle) > -1 || norm(item.label).indexOf(needle) > -1;
                });
            shown = matches.slice(0, MAX_ROWS);

            // With hundreds of names the highlighted row must be the picked one, not
            // the top of the list, or Enter re-picks a stranger and the panel opens
            // scrolled to the wrong place.
            active = -1;
            if (shown.length) {
                active = 0;
                if (selected) {
                    for (var k = 0; k < shown.length; k++) {
                        if (shown[k].value === selected.value) { active = k; break; }
                    }
                }
            }
            list.innerHTML = '';

            shown.forEach(function (item, index) {
                var row = document.createElement('button');
                row.type = 'button';
                row.id = list.id + '-opt-' + index;
                row.className = 'rc-combo-opt'
                    + (index === active ? ' is-active' : '')
                    + (selected && selected.value === item.value ? ' is-selected' : '');
                row.setAttribute('role', 'option');
                row.setAttribute('aria-selected', selected && selected.value === item.value ? 'true' : 'false');
                row.textContent = item.label;
                // mousedown is prevented so the input keeps focus until the click lands.
                row.addEventListener('mousedown', function (event) { event.preventDefault(); });
                row.addEventListener('click', function () { choose(item); });
                list.appendChild(row);
            });

            if (!matches.length) {
                var empty = document.createElement('div');
                empty.className = 'rc-combo-empty';
                empty.textContent = items.length ? 'No match for "' + tidy(term) + '"' : 'Nothing to pick yet';
                list.appendChild(empty);
            }

            var meta = document.createElement('div');
            meta.className = 'rc-combo-meta';
            meta.textContent = matches.length > shown.length
                ? '1-' + shown.length + ' of ' + matches.length + ' matches. Keep typing.'
                : matches.length + ' of ' + items.length;
            list.appendChild(meta);
        }

        function isOpen() { return !list.hidden; }

        // A phone-sized viewport puts the panel off-screen when the field sits low
        // on the page, so flip it upward whenever the space below runs out.
        function place() {
            var box = input.getBoundingClientRect();
            var roomBelow = window.innerHeight - box.bottom;
            var roomAbove = box.top;
            wrap.classList.toggle('is-up', roomBelow < 160 && roomAbove > roomBelow);
        }

        function open(term) {
            // No early return for an empty store: "Nothing to pick yet" tells the
            // midwife the unlinked list is empty instead of the field just not
            // reacting.
            if (input.disabled) { return; }
            render(term);
            place();
            list.hidden = false;
            wrap.classList.add('is-open');
            input.setAttribute('aria-expanded', 'true');
            // scrollIntoView is a no-op while the panel is hidden, so it has to run
            // after the list is on screen.
            var rows = list.querySelectorAll('.rc-combo-opt');
            if (rows[active]) {
                input.setAttribute('aria-activedescendant', rows[active].id);
                rows[active].scrollIntoView({ block: 'nearest' });
            }
        }

        function close() {
            list.hidden = true;
            wrap.classList.remove('is-open');
            input.setAttribute('aria-expanded', 'false');
            input.removeAttribute('aria-activedescendant');
        }

        function move(step) {
            if (!isOpen()) { open(selected ? '' : input.value); return; }
            if (!shown.length) { return; }
            active = (active + step + shown.length) % shown.length;
            var rows = list.querySelectorAll('.rc-combo-opt');
            for (var i = 0; i < rows.length; i++) { rows[i].classList.toggle('is-active', i === active); }
            input.setAttribute('aria-activedescendant', rows[active] ? rows[active].id : '');
            if (rows[active] && rows[active].scrollIntoView) { rows[active].scrollIntoView({ block: 'nearest' }); }
        }

        function choose(item) {
            selected = item;
            input.value = item.label;
            select.value = item.value;
            input.setCustomValidity('');
            close();
            // Page scripts that react to the pick (scope toggles, prefilled forms)
            // still listen on the select.
            select.dispatchEvent(new Event('change', { bubbles: true }));
        }

        // Refocusing a box that still holds a raw search term keeps that filter
        // instead of dumping the whole list back on screen.
        input.addEventListener('focus', function () { open(selected ? '' : input.value); });
        input.addEventListener('input', function () {
            selected = null;
            input.setCustomValidity('');
            var exact = items.filter(function (item) { return norm(item.label) === norm(input.value); });
            if (exact.length === 1) { choose(exact[0]); return; }
            open(input.value);
        });
        input.addEventListener('keydown', function (event) {
            if (event.key === 'ArrowDown') { event.preventDefault(); move(1); }
            else if (event.key === 'ArrowUp') { event.preventDefault(); move(-1); }
            else if (event.key === 'Enter' && isOpen() && active > -1) { event.preventDefault(); choose(shown[active]); }
            else if (event.key === 'Escape') { close(); }
            else if (event.key === 'Tab') { close(); }
        });
        input.addEventListener('blur', function () { window.setTimeout(close, 120); });

        toggle.addEventListener('mousedown', function (event) { event.preventDefault(); });
        toggle.addEventListener('click', function () {
            if (isOpen()) { close(); } else { input.focus(); open(''); }
        });

        var form = wrap.closest('form');
        if (form) {
            // Window capture, not form capture: pwa-outbox.js listens on document in
            // the capture phase and turns a covered POST into a fetch. Stopping the
            // event before it reaches document keeps an unpicked picker from sending
            // a request the server will only reject with 422.
            window.addEventListener('submit', function (event) {
                if (event.target !== form || input.disabled || selected) { return; }
                event.preventDefault();
                event.stopPropagation();
                input.setCustomValidity(PICK_MSG);
                input.reportValidity();
                input.focus();
                open(input.value);
            }, true);
        }
    }

    function scan() {
        var wraps = document.querySelectorAll('[data-combo]');
        for (var i = 0; i < wraps.length; i++) { init(wraps[i]); }
    }

    if (window.document.readyState === 'loading') {
        window.document.addEventListener('DOMContentLoaded', scan);
    } else {
        scan();
    }
})();
