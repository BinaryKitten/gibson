<?php

namespace Web;

$viewDir = realpath(__DIR__ . '/../view/');

return  [
    'layout/layout'           => $viewDir . '/layout/layout.phtml',
    'layout/auth'             => $viewDir . '/layout/auth.phtml',

    'error/404'               => $viewDir . '/error/404.phtml',
    'error/index'             => $viewDir . '/error/index.phtml',

    'web/index/index' => $viewDir . '/index/index.phtml',
    'web/auth/login'   => $viewDir . '/auth/login.phtml',
    'web/auth/logout'   => $viewDir . '/auth/logout.phtml',
    'web/auth/migrate' => $viewDir . '/auth/migrate.phtml',
    'web/registration/index' => $viewDir . '/registration/index.phtml',
    'web/registration/disabled' => $viewDir . '/registration/disabled.phtml',
];