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
    // 5. REQUEST NOTIFICATION PERMISSION
    // ============================================
    function requestNotificationPermission() {
        if ('Notification' in window && Notification.permission === 'default') {
            Notification.requestPermission();
        }
    }

    // Request permission after user interaction (optional)
    // Uncomment if you want to request permission automatically
    // document.addEventListener('click', requestNotificationPermission, { once: true });

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

