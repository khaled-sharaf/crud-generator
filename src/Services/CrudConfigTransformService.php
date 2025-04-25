<?php

namespace Khaled\CrudSystem\Services;

use Khaled\CrudSystem\Models\Crud;

class CrudConfigTransformService
{

    /**
     * Convert the config model to form
     * 
     * @param array $configModel
     * @return array
     */
    public function convertConfigToGenerate(Crud $crud): array
    {
        $config = [
            'name' => $crud->name,
            'frontendModule' => $crud->frontend_module,
            'dashboardApi' => $this->getDashboardApi($crud->current_config),
            'clientApi' => $this->getClientApi($crud->current_config),
            'options' => $this->getOptions($crud->current_config),
            'fields' => $this->getFields($crud->current_config),
            'relations' => $this->getRelations($crud->current_config),
        ];
        return $config;
    }

    private function getDashboardApi(array $config): array
    {
        $dashboardApi = $config['dashboardApi'];
        if ($dashboardApi['activation'] === true) {
            $dashboardApi['activation'] = [
                'default' => $config['activationDefault'],
                'column' => $config['activationColumnName'],
            ];
        }
        if ($dashboardApi['lookup'] === true) {
            $dashboardApi['lookup'] = [
                'label' => $config['lookupColumnLabel'],
                'value' => $config['lookupColumnValue'],
            ];
        }
        return $dashboardApi;
    }

    private function getClientApi(array $config): array|bool
    {
        if ($config['hasClientApi'] === true) {
            return $config['clientApi'];
        }
        return false;
    }

    private function getOptions(array $config): array
    {
        return $config['options'];
    }

    private function getFields(array $config): array
    {
        $fields = [];
        foreach ($config['fields'] as $key => $field) {
            $fields[$key] = $this->transformField($field);
        }
        return $fields;
    }

    private function getRelations(array $config): array
    {
        $relations = [];
        foreach ($config['relations'] as $key => $relation) {
            $relations[$key] = $this->transformRelation($relation);
        }
        return $relations;
    }
    
    private function transformField(array $field): array
    {
        $newField = [
            'type' => $field['type'],
            'label' => $field['label'],
            'frontend' => []
        ];
        $this->checkBoolean($field, $newField, 'notDatabase');
        $this->checkBoolean($field, $newField, 'nullable');
        $this->checkBoolean($field, $newField, 'unique');
        $this->checkBoolean($field, $newField, 'translatable');
        $this->checkBoolean($field, $newField, 'lookup');
        $this->checkBoolean($field, $newField, 'lookupFrontend');
        $this->checkString($field, $newField, 'lookupModel');
        $this->checkBoolean($field, $newField, 'lookupModel');
        $this->checkString($field, $newField, 'filter');
        $this->checkBoolean($field, $newField, 'filter');
        $this->checkArray($field, $newField, 'relation');
        $this->checkBoolean($field, $newField, 'relation');
        $this->checkString($field, $newField, 'keyShowInFront');
        $this->checkString($field, $newField, 'default');
        $this->checkString($field, $newField, 'migrationType');
        $this->checkString($field, $newField, 'validation');
        $this->checkString($field, $newField, 'route');
        $this->checkString($field, $newField, 'lookupModelLabel');
        $this->checkString($field, $newField, 'lookupModelValue');
        $this->checkString($field, $newField, 'filterRelationName');
        $this->checkString($field, $newField, 'filterRelationColumnName');
        $this->checkArray($field, $newField, 'migrationParams');
        $this->checkArray($field, $newField, 'options');
        
        $this->checkBoolean($field['frontend'], $newField['frontend'], 'fullWidth');
        $this->checkBoolean($field['frontend'], $newField['frontend'], 'visibleList');
        $this->checkBoolean($field['frontend'], $newField['frontend'], 'sortable');
        $this->checkBoolean($field['frontend'], $newField['frontend'], 'exportable');
        $this->checkBoolean($field['frontend'], $newField['frontend'], 'searchable');
        $this->checkBoolean($field['frontend'], $newField['frontend'], 'advancedSearchable');
        $this->checkString($field['frontend'], $newField['frontend'], 'searchableName');
        $this->checkString($field['frontend'], $newField['frontend'], 'advancedSearchName');
        $this->checkBoolean($field['frontend'], $newField['frontend'], 'hidden');
        $this->checkArray($field['frontend'], $newField['frontend'], 'hidden');
        return $newField;
    }

    private function transformRelation(array $relation): array
    {
        $newRelation = [];
        $this->checkString($relation, $newRelation, 'type');
        $this->checkString($relation, $newRelation, 'model');
        $this->checkString($relation, $newRelation, 'morphName');
        $this->checkString($relation, $newRelation, 'table');
        $this->checkString($relation, $newRelation, 'foreignKey');
        $this->checkString($relation, $newRelation, 'localKey');
        $this->checkBoolean($relation, $newRelation, 'addMigrationFile');
        $this->checkBoolean($relation, $newRelation, 'deleteRelation');
        $this->checkBoolean($relation, $newRelation, 'checkOnDelete');
        $this->checkArray($relation, $newRelation, 'pivot');
        return $newRelation;
    }

    private function checkBoolean(array $field, array &$newField, string $key): void
    {
        if (isset($field[$key]) && $field[$key] === true) {
            $newField[$key] = $field[$key];
        }
    }

    private function checkString(array $field, array &$newField, string $key): void
    {
        if (isset($field[$key]) && !empty($field[$key])) {
            $newField[$key] = $field[$key];
        }
    }

    private function checkArray(array $field, array &$newField, string $key): void
    {
        if (isset($field[$key]) && is_array($field[$key]) && !empty($field[$key])) {
            $newField[$key] = $field[$key];
        }
    }
    
}