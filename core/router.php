<?php
function route(): void {
    $uri = $_SERVER['REQUEST_URI'];
    $uri = strtok($uri, '?');
    $uri = str_replace('/khok', '', $uri);
    $uri = '/' . trim($uri, '/');
    if ($uri === '') $uri = '/';

    $routes = [
        // Public pages
        '/'                       => __DIR__ . '/../pages/home.php',
        '/boxes'                  => __DIR__ . '/../pages/boxes.php',
        '/checkout'               => __DIR__ . '/../pages/checkout.php',
        '/track'                  => __DIR__ . '/../pages/track.php',
        '/login'                  => __DIR__ . '/../pages/login.php',
        '/register'               => __DIR__ . '/../pages/register.php',
        '/logout'                 => __DIR__ . '/../pages/logout.php',
        '/profile'                => __DIR__ . '/../pages/profile.php',

        // Payment pages
        '/payment/success'        => __DIR__ . '/../pages/payment_success.php',
        '/payment/failed'         => __DIR__ . '/../pages/payment_failed.php',

        // Payment APIs
        '/api/payment/esewa'      => __DIR__ . '/../api/payment/esewa_init.php',
        '/api/payment/esewa/verify' => __DIR__ . '/../api/payment/esewa_verify.php',
        '/api/payment/fonepay'    => __DIR__ . '/../api/payment/fonepay_init.php',
        '/api/payment/fonepay/verify' => __DIR__ . '/../api/payment/fonepay_verify.php',

        // Auth APIs
        '/api/auth/login'         => __DIR__ . '/../api/auth/login.php',
        '/api/auth/register'      => __DIR__ . '/../api/auth/register.php',
        '/api/auth/update_profile'=> __DIR__ . '/../api/auth/update_profile.php',

        // Order APIs
        '/api/orders/create'      => __DIR__ . '/../api/orders/create.php',
        '/api/orders/track'       => __DIR__ . '/../api/orders/track.php',

        // Admin pages
        '/admin'                  => __DIR__ . '/../admin/pages/dashboard.php',
        '/admin/orders'           => __DIR__ . '/../admin/pages/orders.php',
        '/admin/orders/detail'    => __DIR__ . '/../admin/pages/order_detail.php',
        '/admin/products'         => __DIR__ . '/../admin/pages/products.php',
        '/admin/users'            => __DIR__ . '/../admin/pages/users.php',
        '/admin/users/profile'    => __DIR__ . '/../admin/pages/user_profile.php',
        '/admin/delivery'         => __DIR__ . '/../admin/pages/delivery.php',
        '/admin/accounting'       => __DIR__ . '/../admin/pages/accounting.php',
    ];

    if (array_key_exists($uri, $routes) && file_exists($routes[$uri])) {
        require $routes[$uri];
    } else {
        http_response_code(404);
        require __DIR__ . '/../pages/404.php';
    }
}