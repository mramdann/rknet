<?php
// config.php — sumber konten landing page (UI only)
$site = [
    'name'    => 'Starlite',
    'phone'   => '+62811789111',
    'company' => 'PT Integrasi Jaringan Ekosistem',
    'address' => 'Jalan Tiang Bendera V No.20 Roa Malaka, Tambora, Jakarta Barat',

    'benefits' => [
        ['icon' => 'bi-calendar-check', 'text' => 'Gratis 1 bulan'],
        ['icon' => 'bi-tools',          'text' => 'Gratis biaya instalasi'],
        ['icon' => 'bi-infinity',       'text' => 'Bebas FUP - Internet UNLIMITED'],
        ['icon' => 'bi-router',         'text' => 'Termasuk biaya sewa modem'],
        ['icon' => 'bi-receipt',        'text' => 'Harga sudah termasuk PPN'],
    ],

    'packages' => [
        [
            'name'     => 'Unlimited 30 Mbps',
            'speed'    => '30 Mbps',
            'price'    => 'Rp199.000',
            'period'   => '/bulan',
            'features' => ['Bebas FUP - Unlimited', 'Termasuk sewa modem', 'Gratis instalasi', 'Harga termasuk PPN'],
            'featured' => false,
        ],
        [
            'name'     => 'Unlimited 50 Mbps',
            'speed'    => '50 Mbps',
            'price'    => 'Rp299.000',
            'period'   => '/bulan',
            'features' => ['Bebas FUP - Unlimited', 'Termasuk sewa modem', 'Gratis instalasi', 'Gratis 1 bulan'],
            'featured' => true,
        ],
        [
            'name'     => 'Unlimited 100 Mbps',
            'speed'    => '100 Mbps',
            'price'    => 'Rp399.000',
            'period'   => '/bulan',
            'features' => ['Bebas FUP - Unlimited', 'Termasuk sewa modem', 'Gratis instalasi', 'Prioritas jaringan'],
            'featured' => false,
        ],
    ],

    'features' => [
        ['icon' => 'bi-house-wifi',   'title' => 'Wireless Home Network'],
        ['icon' => 'bi-speedometer2', 'title' => 'High Speed Internet'],
        ['icon' => 'bi-wifi',         'title' => 'Stable Internet Connection'],
        ['icon' => 'bi-broadcast',    'title' => 'Pure Fiber Network'],
    ],

    'socials' => [
        ['icon' => 'bi-instagram', 'url' => '#'],
        ['icon' => 'bi-facebook',  'url' => '#'],
        ['icon' => 'bi-whatsapp',  'url' => 'https://wa.me/62811789111'],
    ],
];
