<?php

return [
    'types' => [
        /* ======================== Normal ======================== */
        'normal' => [
            'text' => [
                'migration' => 'string',
                'seeder' => 'fake()->text(20)',
                // 'stub_view' => 'text',
                'stub_form' => 'text',
            ],
            'number' => [
                'migration' => 'string',
                'seeder' => 'fake()->numberBetween(1, 20)',
                // 'stub_view' => 'text',
                'stub_form' => 'text',
            ],
            'password' => [
                'migration' => 'string',
                'seeder' => "\Illuminate\Support\Facades\Hash::make('12345678')",
                // 'stub_view' => 'text',
                'stub_form' => 'password',
            ],
            'textarea' => [
                'migration' => 'text',
                'seeder' => 'fake()->text()',
                // 'stub_view' => 'text',
                'stub_form' => 'text',
            ],
            'editor' => [
                'migration' => 'mediumText',
                'seeder' => 'fake()->text()',
                // 'stub_view' => 'editor',
                'stub_form' => 'editor',
            ],
            'color' => [
                'migration' => 'string',
                'seeder' => 'fake()->hexColor()',
                // 'stub_view' => 'color',
                // 'stub_form' => 'color',
            ],
            'boolean' => [
                'migration' => 'boolean',
                'seeder' => 'fake()->boolean()',
                // 'stub_view' => 'boolean',
                'stub_form' => 'boolean',
            ],
            'time' => [
                'migration' => 'time',
                'seeder' => 'fake()->time()',
                // 'stub_view' => 'text',
                // 'stub_form' => 'time',
            ],
            'date' => [
                'migration' => 'date',
                'seeder' => 'fake()->date()',
                // 'stub_view' => 'text',
                // 'stub_form' => 'date',
            ],
            'datetime' => [
                'migration' => 'dateTime',
                'seeder' => 'fake()->dateTime()',
                // 'stub_view' => 'text',
                // 'stub_form' => 'datetime',
            ],
            'timestamp' => [
                'migration' => 'timestamp',
                'seeder' => 'fake()->dateTime()',
                // 'stub_view' => 'text',
                // 'stub_form' => 'datetime',
            ],
            'image' => [
                'migration' => 'string',
                'seeder' => 'null',
                // 'stub_view' => 'file',
                'stub_form' => 'file',
            ],
            'video' => [
                'migration' => 'string',
                'seeder' => 'null',
                // 'stub_view' => 'file',
                'stub_form' => 'file',
            ],
            'file' => [
                'migration' => 'string',
                'seeder' => 'null',
                // 'stub_view' => 'file',
                'stub_form' => 'file',
            ],
            'checkbox' => [
                'migration' => 'string',
                'seeder' => 'null',
                // 'stub_view' => 'text',
                'stub_form' => 'checkbox',
            ],
            'select' => [
                'migration' => 'string',
                'seeder' => 'null',
                // 'stub_view' => 'text',
                'stub_form' => 'select',
            ],
            'radio' => [
                'migration' => 'string',
                'seeder' => 'null',
                // 'stub_view' => 'text',
                'stub_form' => 'optionGroup',
            ],
            'slider' => [
                'migration' => 'unsignedInteger',
                'seeder' => 'fake()->numberBetween(1, 10)',
                // 'stub_view' => 'text',
                // 'stub_form' => 'slider',
            ],
        ],

        /* ======================== Json ======================== */
        'json' => [
            'range_date' => [
                'migration' => 'json',
                'seeder' => '[]',
                // 'stub_view' => 'range',
                // 'stub_form' => 'date',
            ],
            'multi_date' => [
                'migration' => 'json',
                'seeder' => '[]',
                // 'stub_view' => 'arrayOfText',
                // 'stub_form' => 'date',
            ],
            'multi_range_date' => [
                'migration' => 'json',
                'seeder' => '[]',
                // 'stub_view' => 'arrayOfText',
                // 'stub_form' => 'date',
            ],
            'multi_image' => [
                'migration' => 'json',
                'seeder' => '[]',
                // 'stub_view' => 'multiFile',
                'stub_form' => 'multiFile',
            ],
            'multi_video' => [
                'migration' => 'json',
                'seeder' => '[]',
                // 'stub_view' => 'multiFile',
                'stub_form' => 'multiFile',
            ],
            'multi_file' => [
                'migration' => 'json',
                'seeder' => '[]',
                // 'stub_view' => 'multiFile',
                'stub_form' => 'multiFile',
            ],
            'multi_checkbox' => [
                'migration' => 'json',
                'seeder' => '[]',
                // 'stub_view' => 'arrayOfBadge',
                'stub_form' => 'optionGroup',
            ],
            'multi_select' => [
                'migration' => 'json',
                'seeder' => '[]',
                // 'stub_view' => 'arrayOfBadge',
                'stub_form' => 'select',
            ],
            'range' => [
                'migration' => 'json',
                'seeder' => '[]',
                // 'stub_view' => 'range',
                // 'stub_form' => 'range',
            ],
            'array' => [
                'migration' => 'json',
                'seeder' => '[]',
                // 'stub_view' => 'arrayOfText',
                // 'stub_form' => 'array',
            ],
            'location' => [
                'migration' => 'json',
                'seeder' => '[]',
                // 'stub_view' => 'location',
                // 'stub_form' => 'location',
            ],
        ],

        /* ======================== Translatable ======================== */
        'translatable' => [
            'text' => 'json',
            'textarea' => 'json',
            'editor' => 'json',
            'array' => 'json',
        ],

        /* ======================== File ======================== */
        'file' => [
            'image',
            'video',
            'file',
        ],

        /* ======================== Filter ======================== */
        'filter' => [
            'checkbox',
            'select',
            'radio',
        ],
    ],
];