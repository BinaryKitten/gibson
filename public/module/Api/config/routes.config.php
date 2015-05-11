<?php
namespace Api;

return [
    'SanRestful' => [
        'type'    => 'Literal',
        'options' => [
            'route'    => '/api',
            'defaults' => [
                'controller' => Controller\IndexController::class,
            ],
        ],

        'may_terminate' => true,
        'child_routes' => [
            'client' => [
                'type'    => 'Segment',
                'options' => [
                    'route'    => '/client[/:action]',
                    'constraints' => [
                        'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                    ],
                    'defaults' => [
                        'controller' => 'SampleClient',
                        'action'     => 'index'
                    ],
                ],
            ],
        ],
    ],

];
