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
        return $config['relations'];
    }
    
    private function transformField(array $field): array
    {
        // $newField = [];
        // if ($field['keyShowInFront']) {
        //     $newField['showInFront'] = $field['showInFront'];
        // }
        // if ($field['notDatabase'] === true) {
        //     $newField['notDatabase'] = $field['notDatabase'];
        // }
        // if ($field['nullable'] === true) {
        //     $newField['nullable'] = $field['nullable'];
        // }
        
        return $field;
    }

    
}