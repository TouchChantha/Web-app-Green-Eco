<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$routes = $app->routes->getRoutes();
foreach ($routes as $route) {
    echo $route->uri() . "\n";
}
