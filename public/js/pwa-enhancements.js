/**
 * PWA Enhancements untuk Toko Trijaya
 * Fitur: Install Prompt, Update Notification, Offline Status
 */

(function() {
    'use strict';

    // ============================================
    // 1. INSTALL PROMPT (Custom Install Banner)
    // ============================================
    let deferredPrompt;
    const installBanner = document.getElementById('pwa-install-banner');
    const installButton = document.getElementById('pwa-install-button');
    const dismissInstallButton = document.getElementById('pwa-dismiss-install');

    // Check if app is already installed
    const isInstalled = window.matchMedia('(display-mode: standalone)').matches ||
                       window.navigator.standalone ||
                       document.referrer.includes('android-app://');

    // Listen for beforeinstallprompt event
    window.addEventListener('beforeinstallprompt', (e) => {
        // Prevent the mini-infobar from appearing
        e.preventDefault();
        // Stash the event so it can be triggered later
        deferredPrompt = e;

        // Show custom install banner if not installed
        if (!isInstalled && installBanner) {
            // Check if user has dismissed before (localStorage)
            const installDismissed = localStorage.getItem('pwa-install-dismissed');
            if (!installDismissed) {
                installBanner.style.display = 'block';
            }
        }
    });

    // Handle install button click
    if (installButton) {
        installButton.addEventListener('click', async () => {
            if (!deferredPrompt) {
                return;
            }

            // Show the install prompt
            deferredPrompt.prompt();

            // Wait for the user to respond
            const { outcome } = await deferredPrompt.userChoice;

            if (outcome === 'accepted') {
                // Hide banner
                if (installBanner) {
                    installBanner.style.display = 'none';
                }
                // Track installation (optional)
                if (typeof gtag !== 'undefined') {
                    gtag('event', 'pwa_install', {
                        'event_category': 'PWA',
                        'event_label': 'Install Accepted'
                    });
                }
            }

            // Clear the deferredPrompt
            deferredPrompt = null;

            // Hide banner
            if (installBanner) {
                installBanner.style.display = 'none';
            }
        });
    }

    // Handle dismiss button
    if (dismissInstallButton) {
        dismissInstallButton.addEventListener('click', () => {
            if (installBanner) {
                installBanner.style.display = 'none';
                // Remember dismissal for 7 days
                localStorage.setItem('pwa-install-dismissed', Date.now().toString());
            }
        });
    }

    // Auto-hide banner after 7 days
    const installDismissed = localStorage.getItem('pwa-install-dismissed');
    if (installDismissed) {
        const dismissedTime = parseInt(installDismissed);
        const sevenDays = 7 * 24 * 60 * 60 * 1000;
        if (Date.now() - dismissedTime > sevenDays) {
            localStorage.removeItem('pwa-install-dismissed');
        }
    }

    // ============================================
    // 2. UPDATE NOTIFICATION
    // ============================================
    const updateBanner = document.getElementById('pwa-update-banner');
    const updateButton = document.getElementById('pwa-update-button');
    const dismissUpdateButton = document.getElementById('pwa-dismiss-update');

    // Check for service worker updates
    if ('serviceWorker' in navigator) {
        let refreshing = false;

        // Listen for service worker updates
        navigator.serviceWorker.addEventListener('controllerchange', () => {
            if (refreshing) return;
            refreshing = true;
            // Show update banner
            if (updateBanner) {
                updateBanner.style.display = 'block';
            }
        });

        // Listen for messages from service worker
        navigator.serviceWorker.addEventListener('message', event => {
            if (event.data && event.data.type === 'SW_UPDATED') {
                // Show update notification
                if (updateBanner) {
                    updateBanner.style.display = 'block';
                }
            }
        });

        // Handle update button
        if (updateButton) {
            updateButton.addEventListener('click', () => {
                if (navigator.serviceWorker.controller) {
                    // Send message to service worker to skip waiting
                    navigator.serviceWorker.controller.postMessage({
                        type: 'SKIP_WAITING'
                    });
                    // Reload the page
                    window.location.reload();
                }
            });
        }

        // Handle dismiss update
        if (dismissUpdateButton) {
            dismissUpdateButton.addEventListener('click', () => {
                if (updateBanner) {
                    updateBanner.style.display = 'none';
                }
            });
        }

        // Check for updates only on page load and when user becomes online
        // Removed periodic check to reduce resource usage
        window.addEventListener('online', () => {
            if ('serviceWorker' in navigator) {
                navigator.serviceWorker.getRegistration().then(registration => {
                    if (registration) {
                        registration.update();
                    }
                });
            }
        });
    }

    // ============================================
    // 3. OFFLINE STATUS INDICATOR
    // ============================================
    const offlineIndicator = document.getElementById('pwa-offline-indicator');

    function updateOnlineStatus() {
        if (offlineIndicator) {
            if (navigator.onLine) {
                offlineIndicator.style.display = 'none';
            } else {
                offlineIndicator.style.display = 'block';
            }
        }
    }

    // Initial check
    updateOnlineStatus();

    // Listen for online/offline events
    window.addEventListener('online', () => {
        updateOnlineStatus();
        // Show notification
        if ('Notification' in window && Notification.permission === 'granted') {
            new Notification('Koneksi Internet Tersambung', {
                body: 'Aplikasi kembali online. Data akan disinkronkan.',
                icon: '/images/icons/icon-192x192.png',
                badge: '/images/icons/icon-96x96.png',
                tag: 'online-status'
            });
        }
    });

    window.addEventListener('offline', () => {
        updateOnlineStatus();
        // Show notification
        if ('Notification' in window && Notification.permission === 'granted') {
            new Notification('Anda Sedang Offline', {
                body: 'Aplikasi akan bekerja dalam mode offline. Data akan disinkronkan saat online.',
                icon: '/images/icons/icon-192x192.png',
                badge: '/images/icons/icon-96x96.png',
                tag: 'offline-status'
            });
        }
    });

    // ============================================
    // 4. SYNC STATUS INDICATOR
    // ============================================
    const syncIndicator = document.getElementById('pwa-sync-indicator');

    // Check sync status from IndexedDB (only when needed, not periodically)
    async function checkSyncStatus() {
        if (!syncIndicator) return;

        try {
            const { get, createStore } = idbKeyval;
            const transactionStore = createStore('toko-trijaya-db', 'pending-transactions');
            const pendingTransactions = await get('pending-transactions', transactionStore) || [];

            if (pendingTransactions.length > 0) {
                syncIndicator.style.display = 'block';
                syncIndicator.textContent = `${pendingTransactions.length} transaksi menunggu sinkronisasi`;
            } else {
                syncIndicator.style.display = 'none';
            }
        } catch (error) {
            // Silently fail to reduce console noise
        }
    }

    // Check sync status only on page load and when coming back online
    checkSyncStatus(); // Initial check
    window.addEventListener('online', checkSyncStatus);

    // ============================================
    // 4b. MANUAL SYNC (Fallback) - run on ANY page
    // ============================================
    // Background Sync kadang tidak terpanggil di beberapa device/browser.
    // Jadi kita paksa sync queue ketika online agar tidak "menunggu terus".
    async function manualSyncPendingTransactionsGlobal() {
        if (!navigator.onLine) return;

        try {
            if (typeof idbKeyval === 'undefined') return;
            const { get, set, createStore } = idbKeyval;
            const transactionStore = createStore('toko-trijaya-db', 'pending-transactions');
            let queue = await get('pending-transactions', transactionStore) || [];
            if (!Array.isArray(queue) || queue.length === 0) return;

            const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
            if (!csrf) return;

            // Update UI indicator (optional)
            if (syncIndicator) {
                syncIndicator.style.display = 'block';
                syncIndicator.textContent = `${queue.length} transaksi menunggu sinkronisasi`;
            }

            while (queue.length > 0) {
                const data = queue[0];
                const res = await fetch('/transactions', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrf,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify(data)
                }).catch(() => null);

                if (!res || !res.ok) {
                    // Stop and retry later (don't drop queue)
                    break;
                }

                queue.shift();
                await set('pending-transactions', queue, transactionStore);

                if (syncIndicator) {
                    if (queue.length > 0) {
                        syncIndicator.style.display = 'block';
                        syncIndicator.textContent = `${queue.length} transaksi menunggu sinkronisasi`;
                    } else {
                        syncIndicator.style.display = 'none';
                    }
                }
            }
        } catch (e) {
            // silently fail
        }
    }

    // Run on load (if online) and on every online event
    if (navigator.onLine) {
        manualSyncPendingTransactionsGlobal();
    }
    window.addEventListener('online', manualSyncPendingTransactionsGlobal);

    // ============================================
    // 5. REQUEST NOTIFICATION PERMISSION
    // ============================================
    function requestNotificationPermission() {
        if ('Notification' in window && Notification.permission === 'default') {
            Notification.requestPermission();
        }
    }

    // ============================================
    // 5b. PUSH SUBSCRIPTION (Web Push)
    // ============================================
    function urlBase64ToUint8Array(base64String) {
        const padding = '='.repeat((4 - (base64String.length % 4)) % 4);
        const base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
        const rawData = window.atob(base64);
        const outputArray = new Uint8Array(rawData.length);
        for (let i = 0; i < rawData.length; ++i) {
            outputArray[i] = rawData.charCodeAt(i);
        }
        return outputArray;
    }

    async function ensurePushSubscription() {
        // Only for logged-in pages (meta user-id exists)
        const userId = document.querySelector('meta[name="user-id"]')?.content;
        if (!userId) return;

        if (!('serviceWorker' in navigator) || !('PushManager' in window) || !('Notification' in window)) return;

        const vapidPublicKey = document.querySelector('meta[name="vapid-public-key"]')?.content;
        if (!vapidPublicKey) return; // not configured

        const basePathMeta = document.querySelector('meta[name="app-base-path"]')?.content || '';
        const basePath = basePathMeta.endsWith('/') ? basePathMeta.slice(0, -1) : basePathMeta;
        const swUrl = (basePath ? basePath : '') + '/serviceworker.js';
        const subscribeUrl = (basePath ? basePath : '') + '/push/subscribe';

        // Ensure we have a SW registration (some pages/devices might not be controlled yet)
        let registration = await navigator.serviceWorker.getRegistration();
        if (!registration) {
            // Fallback: register using the known file in public root
            // (layout already registers too, this is just a safety net)
            registration = await navigator.serviceWorker.register(swUrl, { scope: (basePath ? (basePath + '/') : '/') });
        }

        // Ask permission if needed
        if (Notification.permission === 'default') {
            const result = await Notification.requestPermission();
            if (result !== 'granted') return;
        }
        if (Notification.permission !== 'granted') return;

        // Wait until SW is ready (active)
        await navigator.serviceWorker.ready;

        let subscription = await registration.pushManager.getSubscription();
        if (!subscription) {
            subscription = await registration.pushManager.subscribe({
                userVisibleOnly: true,
                applicationServerKey: urlBase64ToUint8Array(vapidPublicKey)
            });
        }

        const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
        if (!csrf) return;

        const res = await fetch(subscribeUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrf,
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin',
            body: JSON.stringify({
                subscription: subscription.toJSON(),
                user_agent: navigator.userAgent
            })
        }).catch(() => null);

        if (!res || !res.ok) {
            console.warn('[PWA Push] subscribe failed', res ? res.status : 'no_response');
            return;
        }

        localStorage.setItem('pwa-push-subscribed', 'true');
    }

    // Trigger on first user interaction to satisfy browser requirements
    document.addEventListener('click', () => {
        // Avoid re-running constantly
        if (localStorage.getItem('pwa-push-subscribed') === 'true') return;
        ensurePushSubscription().catch(() => null);
    }, { once: true });

    // ============================================
    // 6. PWA INSTALLED DETECTION
    // ============================================
    if (isInstalled) {
        // Hide install banner if already installed
        if (installBanner) {
            installBanner.style.display = 'none';
        }
        // Add class to body for styling
        document.body.classList.add('pwa-installed');
    }

    // ============================================
    // 7. CONSOLE LOG FOR DEBUGGING (removed for production)
    // ============================================
    // Removed console logs to reduce resource usage

})();

