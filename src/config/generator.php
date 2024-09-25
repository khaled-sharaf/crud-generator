<?php

return [
    'frontend_path' => 'resources/admin',

    'client_directory' => 'client',

    'route_methods' => ['index', 'show', 'store', 'update', 'destroy'],
    
    'generators' => [
        'backend' => ['migration', 'model', 'route', 'controller', 'service', 'request', 'resource', 'seeder', 'constant', 'lookup', 'lang', 'permission'],
        // 'frontend' => ['list', 'create', 'update', 'profile', 'lang', 'sidebar', 'route'],
        'clientApi' => ['route', 'controller', 'service', 'request', 'resource'],
    ],
];