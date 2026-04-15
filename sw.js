/* ─── sw.js : SSRCMS Service Worker ──────────────────────────── */

const CACHE_NAME    = 'ssrcms-v1';
const STATIC_ASSETS = [
    '/SSRCMS/',
    '/SSRCMS/offline.html',
    '/SSRCMS/manifest.json',
    '/SSRCMS/css/style.css',
    '/SSRCMS/js/app.js',
    '/SSRCMS/js/auth.js',
    '/SSRCMS/js/complaints.js',
    '/SSRCMS/js/admin.js',
    '/SSRCMS/assets/icons/icon-192.png',
    '/SSRCMS/assets/icons/icon-512.png',
    'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css',
    'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js',
    'https://code.jquery.com/jquery-3.7.1.min.js',
    'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css',
    'https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap',
];

// ─── Install: pre-cache static assets ─────────────────────────
self.addEventListener('install', event => {
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then(cache => {
                console.log('[SW] Caching static assets');
                return cache.addAll(STATIC_ASSETS);
            })
            .then(() => self.skipWaiting())
    );
});

// ─── Activate: clean old caches ────────────────────────────────
self.addEventListener('activate', event => {
    event.waitUntil(
        caches.keys().then(keys =>
            Promise.all(
                keys.filter(k => k !== CACHE_NAME)
                    .map(k => { console.log('[SW] Deleting old cache:', k); return caches.delete(k); })
            )
        ).then(() => self.clients.claim())
    );
});

// ─── Fetch: network-first for PHP, cache-first for static ─────
self.addEventListener('fetch', event => {
    const url = new URL(event.request.url);

    // Bypass SW completely for API endpoints
    if (url.pathname.includes('/SSRCMS/api/')) {
        return;
    }

    // Always use network for POST requests
    if (event.request.method !== 'GET') {
        return;
    }

    // Network-first for PHP pages
    if (url.pathname.endsWith('.php') || url.pathname === '/SSRCMS/') {
        event.respondWith(
            fetch(event.request)
                .catch(() => caches.match('/SSRCMS/offline.html'))
        );
        return;
    }

    // Cache-first strategy for static assets
    event.respondWith(
        caches.match(event.request).then(cached => {
            if (cached) return cached;
            return fetch(event.request).then(res => {
                if (res && res.status === 200 && res.type === 'basic') {
                    const clone = res.clone();
                    caches.open(CACHE_NAME).then(c => c.put(event.request, clone));
                }
                return res;
            });
        }).catch(() => {
            if (event.request.mode === 'navigate') {
                return caches.match('/SSRCMS/offline.html');
            }
        })
    );
});
