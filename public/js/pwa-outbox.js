/* ReproCare offline-first outbox (BHW fieldwork).
 *
 * Intercepts field data forms, stores submissions in IndexedDB when the
 * network save fails, and replays them automatically on reconnect.
 * Covered endpoints: BHW health records, walk-in patients, checkups,
 * postpartum newborn + visit logs, and checkup referrals.
 */
(function () {
    'use strict';

    var DB_NAME = 'reprocare-outbox';
    var QUEUE_PATTERNS = [
        /\/bhw\/health-records\/?$/,
        /\/bhw\/walk-in-patients\/?$/,
        /\/bhw\/checkups\/?$/,
        /\/bhw\/postpartum\/(newborn|visit)\/?$/,
        /\/bhw\/referrals\/?$/,
        /\/bhw\/pregnancies\/?$/,
    ];

    function csrfToken() {
        var meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.getAttribute('content') : '';
    }

    function openDb() {
        return new Promise(function (resolve, reject) {
            var req = indexedDB.open(DB_NAME, 1);
            req.onupgradeneeded = function () { req.result.createObjectStore('entries', { keyPath: 'id', autoIncrement: true }); };
            req.onsuccess = function () { resolve(req.result); };
            req.onerror = function () { reject(req.error); };
        });
    }

    function makeSyncUuid() {
        try {
            if (window.crypto && crypto.randomUUID) return crypto.randomUUID();
        } catch (e) { /* fall through */ }
        return 'sync-' + Date.now().toString(36) + '-' + Math.random().toString(36).slice(2, 12);
    }

    function queueEntry(url, payload) {
        // Idempotency + race detection: every queued entry carries a unique
        // sync_uuid (duplicate replays collapse server-side) and the
        // client_timestamp the data was captured at (upstream-race signal).
        payload.sync_uuid = makeSyncUuid();
        payload.client_timestamp = new Date().toISOString();
        return openDb().then(function (db) {
            return new Promise(function (resolve, reject) {
                var tx = db.transaction('entries', 'readwrite');
                tx.objectStore('entries').add({
                    url: url, payload: payload, token: csrfToken(), queued_at: new Date().toISOString(),
                });
                tx.oncomplete = resolve;
                tx.onerror = function () { reject(tx.error); };
            });
        });
    }

    function pendingCount() {
        return openDb().then(function (db) {
            return new Promise(function (resolve) {
                try {
                    var tx = db.transaction('entries', 'readonly');
                    var count = tx.objectStore('entries').count();
                    count.onsuccess = function () { resolve(count.result || 0); };
                    count.onerror = function () { resolve(0); };
                } catch (e) { resolve(0); }
            });
        }).catch(function () { return 0; });
    }

    function updatePill() {
        var pill = document.getElementById('pwa-sync-pill');
        if (!pill) return;
        pendingCount().then(function (n) {
            if (!navigator.onLine) {
                pill.style.display = 'inline-flex';
                pill.innerHTML = '<span style="width:8px;height:8px;border-radius:50%;background:#F59E0B;display:inline-block;"></span> Offline' + (n > 0 ? ' · ' + n + ' queued' : '');
            } else if (n > 0) {
                pill.style.display = 'inline-flex';
                pill.innerHTML = '<span style="width:8px;height:8px;border-radius:50%;background:#0EA5E9;display:inline-block;"></span> Syncing ' + n + '…';
            } else {
                pill.style.display = 'none';
            }
        });
    }

    function toast(message, ok) {
        var el = document.createElement('div');
        el.textContent = message;
        el.style.cssText = 'position:fixed;bottom:24px;left:50%;transform:translateX(-50%);z-index:9999;'
            + 'background:' + (ok === false ? '#DC2626' : '#111827') + ';color:#fff;font-size:.85rem;font-weight:600;'
            + 'padding:.7rem 1.2rem;border-radius:12px;box-shadow:0 8px 24px rgba(0,0,0,.25);max-width:90vw;text-align:center;';
        document.body.appendChild(el);
        setTimeout(function () { el.remove(); }, 4200);
    }

    function formDataToObject(form) {
        var obj = {};
        new FormData(form).forEach(function (value, key) {
            if (value instanceof File) return; // files require connectivity
            if (obj[key] === undefined) obj[key] = value;
            else if (Array.isArray(obj[key])) obj[key].push(value);
            else obj[key] = [obj[key], value];
        });
        return obj;
    }

    function hasFiles(form) {
        var inputs = form.querySelectorAll('input[type="file"]');
        for (var i = 0; i < inputs.length; i++) {
            if (inputs[i].files && inputs[i].files.length > 0) return true;
        }
        return false;
    }

    function requestSync() {
        if ('serviceWorker' in navigator && navigator.serviceWorker.controller) {
            try {
                navigator.serviceWorker.controller.postMessage({ type: 'flush-outbox' });
            } catch (e) { /* fall through */ }
        }
        if ('SyncManager' in window && navigator.serviceWorker && navigator.serviceWorker.ready) {
            navigator.serviceWorker.ready.then(function (reg) {
                if (reg.sync) return reg.sync.register('reprocare-outbox').catch(function () {});
            });
        }
        setTimeout(updatePill, 1500);
    }

    document.addEventListener('submit', function (event) {
        var form = event.target;
        if (!form || form.tagName !== 'FORM' || form.method.toLowerCase() !== 'post') return;
        var action = form.action || window.location.href;
        var covered = QUEUE_PATTERNS.some(function (re) { return re.test(action); });
        if (!covered || hasFiles(form)) return;

        // Let it through when online; the catch below queues on failure.
        var payload = formDataToObject(form);
        // Remove Laravel method spoofing noise; keep _token out (sent via header).
        delete payload._token;

        fetch(action, {
            method: 'POST',
            body: (function () {
                var fd = new FormData(form);
                return fd;
            })(),
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin',
            redirect: 'follow',
        }).then(function (res) {
            if (!res.ok && res.status !== 422) throw new Error('save failed');
            // Online save worked — follow the redirect manually for UX.
            if (res.redirected) window.location.href = res.url;
            else window.location.reload();
        }).catch(function () {
            queueEntry(action, payload).then(function () {
                toast('No connection — entry saved on this device and will sync automatically.');
                requestSync();
                updatePill();
            });
        });

        event.preventDefault();
    }, true);

    window.addEventListener('online', function () {
        toast('Back online — syncing field entries…');
        requestSync();
        setTimeout(updatePill, 2500);
    });
    window.addEventListener('offline', updatePill);

    if ('serviceWorker' in navigator && navigator.serviceWorker.controller) {
        navigator.serviceWorker.addEventListener('message', function (event) {
            if (event.data && event.data.type === 'outbox-synced') {
                var d = event.data;
                var synced = d.count || 0;
                var deduped = d.deduped || 0;
                var failed = d.failed || 0;
                var warnings = d.warnings || [];
                if (synced > 0) {
                    toast(synced + ' offline entr' + (synced === 1 ? 'y' : 'ies') + ' synced to ReproCare.'
                        + (deduped > 0 ? ' (' + deduped + ' duplicate' + (deduped === 1 ? '' : 's') + ' skipped)' : ''));
                } else if (deduped > 0 && failed === 0) {
                    toast('Already up to date — ' + deduped + ' duplicate replay' + (deduped === 1 ? '' : 's') + ' ignored.');
                }
                if (failed > 0) {
                    toast(failed + ' entr' + (failed === 1 ? 'y needs' : 'ies need') + ' review — validation failed on the server.', false);
                }
                warnings.slice(0, 3).forEach(function (w) { toast('Upstream change: ' + w, false); });
            }
            updatePill();
        });
    }

    document.addEventListener('DOMContentLoaded', updatePill);
    setInterval(updatePill, 15000);

    /* Device access + offline-cache validation (staff deactivation safety).
     * When the server reports a revoked account or a bumped cache version,
     * wipe local field data so a deactivated device keeps nothing usable. */
    var CACHE_VERSION_KEY = 'reprocare-cache-version';
    function wipeLocalData() {
        try {
            var del = indexedDB.deleteDatabase(DB_NAME);
            del.onsuccess = del.onerror = del.onblocked = function () {};
        } catch (e) { /* ignore */ }
        try { localStorage.removeItem(CACHE_VERSION_KEY); } catch (e) { /* ignore */ }
        if ('caches' in window) {
            caches.keys().then(function (names) {
                names.forEach(function (n) { caches.delete(n); });
            }).catch(function () {});
        }
    }
    function checkDeviceAccess() {
        if (!navigator.onLine) return;
        // Never run on public pages: guests have no session, and a 401
        // there must not trigger a wipe or a redirect loop.
        var path = window.location.pathname.replace(/\/+$/, '') || '/';
        if (path === '/' || /^\/(auth\/)?(login|register)$/.test(path)) return;
        fetch('/api/device/session-check', { credentials: 'same-origin', headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function (res) {
                if (res.status === 401 || res.status === 419) {
                    if (/^\/(auth\/)?login$/.test(path)) return null;
                    wipeLocalData();
                    window.location.href = '/auth/login';
                    return null;
                }
                if (!res.ok) return null;
                return res.json();
            })
            .then(function (data) {
                if (!data) return;
                if (!data.active) {
                    if (/^\/(auth\/)?login$/.test(window.location.pathname.replace(/\/+$/, '') || '/')) return;
                    wipeLocalData();
                    window.location.href = '/auth/login';
                    return;
                }
                var seen = null;
                try { seen = parseInt(localStorage.getItem(CACHE_VERSION_KEY) || '0', 10) || null; } catch (e) { /* ignore */ }
                if (seen === null) {
                    try { localStorage.setItem(CACHE_VERSION_KEY, String(data.pwa_cache_version)); } catch (e) { /* ignore */ }
                } else if (seen !== parseInt(data.pwa_cache_version, 10)) {
                    wipeLocalData();
                    toast('This device access was refreshed by your administrator. Reloading…', false);
                    setTimeout(function () { window.location.reload(); }, 1200);
                }
            })
            .catch(function () { /* offline or unreachable — keep working locally */ });
    }
    document.addEventListener('DOMContentLoaded', checkDeviceAccess);
    window.addEventListener('online', checkDeviceAccess);
})();
