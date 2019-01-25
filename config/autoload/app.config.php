<?php
return [
    'routes' => [
        [
            'name' => 'Generate',
            'short_description' => 'Generate RNG Data',
            'handler' => Application\Command\Generate::class,
        ],
    ],
    'service_manager' => [
        'invokables' => [
        ],
        'factories' => [
            Application\Command\Generate::class => function ($container, $requestedName) {
                return new Application\Command\Generate(
                    $container->get('config')['general']
                );
            },
        ],
    ],
];
