<?php
namespace Application;

return [
    'home' => [
        'type' => 'Zend\Mvc\Router\Http\Literal',
        'options' => [
            'route'    => '/',
            'defaults' => [
                'controller' => Controller\IndexController::class,
                'action'     => 'index',
            ],
        ],
    ],
    'login' => [
        'type'    => 'Literal',
        'options' => [
            'route'    => '/login',
            'defaults' => [
                'controller'    => Controller\AuthController::class,
                'action'        => 'login',
            ],
            'may_terminate' =>  true,
            'child_routes' => [
                'migrate' => [
                    'type' => 'Literal',
                    'options' => [
                        'route' => '/migrate',
                        'defaults' => [
                            'controller' => Controller\AuthController::class,
                            'action' => 'migrate'
                        ]
                    ]
                ]
            ]
        ],

    ],

];
