<?php
namespace Api;

$routes = require __DIR__ . '/routes.config.php';
$controllers = require __DIR__ . '/controllers.config.php';

return [
    'router' => [
        'routes' => $routes,
    ],
    'controllers' => [
        'invokables' => $controllers,
    ],
];
