/* ReproCare Service Worker — app-shell caching + offline fallback + outbox sync. */
const CACHE_VERSION = 'reprocare-v2';
const SHELL_CACHE = CACHE_VERSION + '-shell';
const RUNTIME_CACHE = CACHE_VERSION + '-runtime';
const OFFLINE_URL = '/offline.html';

const SHELL_ASSETS = [
    '/',
    OFFLINE_URL,
    '/manifest.webmanifest',
    '/images/brand/reprocare-logo.png',
    '/images/brand/icon-192.png',
];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(SHELL_CACHE)
            .then((cache) => cache.addAll(SHELL_ASSETS.map((u) => new Request(u, { cache: 'reload' }))))
            .catch(() => undefined)
            .then(() => self.skipWaiting())
    );
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys()
            .then((keys) => Promise.all(
                keys.filter((k) => k.indexOf('reprocare-') === 0 && k !== SHELL_CACHE && k !== RUNTIME_CACHE)
                    .map((k) => caches.delete(k))
            ))
            .then(() => self.clients.claim())
    );
});

self.addEventListener('fetch', (event) => {
    const req = event.request;
    if (req.method !== 'GET') return;
    const url = new URL(req.url);
    if (url.origin !== self.location.origin) return;

    // Navigations: network first, fall back to offline page.
    if (req.mode === 'navigate') {
        event.respondWith(
            fetch(req)
                .then((res) => {
                    const copy = res.clone();
                    caches.open(RUNTIME_CACHE).then((c) => c.put(req, copy)).catch(() => undefined);
                    return res;
                })
                .catch(() => caches.match(req).then((hit) => hit || caches.match(OFFLINE_URL)))
        );
        return;
    }

    // Static assets: cache first.
    if (/\.(css|js|png|jpg|jpeg|svg|webp|ico|woff2?)$/i.test(url.pathname)) {
        event.respondWith(
            caches.match(req).then((hit) => hit || fetch(req).then((res) => {
                const copy = res.clone();
                caches.open(RUNTIME_CACHE).then((c) => c.put(req, copy)).catch(() => undefined);
                return res;
            }))
        );
    }
});

/* ---- Outbox sync: replay queued field entries when connectivity returns. ---- */
function openOutbox() {
    return new Promise((resolve, reject) => {
        const req = indexedDB.open('reprocare-outbox', 1);
        req.onupgradeneeded = () => req.result.createObjectStore('entries', { keyPath: 'id', autoIncrement: true });
        req.onsuccess = () => resolve(req.result);
        req.onerror = () => reject(req.error);
    });
}

async function flushOutbox() {
    try {
        const db = await openOutbox();
        const entries = await new Promise((resolve, reject) => {
            const tx = db.transaction('entries', 'readonly');
            const getAll = tx.objectStore('entries').getAll();
            getAll.onsuccess = () => resolve(getAll.result || []);
            getAll.onerror = () => reject(getAll.error);
        });

        let synced = 0;
        let deduped = 0;
        let failed = 0;
        const warnings = [];
        const dropEntry = (id) => new Promise((resolve, reject) => {
            const tx = db.transaction('entries', 'readwrite');
            tx.objectStore('entries').delete(id);
            tx.oncomplete = resolve;
            tx.onerror = () => reject(tx.error);
        });
        for (const entry of entries) {
            try {
                const form = new FormData();
                Object.keys(entry.payload || {}).forEach((k) => {
                    const v = entry.payload[k];
                    if (Array.isArray(v)) v.forEach((item) => form.append(k, item));
                    else if (v !== null && v !== undefined) form.append(k, v);
                });
                const res = await fetch(entry.url, {
                    method: 'POST',
                    body: form,
                    headers: {
                        'X-CSRF-TOKEN': entry.token || '',
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                    },
                    credentials: 'same-origin',
                    redirect: 'follow',
                });
                // Structured sync replies carry dedupe + upstream-race signals.
                let body = null;
                try {
                    const ct = res.headers.get('content-type') || '';
                    if (ct.indexOf('application/json') !== -1) body = await res.clone().json();
                } catch (e) { /* non-JSON (redirect landing page) */ }
                if (body && body.status === 'deduped') {
                    await dropEntry(entry.id);
                    deduped++;
                } else if (res.status === 422) {
                    // Poison entry (fails validation even online): drop it so
                    // it stops retrying forever, and flag it for review.
                    await dropEntry(entry.id);
                    failed++;
                } else if (res.ok || res.type === 'opaqueredirect' || (res.status >= 300 && res.status < 400)) {
                    await dropEntry(entry.id);
                    synced++;
                    if (body && body.upstream_warning) warnings.push(body.upstream_warning);
                }
                // Anything else (5xx, network) stays queued for next attempt.
            } catch (e) { /* keep for next attempt */ }
        }

        const clients = await self.clients.matchAll({ type: 'window' });
        clients.forEach((c) => c.postMessage({ type: 'outbox-synced', count: synced, deduped, failed, warnings }));
    } catch (e) { /* IndexedDB unavailable */ }
}

self.addEventListener('sync', (event) => {
    if (event.tag === 'reprocare-outbox') {
        event.waitUntil(flushOutbox());
    }
});

self.addEventListener('message', (event) => {
    if (event.data && event.data.type === 'flush-outbox') {
        event.waitUntil(flushOutbox());
    }
});
