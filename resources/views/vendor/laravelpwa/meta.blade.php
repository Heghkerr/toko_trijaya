<!-- Web Application Manifest -->
@php
    try {
        $manifestUrl = route('laravelpwa.manifest');
    } catch (\Exception $e) {
        // Gunakan path dengan subfolder
        $manifestUrl = url('toko_trijaya/public/manifest.json');
    }
    // Pakai HTTPS hanya jika situsnya sudah diakses via HTTPS/production
    $forceHttps = request()->isSecure() || app()->environment('production');
    if ($forceHttps && strpos($manifestUrl, 'http://') === 0) {
        $manifestUrl = str_replace('http://', 'https://', $manifestUrl);
    }
@endphp
<link rel="manifest" href="{{ $manifestUrl }}">
<!-- Chrome for Android theme color -->
<meta name="theme-color" content="{{ $config['theme_color'] }}">

<!-- Add to homescreen for Chrome on Android -->
<meta name="mobile-web-app-capable" content="{{ $config['display'] == 'standalone' ? 'yes' : 'no' }}">
<meta name="application-name" content="{{ $config['short_name'] }}">
@php
    $lastIcon = end($config['icons']);
    $iconPath = $lastIcon['path'] ?? '';
    // Gunakan secure_asset untuk memastikan path benar dan HTTPS
    $iconUrl = $iconPath ? secure_asset($iconPath) : '';
@endphp
<link rel="icon" sizes="{{ data_get($lastIcon, 'sizes') }}" href="{{ $iconUrl }}">

<!-- Add to homescreen for Safari on iOS -->
<meta name="apple-mobile-web-app-capable" content="{{ $config['display'] == 'standalone' ? 'yes' : 'no' }}">
<meta name="apple-mobile-web-app-status-bar-style" content="{{  $config['status_bar'] }}">
<meta name="apple-mobile-web-app-title" content="{{ $config['short_name'] }}">
<link rel="apple-touch-icon" href="{{ $iconUrl }}">


<link href="{{ $config['splash']['640x1136'] }}" media="(device-width: 320px) and (device-height: 568px) and (-webkit-device-pixel-ratio: 2)" rel="apple-touch-startup-image" />
<link href="{{ $config['splash']['750x1334'] }}" media="(device-width: 375px) and (device-height: 667px) and (-webkit-device-pixel-ratio: 2)" rel="apple-touch-startup-image" />
<link href="{{ $config['splash']['1242x2208'] }}" media="(device-width: 621px) and (device-height: 1104px) and (-webkit-device-pixel-ratio: 3)" rel="apple-touch-startup-image" />
<link href="{{ $config['splash']['1125x2436'] }}" media="(device-width: 375px) and (device-height: 812px) and (-webkit-device-pixel-ratio: 3)" rel="apple-touch-startup-image" />
<link href="{{ $config['splash']['828x1792'] }}" media="(device-width: 414px) and (device-height: 896px) and (-webkit-device-pixel-ratio: 2)" rel="apple-touch-startup-image" />
<link href="{{ $config['splash']['1242x2688'] }}" media="(device-width: 414px) and (device-height: 896px) and (-webkit-device-pixel-ratio: 3)" rel="apple-touch-startup-image" />
<link href="{{ $config['splash']['1536x2048'] }}" media="(device-width: 768px) and (device-height: 1024px) and (-webkit-device-pixel-ratio: 2)" rel="apple-touch-startup-image" />
<link href="{{ $config['splash']['1668x2224'] }}" media="(device-width: 834px) and (device-height: 1112px) and (-webkit-device-pixel-ratio: 2)" rel="apple-touch-startup-image" />
<link href="{{ $config['splash']['1668x2388'] }}" media="(device-width: 834px) and (device-height: 1194px) and (-webkit-device-pixel-ratio: 2)" rel="apple-touch-startup-image" />
<link href="{{ $config['splash']['2048x2732'] }}" media="(device-width: 1024px) and (device-height: 1366px) and (-webkit-device-pixel-ratio: 2)" rel="apple-touch-startup-image" />

<!-- Tile for Win8 -->
<meta name="msapplication-TileColor" content="{{ $config['background_color'] }}">
<meta name="msapplication-TileImage" content="{{ data_get(end($config['icons']), 'src') }}">

<script type="text/javascript">
    // Initialize the service worker dengan error handling yang lebih baik
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', function() {
            // Support deployments in subfolder/base-path (ngrok -> /toko_trijaya/public, etc)
            var basePathMeta = document.querySelector('meta[name="app-base-path"]')?.content || '';
            if (basePathMeta.endsWith('/')) basePathMeta = basePathMeta.slice(0, -1);
            var swPath = basePathMeta + '/serviceworker.js';
            var scope = (basePathMeta ? (basePathMeta + '/') : '/');

            navigator.serviceWorker.register(swPath, { scope: scope }).then(function (registration) {
                // Registration was successful
                console.log('✅ Laravel PWA: ServiceWorker registration successful');
                console.log('Scope:', registration.scope);
                console.log('Registration:', registration);

                // Check for updates
                registration.addEventListener('updatefound', function() {
                    console.log('🔄 Service Worker update found');
                });

                // Check registration state
                if (registration.installing) {
                    console.log('📥 Service Worker installing...');
                } else if (registration.waiting) {
                    console.log('⏳ Service Worker waiting...');
                } else if (registration.active) {
                    console.log('✅ Service Worker active');
                }
            }, function (err) {
                // registration failed
                console.error('❌ Laravel PWA: ServiceWorker registration failed:', err);
                console.error('Error details:', err.message, err.stack);
            });

            // Check if service worker is already registered
            navigator.serviceWorker.getRegistration().then(function(registration) {
                if (registration) {
                    console.log('✅ Service Worker already registered:', registration.scope);
                } else {
                    console.log('⚠️ Service Worker not registered yet');
                }
            }).catch(function(err) {
                console.error('❌ Error checking service worker registration:', err);
            });
        });
    } else {
        console.warn('⚠️ Service Worker not supported in this browser');
    }
</script>
