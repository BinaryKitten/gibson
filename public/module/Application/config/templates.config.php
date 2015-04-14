<?php

namespace Application;

$viewDir = realpath(__DIR__ . '/../view/');

return  [
    'layout/layout'           => $viewDir . '/layout/layout.phtml',
    'layout/auth'             => $viewDir . '/layout/auth.phtml',

    'error/404'               => $viewDir . '/error/404.phtml',
    'error/index'             => $viewDir . '/error/index.phtml',

    'application/index/index' => $viewDir . '/index/index.phtml',
    'application/auth/login'   => $viewDir . '/auth/login.phtml',
    'application/auth/logout'   => $viewDir . '/auth/logout.phtml',
    'application/auth/migrate' => $viewDir . '/auth/migrate.phtml',
    'application/registration/index' => $viewDir . '/registration/index.phtml',
    'application/registration/disabled' => $viewDir . '/registration/disabled.phtml',
];