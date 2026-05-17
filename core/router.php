<?php
function route(): void {
    $uri = $_SERVER['REQUEST_URI'];
    $uri = strtok($uri, '?');
    $uri = str_replace('/khok', '', $uri);
    $uri = '/' . trim($uri, '/');
    if ($uri === '') $uri = '/';

    $routes = [
        '/'                    => __DIR__ . '/../pages/home.php',
        '/boxes'               => __DIR__ . '/../pages/boxes.php',
        '/checkout'            => __DIR__ . '/../pages/checkout.php',
        '/track'               => __DIR__ . '/../pages/track.php',
        '/login'               => __DIR__ . '/../pages/login.php',
        '/register'            => __DIR__ . '/../pages/register.php',
        '/logout'              => __DIR__ . '/../pages/logout.php',
        '/profile'             => __DIR__ . '/../pages/profile.php',
        '/admin'               => __DIR__ . '/../admin/pages/dashboard.php',
        '/admin/orders'        => __DIR__ . '/../admin/pages/orders.php',
        '/admin/orders/detail' => __DIR__ . '/../admin/pages/order_detail.php',
        '/admin/products'      => __DIR__ . '/../admin/pages/products.php',
        '/admin/users'         => __DIR__ . '/../admin/pages/users.php',
        '/admin/users/profile' => __DIR__ . '/../admin/pages/user_profile.php',
        '/admin/delivery'      => __DIR__ . '/../admin/pages/delivery.php',
        '/admin/accounting'    => __DIR__ . '/../admin/pages/accounting.php',
    ];

    if (array_key_exists($uri, $routes) && file_exists($routes[$uri])) {
        require $routes[$uri];
    } else {
        http_response_code(404);
        require __DIR__ . '/../pages/404.php';
    }
}