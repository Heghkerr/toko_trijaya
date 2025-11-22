// NAIKKAN VERSI (Sangat Penting) - Update ini setiap kali ada perubahan
var staticCacheName = "pwa-trijaya-v2.2.0-static";
var dynamicCacheName = "pwa-trijaya-v2.2.0-data";
var CACHE_VERSION = "2.2.0";

// 1. filesToCache (Sudah benar)
var filesToCache = [
    '/login',
    '/dashboard',
    '/offline',
    '/transactions/create',
    '/js/idb-keyval.min.js',
    '/images/icons/icon-192x192.png',
    '/images/icons/icon-512x512.png'
];

// Cache CDN resources untuk offline
var cdnResourcesToCache = [
    'https://code.jquery.com/jquery-3.6.0.min.js',
    'https://cdn.jsdelivr.net/npm/idb-keyval@6/dist/umd.js',
    'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css',
    'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js',
    'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css',
    'https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.bootstrap5.css',
    'https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js'
];

// Cache on install (Tidak berubah)
self.addEventListener("install", event => {
    self.skipWaiting();
    event.waitUntil(
        caches.open(staticCacheName)
            .then(cache => {
                console.log("Mencoba menyimpan App Shell (file inti) ke cache...");
                // Cache local files
                return cache.addAll(filesToCache).catch(err => {
                    console.error("Gagal melakukan precaching local files:", err);
                }).then(() => {
                    // Cache CDN resources
                    return Promise.all(
                        cdnResourcesToCache.map(url => {
                            return fetch(url)
                                .then(response => {
                                    if (response.ok) {
                                        return cache.put(url, response);
                                    }
                                })
                                .catch(err => {
                                    console.warn("Gagal cache CDN resource:", url, err);
                                });
                        })
                    );
                });
            })
    );
});

// Clear old cache on activate (Tidak berubah)
self.addEventListener('activate', event => {
    event.waitUntil(
        caches.keys().then(cacheNames => {
            return Promise.all(
                cacheNames
                    .filter(cacheName => (cacheName.startsWith("pwa-trijaya-")))
                    .filter(cacheName => (cacheName !== staticCacheName && cacheName !== dynamicCacheName))
                    .map(cacheName => caches.delete(cacheName))
            );
        }).then(() => {
            // Notify all clients about the update
            return self.clients.matchAll().then(clients => {
                clients.forEach(client => {
                    client.postMessage({
                        type: 'SW_UPDATED',
                        version: CACHE_VERSION
                    });
                });
            });
        })
    );
    self.clients.claim();
});

// Listen for messages from the page
self.addEventListener('message', event => {
    if (event.data && event.data.type === 'SKIP_WAITING') {
        self.skipWaiting();
    }
    if (event.data && event.data.type === 'GET_VERSION') {
        event.ports[0].postMessage({ version: CACHE_VERSION });
    }
});


// 2. FETCH LISTENER DENGAN LOGIKA YANG SUDAH DIPERBAIKI
self.addEventListener("fetch", event => {

    // 1. TANGANI REQUEST NON-GET (POST, PUT, etc)
    if (event.request.method !== 'GET') {
        return; // Biarkan browser menanganinya (AJAX)
    }

    // 2. STRATEGI UNTUK API (Network First dgn fallback cache) - HANYA GET
    if (event.request.url.includes('/api/')) {
        if (event.request.method === 'GET') {
            event.respondWith(
                fetch(event.request).then(function(networkResponse) {
                    const responseToCache = networkResponse.clone();
                    caches.open(dynamicCacheName).then(cache => {
                        cache.put(event.request, responseToCache);
                    });
                    return networkResponse;
                }).catch(function() {
                    return caches.match(event.request);
                })
            );
        }
        return;
    }

    // --- 👇 PERBAIKAN LOGIKA ADA DI SINI 👇 ---

    // 3. STRATEGI UNTUK ASET (Cache First)
    //    (JS, CSS, Font, Gambar, termasuk CDN)
    if (event.request.destination === 'script' ||
        event.request.destination === 'style' ||
        event.request.destination === 'font' ||
        event.request.destination === 'image' ||
        event.request.url.includes('jquery') ||
        event.request.url.includes('bootstrap') ||
        event.request.url.includes('tom-select') ||
        event.request.url.includes('bootstrap-icons') ||
        event.request.url.includes('idb-keyval'))
    {
        event.respondWith(
            caches.match(event.request).then(function(cacheResponse) {
                // 1. Jika ada di cache, KEMBALIKAN (termasuk CDN resources)
                if (cacheResponse) {
                    return cacheResponse;
                }

                // 2. Jika tidak ada, ambil dari jaringan
                return fetch(event.request).then(function(networkResponse) {
                    // --- PERBAIKAN (ANTI-RACE-CONDITION) ---
                    // 3. Buat salinan/clone-nya DULU
                    const responseToCache = networkResponse.clone();

                    // 4. Simpan salinannya ke cache STATIS (untuk CDN) atau DINAMIS
                    const cacheToUse = (event.request.url.includes('jquery') ||
                                       event.request.url.includes('bootstrap') ||
                                       event.request.url.includes('tom-select') ||
                                       event.request.url.includes('bootstrap-icons') ||
                                       event.request.url.includes('idb-keyval'))
                                       ? staticCacheName : dynamicCacheName;

                    caches.open(cacheToUse).then(cache => {
                        cache.put(event.request, responseToCache);
                    });

                    // 5. Kembalikan data ASLI ke browser
                    return networkResponse;
                    // --- AKHIR PERBAIKAN ---
                }).catch(function() {
                    // Jika fetch gagal (offline), coba cari di cache lagi
                    return caches.match(event.request);
                });
            })
        );
        return; // Hentikan di sini
    }

    // 4. STRATEGI UNTUK HALAMAN/CANGKANG (Network First)
    //    ( /dashboard, /transactions/create, dll.)
    event.respondWith(
        fetch(event.request).then(
            function(networkResponse) {
                // --- PERBAIKAN (ANTI-RACE-CONDITION) ---
                // 1. Buat salinan/clone-nya DULU
                const responseToCache = networkResponse.clone();

                // 2. Simpan salinannya ke cache STATIS
                caches.open(staticCacheName).then(cache => {
                    cache.put(event.request, responseToCache);
                });

                // 3. Kembalikan data ASLI ke browser
                return networkResponse;
                // --- AKHIR PERBAIKAN ---
            }
        ).catch(function() {
            // Jika jaringan gagal, ambil dari cache
            return caches.match(event.request).then(cacheResponse => {
                if (cacheResponse) {
                    return cacheResponse;
                }
                if (event.request.mode === 'navigate') {
                    return caches.match('/offline');
                }
            });
        })
    );
});


// 3. LOGIKA SYNC ANDA (Sudah benar)
self.importScripts('/js/idb-keyval.min.js');
const { get, set, createStore } = idbKeyval;

const transactionStore = createStore('toko-trijaya-db', 'pending-transactions');

self.addEventListener('sync', function(event) {
    if (event.tag == 'sync-new-transactions') {
        event.waitUntil(syncNewTransactions());
    }
});


// 6. Fungsi yang melakukan sinkronisasi
async function syncNewTransactions() {
    console.log('Service Worker: Memulai sinkronisasi transaksi...');
    let queue;
    try {
        // 7. Ambil antrian dari 'pending-transactions'
        queue = await get('pending-transactions', transactionStore) || [];

        if (queue.length === 0) {
            console.log('Service Worker: Tidak ada transaksi untuk disinkronkan.');
            return;
        }

        // 8. Kirim setiap transaksi di antrian ke server, SATU PER SATU
        while (queue.length > 0) {
            let transactionData = queue[0]; // Ambil data transaksi pertama

            // ⚠️ PENTING: Ganti dengan URL hardcode Anda
            // Service worker tidak bisa baca Blade {{ route(...) }}
            const response = await fetch('/transactions', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                    // CSRF token tidak diperlukan, akan di-handle di server
                },
                body: JSON.stringify(transactionData)
            });

            if (!response.ok) {
                // Gagal mengirim (mungkin server 500), hentikan proses.
                // Data akan tetap di antrian untuk dicoba lagi nanti.
                console.error('Service Worker: Gagal mengirim transaksi, akan dicoba lagi nanti.', response);
                return;
            }

            // 9. BERHASIL! Hapus transaksi dari antrian
            console.log('Service Worker: Transaksi berhasil dikirim, menghapus dari antrian.');
            queue.shift(); // Hapus item pertama yang sudah terkirim
        }

        // 10. Simpan kembali antrian (yang sekarang kosong) ke IndexedDB
        await set('pending-transactions', queue, transactionStore);
        console.log('Service Worker: Sinkronisasi transaksi selesai.');

    } catch (error) {
        console.error('Service Worker: Error saat sinkronisasi transaksi:', error);
        // Biarkan data di 'queue' untuk dicoba lagi nanti
    }
}
