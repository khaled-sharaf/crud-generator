# crud-system

crud-system is a laravel package that helps you to generate a complete crud system for your model.

## Installation

```bash
composer require --dev Khaled/crud-system
```

## Usage

```bash
php artisan crud-make {name} {--module=}
```

## Example

```bash
php artisan crud-make User Users
```

## Generate a complete crud system for a models

```bash
php artisan crud-generate {name?} {--module=} {--force}
```

Example:
```bash
php artisan crud-generate User --module=Users
```

---

## Basic Configuration

### 1. `name`
- **Description**: The name of the model for which you want to generate CRUD operations.
- **Default Value**: `{{ MODEL_NAME }}`

### 2. `frontendModule`
- **Description**: The name of the module to be used in the frontend.
- **Default Value**: `{{ MODULE_NAME }}`

### 3. `lockAfterGenerate`
- **Description**: If set to `true`, the generated files will be locked to prevent further modifications.
- **Default Value**: `true`

---

## Dashboard API Configuration (`dashboardApi`)

### 1. `create`, `show`, `edit`, `delete`
- **Description**: Enable or disable CRUD operations in the dashboard.
- **Default Value**: `true`

### 2. `activation`
- **Description**: Enable or disable the activation feature for the model. You can set it to `true` or customize it further using `column` to specify the column to be used.
- **Default Value**: `true`

### 3. `lookup`
- **Description**: Enable or disable the lookup feature. You can set it to `true` or specify a specific field for lookup.
- **Default Value**: `true`

---

## Client API Configuration (`clientApi`)

### 1. `list`, `create`, `show`, `edit`, `delete`
- **Description**: Enable or disable CRUD operations in the API.
- **Default Value**: `true`

---

## Additional Options (`options`)

### 1. `icon`
- **Description**: Icon to be used in the frontend.
- **Default Value**: `view_list`

### 2. `showPopup`, `formPopup`
- **Description**: Display the form in a popup window.
- **Default Value**: `true`

### 3. `permissions`
- **Description**: Add permissions to the permissions file with translation.
- **Default Value**: `true`

### 4. `softDeletes`
- **Description**: Enable soft delete functionality.
- **Default Value**: `true`

### 5. `seeder`
- **Description**: Enable the creation of a Seeder file with Factory.
- **Default Value**: `true`

### 6. `tableSettings`
- **Description**: Configure table settings for the frontend.
  - `multiSelection`: Enable multi-selection for delete or other actions in the frontend list.
  - `tableSearch`: Enable table search.
  - `tableFilter`: Enable table filter.
  - `tableExport`: Enable table export.
- **Default Value**: `false`

---

## Fields Configuration (`fields`)

### 1. `type`
- **Description**: The type of the field (e.g., `text`, `decimal`, `boolean`).
- **Default Value**: `text`

### 2. `label`
- **Description**: The label to be displayed in the frontend.
- **Default Value**: `Title`

### 3. `nullable`
- **Description**: Whether the field can be nullable.
- **Default Value**: `false`

### 4. `default`
- **Description**: The default value for the field.
- **Default Value**: `string_test`

### 5. `unique`
- **Description**: Whether the field should be unique.
- **Default Value**: `false`

### 6. `migrationType`
- **Description**: The type of the field in the migration file.
- **Default Value**: `decimal`

### 7. `migrationParams`
- **Description**: Additional parameters for the migration type (e.g., precision for `decimal`).
- **Default Value**: `[8, 2]`

### 8. `translatable`
- **Description**: Whether the field is translatable.
- **Default Value**: `false`

### 9. `validation`
- **Description**: Validation rules for the field (e.g., `required`).
- **Default Value**: `required`

### 10. `route`
- **Description**: Route to be used for boolean fields (e.g., `toggle-display`).
- **Default Value**: `null`

### 11. `lookup`
- **Description**: Enable or disable lookup for the field.
- **Default Value**: `false`

### 12. `filter`
- **Description**: Enable or disable filtering for the field.
- **Default Value**: `false`

### 13. `relation`
- **Description**: Define relationships for the field (e.g., `belongsTo`, `hasMany`).
- **Default Value**: `null`

---

## Relationships Configuration (`relations`)

### 1. `type`
- **Description**: The type of relationship (e.g., `belongsTo`, `hasMany`, `belongsToMany`).
- **Default Value**: `belongsTo`

### 2. `model`
- **Description**: The model to which the relationship is defined.
- **Default Value**: `null`

### 3. `foreignKey`
- **Description**: The foreign key for the relationship.
- **Default Value**: `null`

### 4. `localKey`
- **Description**: The local key for the relationship.
- **Default Value**: `null`

### 5. `deleteRelation`
- **Description**: Whether to delete the relationship when the model is deleted.
- **Default Value**: `false`

### 6. `checkOnDelete`
- **Description**: Check for relationships before deleting the model.
- **Default Value**: `false`

### 7. `pivot`
- **Description**: Define pivot table attributes for many-to-many relationships.
- **Default Value**: `null`

---

## Example Configuration

```php
return [
    'name' => 'Product',
    'frontendModule' => 'Products',
    'lockAfterGenerate' => true,

    'dashboardApi' => [
        'create' => true,
        'show' => true,
        'edit' => true,
        'delete' => true,
        'activation' => true,
        'lookup' => 'title',
    ],

    'clientApi' => [
        'list' => true,
        'create' => true,
        'show' => true,
        'edit' => true,
        'delete' => true,
    ],

    'options' => [
        'icon' => 'view_list',
        'showPopup' => true,
        'formPopup' => true,
        'permissions' => true,
        'softDeletes' => true,
        'seeder' => true,
    ],

    'fields' => [
        'title' => [
            'type' => 'text',
            'label' => 'Title',
            'nullable' => true,
            'default' => 'Default Title',
            'unique' => true,
            'translatable' => true,
            'validation' => 'required',
        ],
    ],

    'relations' => [
        'category' => [
            'type' => 'belongsTo',
            'model' => 'App\Models\Category',
            'foreignKey' => 'category_id',
        ],
    ],
];
```

## Advanced Configuration

### 1. Field Options

You can further customize fields using the following options:

- **keyShowInFront**  
  **Description:** A custom key to display in the frontend. You can use dynamic values like `{model}.title`.  
  **Example:**
  ```php
  'keyShowInFront' => "{model}.title ? 'Mr. ' + {model}.title : ''"
  ```

- **notDatabase**  
  **Description:** If set to true, the field will not be added to the database migration.  
  **Default Value:** false

- **frontend**  
  **Description:** Customize how the field behaves in the frontend.
  - **fullWidth:** Make the field take up the full width.
  - **visibleList:** Show the field in the list view.
  - **sortable:** Allow sorting by this field.
  - **exportable:** Include the field in table exports (e.g., CSV).
  - **searchable:** Enable searching by this field.
  - **advancedSearchable:** Enable advanced searching by this field.
  - **hidden:** Hide the field in specific views (list, show, create, edit).

- **filter**  
  **Description:** Enable filtering for the field. You can specify `single` or `multi` for filter types.  
  **Example:**
  ```php
  'filter' => 'multi'
  ```

- **relation**  
  **Description:** Define relationships for the field. You can specify the relationship type (`belongsTo`, `hasMany`, etc.), foreign key, and other options.  
  **Example:**
  ```php
  'relation' => [
      'type' => 'belongsTo',
      'model' => 'App\Models\User',
      'foreignKey' => 'user_id',
  ]
  ```

### 2. Relationships

You can define relationships between models in the relations section. Supported relationship types include:

- `belongsTo`
- `hasMany`
- `belongsToMany`
- `morphOne`
- `morphMany`
- `morphToMany`
- `morphedByMany`

**Example:**

```php
'relations' => [
    'user' => [
        'type' => 'belongsTo',
        'model' => 'App\Models\User',
        'foreignKey' => 'user_id',
    ],
    'comments' => [
        'type' => 'hasMany',
        'model' => 'App\Models\Comment',
        'foreignKey' => 'post_id',
    ],
    'tags' => [
        'type' => 'belongsToMany',
        'model' => 'App\Models\Tag',
        'table' => 'post_tag',
        'foreignKey' => 'post_id',
        'localKey' => 'tag_id',
    ],
],
```

### 3. Pivot Table Attributes
For many-to-many relationships, you can define pivot table attributes using the pivot key.

**Example:**
```php
'pivot' => [
    'active' => [
        'type' => 'boolean',
        'nullable' => true,
        'default' => false,
    ],
],
```
