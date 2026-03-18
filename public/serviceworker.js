/* ============================================================
   PWA TRIJAYA - CLEAN SERVICE WORKER
   Versi: 2.2.1
   ============================================================ */

const CACHE_VERSION = "2.2.2";
const staticCacheName = `pwa-trijaya-v${CACHE_VERSION}-static`;
const dynamicCacheName = `pwa-trijaya-v${CACHE_VERSION}-data`;

/* ------------------------------------------------------------
   FILES TO CACHE
------------------------------------------------------------ */
const filesToCache = [
    "/login",
    "/dashboard",
    "/offline",
    "/transactions/create",

    "/js/idb-keyval.min.js",
    "/images/icons/icon-192x192.png",
    "/images/icons/icon-512x512.png",

    "/manifest.json"
];

/* ------------------------------------------------------------
   CDN RESOURCES
------------------------------------------------------------ */
const cdnResourcesToCache = [
    "https://code.jquery.com/jquery-3.6.0.min.js",
    "https://cdn.jsdelivr.net/npm/idb-keyval@6/dist/umd.js",
    "https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css",
    "https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js",
    "https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css",
    "https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.bootstrap5.css",
    "https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"
];

/* ------------------------------------------------------------
   INSTALL: PRE-CACHE ASET
------------------------------------------------------------ */
self.addEventListener("install", event => {
    console.log("🔧 Installing SW…");
    self.skipWaiting();

    event.waitUntil(
        caches.open(staticCacheName).then(async cache => {
            console.log("Caching core files...");
            await Promise.allSettled(
                filesToCache.map(url =>
                    fetch(url)
                        .then(res => res.ok ? cache.put(url, res) : null)
                        .catch(() => null)
                )
            );

            console.log("Caching CDN...");
            await Promise.all(
                cdnResourcesToCache.map(url =>
                    fetch(url)
                        .then(res => res.ok ? cache.put(url, res) : null)
                        .catch(() => null)
                )
            );
        })
    );
});

/* ------------------------------------------------------------
   ACTIVATE: CLEAR OLD CACHE
------------------------------------------------------------ */
self.addEventListener("activate", event => {
    console.log("⚡ Activating SW…");

    event.waitUntil(
        caches.keys().then(cacheNames => {
            return Promise.all(
                cacheNames
                    .filter(name => name.startsWith("pwa-trijaya-"))
                    .filter(name => name !== staticCacheName && name !== dynamicCacheName)
                    .map(name => caches.delete(name))
            );
        })
        .then(() => self.clients.claim())
    );
});

/* ------------------------------------------------------------
   MESSAGE HANDLER
------------------------------------------------------------ */
self.addEventListener("message", event => {
    if (event.data?.type === "SKIP_WAITING") self.skipWaiting();

    if (event.data?.type === "GET_VERSION") {
        event.ports[0].postMessage({ version: CACHE_VERSION });
    }
});

/* ------------------------------------------------------------
   FETCH HANDLER (BERSIH)
------------------------------------------------------------ */
self.addEventListener("fetch", event => {
    const req = event.request;
    const url = new URL(req.url);

    // Abaikan request chrome-extension:// atau tentang://
    if (!["http:", "https:"].includes(url.protocol)) return;

    // Jangan cache manifest & favicon
    if (
        req.url.includes("manifest.json") ||
        req.url.includes("favicon") ||
        req.destination === "manifest"
    ) {
        event.respondWith(fetch(req));
        return;
    }

    // API → Network First
    if (req.url.includes("/api/") && req.method === "GET") {
        event.respondWith(
            fetch(req)
                .then(res => {
                    const clone = res.clone();
                    caches.open(dynamicCacheName).then(c => c.put(req, clone));
                    return res;
                })
                .catch(() => caches.match(req))
        );
        return;
    }

    // ASET → Cache First
    if (["script", "style", "font", "image"].includes(req.destination)) {
        event.respondWith(
            caches.match(req).then(cacheRes => {
                if (cacheRes) return cacheRes;

                return fetch(req)
                    .then(res => {
                        const clone = res.clone();
                        caches.open(staticCacheName).then(c => c.put(req, clone));
                        return res;
                    })
                    .catch(() => caches.match(req));
            })
        );
        return;
    }

    // HALAMAN → Network First
    event.respondWith(
        fetch(req)
            .then(res => {
                const clone = res.clone();
                caches.open(staticCacheName).then(c => c.put(req, clone));
                return res;
            })
            .catch(() =>
                caches.match(req).then(cacheRes => {
                    if (cacheRes) return cacheRes;
                    if (req.mode === "navigate") return caches.match("/offline");
                })
            )
    );
});

/* ============================================================
   BACKGROUND SYNC – OFFLINE TRANSACTION QUEUE
============================================================ */

try {
    // Prefer local copy to avoid install hangs when CDN is slow/unreachable
    importScripts("/js/idb-keyval.min.js");
    console.log("idb-keyval loaded (local)");
} catch (e) {
    console.warn("Fallback idb-keyval");
    var idbKeyval = {
        get: () => Promise.resolve([]),
        set: () => Promise.resolve(),
        createStore: () => ({})
    };
}

const { get, set, createStore } = idbKeyval;
const transactionStore = createStore("toko-trijaya-db", "pending-transactions");

self.addEventListener("sync", event => {
    if (event.tag === "sync-new-transactions") {
        event.waitUntil(syncNewTransactions());
    }
});

async function syncNewTransactions() {
    console.log("SW: Syncing pending transactions...");

    let queue = await get("pending-transactions", transactionStore) || [];
    if (queue.length === 0) return;

    while (queue.length > 0) {
        const data = queue[0];

        const response = await fetch("/transactions", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                Accept: "application/json",
                ...(data && data._csrf ? { "X-CSRF-TOKEN": data._csrf } : {}),
                "X-Requested-With": "XMLHttpRequest"
            },
            credentials: "include",
            body: JSON.stringify(data)
        }).catch(() => null);

        if (!response || !response.ok) {
            console.log("SW: Sync failed, retry later");
            // Jangan drop queue; biarkan untuk retry nanti
            return;
        }

        queue.shift();
        await set("pending-transactions", queue, transactionStore);
    }

    console.log("SW: Sync completed!");
}

/* ============================================================
   WEB PUSH NOTIFICATION HANDLERS
============================================================ */

self.addEventListener('push', event => {
    let data = {};
    try {
        data = event.data ? event.data.json() : {};
    } catch (e) {
        data = { body: event.data ? event.data.text() : '' };
    }

    const title = data.title || 'Toko Trijaya';
    const iconUrl = new URL('images/icons/icon-192x192.png', self.registration.scope).toString();
    const badgeUrl = new URL('images/icons/icon-96x96.png', self.registration.scope).toString();
    const options = {
        body: data.body || '',
        icon: iconUrl,
        badge: badgeUrl,
        tag: data.tag || 'toko-trijaya',
        renotify: true,
        data: {
            url: data.url || '/dashboard'
        }
    };

    event.waitUntil(self.registration.showNotification(title, options));
});

self.addEventListener('notificationclick', event => {
    event.notification.close();
    const targetUrl = event.notification?.data?.url || '/dashboard';

    event.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true }).then(clientList => {
            for (const client of clientList) {
                if ('focus' in client) {
                    // Focus existing tab and navigate if needed
                    return client.focus().then(() => {
                        if ('navigate' in client && client.url !== targetUrl) {
                            return client.navigate(targetUrl);
                        }
                    });
                }
            }
            if (clients.openWindow) {
                return clients.openWindow(targetUrl);
            }
        })
    );
});
