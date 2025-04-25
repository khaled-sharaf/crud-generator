<?php

return [
    'frontend_path' => 'resources/admin',

    'client_directory' => 'client',

    'route_methods' => ['index', 'show', 'store', 'update', 'destroy'],
    
    'generators' => [
        'backend' => [
            'migration',
            'model',
            'route',
            'controller',
            'service',
            'request',
            'resource',
            'seeder',
            'constant',
            'lookup',
            'lang',
            'permission',
        ],
        
        'clientApi' => [
            'route',
            'controller',
            'service',
            'request',
            'resource',
        ],

        'frontend' => [
            'list',
            'form',
            'create',
            'edit',
            'show',
            'lookup',
            'lang',
            'sidebar',
            'route'
        ],
    ],
];