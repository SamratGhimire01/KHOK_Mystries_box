<?php
// ─────────────────────────────────────────
//  K HO K — Router / Front Controller
//  core/router.php
// ─────────────────────────────────────────

function route(string $path): void {
    $base    = dirname($_SERVER['SCRIPT_NAME']);
    $request = str_replace($base, '', $_SERVER['REQUEST_URI']);
    $request = strtok($request, '?'); // strip query string
    $request = rtrim($request, '/') ?: '/';

    $routes = [
        '/'               => __DIR__ . '/../pages/home.php',
        '/boxes'          => __DIR__ . '/../pages/boxes.php',
        '/checkout'       => __DIR__ . '/../pages/checkout.php',
        '/track'          => __DIR__ . '/../pages/track.php',
        '/login'          => __DIR__ . '/../pages/login.php',
        '/register'       => __DIR__ . '/../pages/register.php',
        '/logout'         => __DIR__ . '/../pages/logout.php',
        '/profile'        => __DIR__ . '/../pages/profile.php',
        '/admin'          => __DIR__ . '/../admin/pages/dashboard.php',
        '/admin/orders'   => __DIR__ . '/../admin/pages/orders.php',
        '/admin/products' => __DIR__ . '/../admin/pages/products.php',
        '/admin/users'    => __DIR__ . '/../admin/pages/users.php',
        '/admin/delivery' => __DIR__ . '/../admin/pages/delivery.php',
    ];

    if (array_key_exists($request, $routes) && file_exists($routes[$request])) {
        require $routes[$request];
    } else {
        http_response_code(404);
        require __DIR__ . '/../pages/404.php';
    }
}
