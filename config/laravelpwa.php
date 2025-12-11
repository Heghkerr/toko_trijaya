<?php

return [
    'name' => 'Toko Trijaya', // Biarkan ini

    'manifest' => [
        // 👇 UBAH INI
        'name' => 'Toko Trijaya',
        'short_name' => 'Trijaya',
        'description' => 'Aplikasi Point of Sale Toko Trijaya',

        // 👇 UBAH INI
        'start_url' => '/login',
        'scope' => '/',
        'id' => '/',
        'background_color' => '#ffffff',

        // 👇 UBAH INI (sesuaikan dengan warna tema Anda, cth: biru)
        'theme_color' => '#0D6EFD',
        'display' => 'standalone',
        'orientation'=> 'any',
        'status_bar'=> 'black',
        'icons' => [
            '48x48' => [
                'path' => '/images/icons/icon-48x48.png',
                'purpose' => 'any'
            ],
            '72x72' => [
                'path' => '/images/icons/icon-72x72.png',
                'purpose' => 'any'
            ],
            '96x96' => [
                'path' => '/images/icons/icon-96x96.png',
                'purpose' => 'any'
            ],
            '128x128' => [
                'path' => '/images/icons/icon-128x128.png',
                'purpose' => 'any'
            ],
            '144x144' => [
                'path' => '/images/icons/icon-144x144.png',
                'purpose' => 'any'
            ],
            '152x152' => [
                'path' => '/images/icons/icon-152x152.png',
                'purpose' => 'any'
            ],
            '192x192' => [
                'path' => '/images/icons/icon-192x192.png',
                'purpose' => 'any'
            ],
            '384x384' => [
                'path' => '/images/icons/icon-384x384.png',
                'purpose' => 'any'
            ],
            '512x512' => [
                'path' => '/images/icons/icon-512x512.png',
                'purpose' => 'any'
            ],
        ],
        'splash' => [
            '640x1136' => '/images/icons/splash-640x1136.png',
            '750x1334' => '/images/icons/splash-750x1334.png',
            '828x1792' => '/images/icons/splash-828x1792.png',
            '1125x2436' => '/images/icons/splash-1125x2436.png',
            '1242x2208' => '/images/icons/splash-1242x2208.png',
            '1242x2688' => '/images/icons/splash-1242x2688.png',
            '1536x2048' => '/images/icons/splash-1536x2048.png',
            '1668x2224' => '/images/icons/splash-1668x2224.png',
            '1668x2388' => '/images/icons/splash-1668x2388.png',
            '2048x2732' => '/images/icons/splash-2048x2732.png',
        ],


        // 👇 UBAH INI
        'shortcuts' => [
            [
                'name' => 'Tambah Produk',
                'description' => 'Buka form tambah produk baru',
                'url' => '/products/create', // Sesuaikan URL Anda
                'icons' => [
                    "src" => "/images/icons/icon-96x96.png", // Pastikan icon 96x96 ada
                    "purpose" => "any"
                ]
            ],
            // Hapus 'Shortcut Link 2' jika tidak perlu, atau perbaiki:
            [
                'name' => 'Laporan',
                'description' => 'Buka halaman laporan',
                'url' => '/reports', // Sesuaikan URL Anda
                'icons' => [
                    "src" => "/images/icons/icon-96x96.png",
                    "purpose" => "any"
                ]
            ]
        ],

        // 👇 TAMBAHKAN BAGIAN INI (yang tadi "tidak ada")
        'screenshots' => [
            [
                'src' => '/images/screenshots/desktop.png', // Buat file screenshot ini
                'sizes' => '1280x720',
                'type' => 'image/png',
                'form_factor' => 'wide' // 'wide' untuk desktop
            ],
            [
                'src' => '/images/screenshots/mobile.png', // Buat file screenshot ini
                'sizes' => '540x720',
                'type' => 'image/png',
                'form_factor' => 'narrow' // 'narrow' untuk mobile
            ]
        ],

        'custom' => [
            'description' => 'Aplikasi Point of Sale Toko Trijaya',
            'lang' => 'id',
            'dir' => 'ltr'
        ]
    ]
];
