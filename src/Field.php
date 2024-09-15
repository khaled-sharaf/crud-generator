<?php

namespace W88\CrudSystem;

class Field
{
    public static function getMigrationType(array $field): string
    {
        if (isset($field['migrationType'])) return $field['migrationType'];
        if (isset($field['translatable']) && $field['translatable'] === true && isset(static::translatableFields()[$field['type']])) {
            return static::translatableFields()[$field['type']];
        }
        if (isset($field['relation'])) {
            $isConstrained = isset($field['relation']['constrained']) && $field['relation']['constrained'] === true;
            return $isConstrained ? 'foreignId' : 'unsignedBigInteger';
        }
        return static::types()[$field['type']];
    }

    public static function types(): array
    {
        return array_merge(static::normalFields(), static::jsonFields());
    }

    public static function normalFields(): array
    {
        return [
            'text' => 'string',
            'number' => 'string',
            'password' => 'string',
            'textarea' => 'text',
            'editor' => 'mediumText',
            'color' => 'string',
            'boolean' => 'boolean',
            'time' => 'time',
            'date' => 'date',
            'datetime' => 'dateTime',
            'image' => 'string',
            'video' => 'string',
            'file' => 'string',
            'checkbox' => 'string',
            'radio' => 'string',
            'select' => 'string',
            'slider' => 'string',
        ];
    }

    public static function jsonFields(): array
    {
        return [
            'range_date' => 'array',
            'multi_date' => 'array',
            'multi_range_date' => 'array',
            'multi_image' => 'array',
            'multi_video' => 'array',
            'multi_file' => 'array',
            'multi_checkbox' => 'array',
            'multi_select' => 'array',
            'range' => 'array',
            'array' => 'array',
            'location' => 'array',
        ];
    }

    public static function translatableFields(): array
    {
        return [
            'text' => 'text',
            'textarea' => 'mediumText',
            'editor' => 'longText',
            'array' => 'mediumText',
        ];
    }

}