<?php
namespace Web;

return [
    'home'     => [
        'type'    => 'Zend\Mvc\Router\Http\Literal',
        'options' => [
            'route'    => '/',
            'defaults' => [
                'controller' => Controller\IndexController::class,
                'action'     => 'index',
            ],
        ],
    ],
    'login'    => [
        'type'          => 'segment',
        'options'       => [
            'route'    => '/login',
            'defaults' => [
                'controller' => Controller\AuthController::class,
                'action'     => 'login',
            ]
        ],
        'may_terminate' => true,
        'child_routes'  => [
            'migrate' => [
                'type'    => 'Segment',
                'options' => [
                    'route'    => '/migrate',
                    'defaults' => [
                        'controller' => Controller\AuthController::class,
                        'action'     => 'migrate'
                    ],
                ],
            ],
        ],
    ],
    'logout'   => [
        'type'    => 'Literal',
        'options' => [
            'route'    => '/logout',
            'defaults' => [
                'controller' => Controller\AuthController::class,
                'action'     => 'logout',
            ],
        ],
    ],
    'register' => [
        'type'    => 'Literal',
        'options' => [
            'route'    => '/register',
            'defaults' => [
                'controller' => Controller\RegistrationController::class,
                'action'     => 'index',
            ],
        ],
    ],
    'ldap'     => [
        'type'    => 'Segment',
        'options' => [
            'route'    => '/ldap/[:action]',
            'defaults' => [
                'controller' => Controller\LdapController::class,
                'action'     => 'index'
            ]
        ]
    ]
];
