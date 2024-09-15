# crud-system

crud-system is a laravel package that helps you to generate a complete crud system for your model.

## Installation

```bash
composer require --dev w88/crud-system
```

## Usage

```bash
php artisan fr:crud-make {name} {module}
```

## Example

```bash
php artisan fr:crud-make User users
```

## Configuration

```php
/**
 * Configuration array for the CRUD system
 *
 * @return array The configuration settings
 */
return [
    // Model name
    'name' => 'User',

    // Dashboard settings
    // Dashboard settings
    // These settings control the visibility and functionality of various dashboard features
    'dashboard' => [
        'create' => true,  // Enable/disable create functionality (default: true)
        'update' => true,  // Enable/disable update functionality (default: true)
        'profile' => true, // Enable/disable profile view (default: true)
        'delete' => true,  // Enable/disable delete functionality (default: true)
        'table_search' => true,  // Enable/disable table search feature (default: true)
        'table_filter' => true,  // Enable/disable table filtering (default: true)
        'table_export' => true,  // Enable/disable table export feature (default: true)
    ],

    // Client API settings
    // These settings control which API endpoints are available for client-side use
    'client_api' => [
        'list' => true,    // Enable/disable list API endpoint (default: true)
        'create' => true,  // Enable/disable create API endpoint (default: true)
        'update' => true,  // Enable/disable update API endpoint (default: true)
        'delete' => true,  // Enable/disable delete API endpoint (default: true)
        'show' => true,    // Enable/disable show (single item) API endpoint (default: true)
    ],

    // Note: Changing any value to false will disable the corresponding feature
    // in the dashboard or API. This allows for fine-grained control over
    // the CRUD functionality exposed to users and client applications.

    /**
     * Field definitions for the CRUD system
     */
    'fields' => [
        // Example field: 'title'
        'title' => [
            'type' => 'text', // Field type (e.g., text, number, date)
            'label' => 'Title', // Label for the field (used in forms and lists)
            // 'enum' => ['key' => 'value'], // Possible values for selection or checkbox fields
            'nullable' => true, // Whether the field can be null (default: false)
            'default' => 'string_test', // Default value for the field
            'migrationType' => 'string', // Database column type (default: same as 'type')
            'add_filter' => true, // Add a custom filter for this field (default: false)
            'translatable' => true, // Whether the field is translatable (default: false)
            'validation' => [ // Laravel validation rules (optional)
                // 'required',
                // 'string',
            ],
            // 'relation' => true, // or array
            'relation' => [
                'constrained' => true, // optional
                'onUpdate' => 'cascade', // optional
                'onDelete' => 'set null', // optional
                'table' => 'categories', // optional
                'foreign_key' => 'category_id', // optional
            ],
            'frontend' => [
                'sortable' => false, // Allow sorting in frontend tables (default: true)
                'searchable' => false, // Allow searching in frontend tables (default: true)
                'exportable' => false, // Include in exports (default: true)
                'advanced_searchable' => false, // Include in advanced search (default: true)
                // 'advanced_search_name' => 'title', // Custom name for advanced search (default: field name)
                'hidden' => [ // Hide field in different views
                    'create' => true, // Hide in create form (default: false)
                    'update' => true, // Hide in update form (default: false)
                    'list'  => true, // Hide in list view (default: false)
                ],
                'visible_list' => true, // Show in list view (default: false)
            ]
        ],
        // Add more fields as needed
    ],

    /**
     * Define the relationships for the CRUD model
     */
    'relations' => [
        [
            // Example of a one-to-one relationship
            'type' => 'hasOne',
            'table' => 'phone',
            // 'foreign_key' => 'user_id',
            // 'local_key' => 'id',

            // Output in model:
            // public function phone()
            // {
            //     return $this->hasOne(Phone::class);
            // }

            // Example of a many-to-one relationship
            'type' => 'belongsTo',
            'table' => 'department',
            // 'foreign_key' => 'department_id',
            // 'local_key' => 'id',

            // Output in model:
            // public function department()
            // {
            //     return $this->belongsTo(Department::class);
            // }

            // Example of a one-to-many relationship
            'type' => 'hasMany',
            'table' => 'comment',
            // 'foreign_key' => 'comment_id',
            // 'local_key' => 'id',

            // Output in model:
            // public function comments()
            // {
            //     return $this->hasMany(Comment::class);
            // }

            // Example of a many-to-many relationship
            'type' => 'belongsToMany',
            'table' => 'phone',
            // 'local_key' => 'user_id',
            // 'foreign_key' => 'phone_id',

            // Output in model:
            // public function phones()
            // {
            //     return $this->belongsToMany(Phone::class)
            //                 ->withPivot('active')
            //                 ->withTimestamps();
            // }
            
            // Define pivot table attributes for many-to-many relationships
            'pivot' => [
                'active' => [
                    'type' => 'boolean',
                    'default' => false,
                ],
                // This will add withPivot('active') in the model relationship
            ],
            
            // Set to false to skip migration creation (default is true)
            'migration' => false,
        ]
    ],

    // Permissions: Determines whether to add permissions to the permission file with translations
    'permissions' => true,

    // Timestamps: Includes created_at and updated_at columns in the database table
    'timestamps' => true,

    // Soft Deletes: When enabled, adds a deleted_at column for soft deletion functionality
    'soft_deletes' => false,

    // Multi Selection: Allows multiple items to be selected for actions like delete in the frontend list
    'multi_selection' => true,

    // Seeder: Configuration for database seeding
    'seeder' => false,
    // Uncomment and modify the following to enable seeding with a specific count
    // 'seeder' => [
    //     'count' => 10, // Number of records to seed
    // ],

```

