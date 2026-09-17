<?php

declare(strict_types=1);

return [
    'name' => 'Northstar Store',
    'short_name' => 'Northstar',
    'tagline' => 'A practical PHP e-commerce starter.',
    'description' => 'Reusable PHP storefront with a session basket, SQLite orders, Stripe Checkout and SMTP email.',
    'site_url' => env('SITE_URL', 'http://localhost:8000'),
    'locale' => 'en_GB',
    'currency' => 'gbp',
    'currency_symbol' => '£',
    'contact_email' => env('CONTACT_EMAIL', 'hello@example.com'),
    'theme' => [
        'brand' => '#172033',
        'brand_dark' => '#0d1422',
        'accent' => '#5ea6ff',
        'surface' => '#f5f7fb',
        'surface_alt' => '#eaf0f8',
        'text' => '#18202d',
        'muted' => '#5a6677',
    ],
    'navigation' => [
        ['label' => 'Home', 'href' => '/'],
        ['label' => 'Shop', 'href' => '/products'],
        ['label' => 'Contact', 'href' => '/contact'],
    ],
    'checkout' => [
        'allowed_countries' => ['GB'],
        'collect_billing_address' => true,
        'collect_shipping_address' => true,
        'shipping_label' => 'Free UK delivery',
        'shipping_description' => 'Delivery is included for this demonstration store.',
    ],
    'products' => [
        ['slug' => 'classic-tshirt', 'name' => 'Classic Cotton T-Shirt', 'summary' => 'A simple everyday cotton tee.', 'description' => 'A versatile cotton T-shirt with a clean fit for everyday wear.', 'price_pence' => 2500, 'stock' => 100, 'image' => '/images/products/classic-tshirt.svg'],
        ['slug' => 'everyday-hoodie', 'name' => 'Everyday Hoodie', 'summary' => 'A comfortable mid-weight hoodie.', 'description' => 'A practical mid-weight hoodie designed for everyday use.', 'price_pence' => 4500, 'stock' => 50, 'image' => '/images/products/everyday-hoodie.svg'],
        ['slug' => 'canvas-tote', 'name' => 'Canvas Tote Bag', 'summary' => 'A practical reusable shopping bag.', 'description' => 'A durable canvas tote for groceries, commuting and everyday carry.', 'price_pence' => 1800, 'stock' => 75, 'image' => '/images/products/canvas-tote.svg'],
        ['slug' => 'stoneware-mug', 'name' => 'Stoneware Mug', 'summary' => 'A durable everyday mug.', 'description' => 'A sturdy stoneware mug for coffee, tea and everyday use.', 'price_pence' => 1200, 'stock' => 60, 'image' => '/images/products/stoneware-mug.svg'],
    ],
    'social' => [
        ['label' => 'GitHub', 'href' => 'https://github.com/kodakodra'],
    ],
];
