<?php

return [
    'name' => '{{ MODEL_NAME }}',
    'label' => 'Post',

    'dashboard' => [
        'create' => true,
        'update' => true,
        'profile' => true,
        'delete' => true,
        'table_search' => true,
        'table_filter' => true,
        'table_export' => true,
    ],

    'client_api' => [],
    // 'client_api' => [    ER     //     'list' => true,
    //     'create' => true,
    //     'update' => true,
    //     'delete' => true,
    //     'show' => true,
    // ],

    'fields' => [
        'title' => [
            'type' => 'text',
            'label' => 'Title', // translation in validation and frondend list and form
            // 'enum' => ['key' => 'value'], // available values - selection or checkbox - will be create constant with this values
            'nullable' => true, // default false - nullable in migration and frontend
            'default' => 'string_test', // no default
            'migration_type' => 'string', // default is: field type
            'add_filter' => true, // default false - Will be add custom filter in backend and frontend
            'translatable' => true, // default false
            'validation' => [ // optional - default: nullable -> request validation [laravel]
                // 'required',
                // 'string',
            ],
            'frontend' => [
                'sortable' => false, // default true
                'searchable' => false, // default true
                'exportable' => false, // default true
                'advanced_searchable' => false, // default true
                // 'advanced_search_name' => 'title', // default is: field name
                'hidden' => [
                    'create' => true, // default false
                    'update' => true, // default false
                    'list'  => true, // default false
                ],
                'visible_list' => true, // default false
            ]
        ],
    ],

    'relations' => [
        [
            'type' => 'hasOne', // hasOne, belongsTo, hasMany, belongsToMany
            'table' => 'phone',
            // 'foreign_key' => 'user_id',
            // 'local_key' => 'id',

            'type' => 'belongsTo',
            'table' => 'department',
            // 'foreign_key' => 'department_id',
            // 'local_key' => 'id',

            'type' => 'hasMany',
            'table' => 'comment',
            // 'foreign_key' => 'department_id',
            // 'local_key' => 'id',

            'type' => 'belongsToMany',
            'table' => 'phone',
            // 'local_key' => 'user_id',
            // 'foreign_key' => 'phone_id',
            'pivot' => [
                'active' => [
                    'type' => 'boolean',
                    'default' => false,
                    // 'nullable' => true,
                ],
                // add withPivot in model
            ],
            'migration' => false, // default true
        ]
    ],

    'permissions' => true, // Add permissions to permission file with translation
    'timestamps' => true, // Include timestamps
    'soft_deletes' => false, // Enable soft deletes
    'multi_selection' => true, // Enable multi selection for delete or any action in frontend list

    'seeder' => false,
    // 'seeder' => [
    //     'count' => 10,
    // ],
];
