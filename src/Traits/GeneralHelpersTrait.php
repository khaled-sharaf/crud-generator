<?php

namespace W88\CrudSystem\Traits;

use Illuminate\Support\Str;
use W88\CrudSystem\Facades\Field;

trait GeneralHelpersTrait
{
    /* ======================== Checks ======================== */
    protected function checkApiRoute($route, $type = 'dashboardApi'): bool|array
    {
        $checkClientApi = $type === 'clientApi' ? $this->allClientApiIsAllowed() : false;
        return $this->config[$type ?? 'dashboardApi'][$route] ?? $checkClientApi;
    }

    protected function allClientApiIsAllowed(): bool
    {
        $clientApi = $this->config['clientApi'] ?? false;
        return $clientApi === true || (is_array($clientApi) && count($clientApi) === 5 && collect($clientApi)->every(fn ($route) => $route === true));
    }

    protected function hasPermissions(): bool
    {
        return $this->config['options']['permissions'] ?? false;
    }

    protected function hasSoftDeletes(): bool
    {
        return $this->config['options']['softDeletes'] ?? false;
    }

    protected function hasTableSearch(): bool
    {
        return $this->config['options']['tableSettings']['tableSearch'] ?? false;
    }

    protected function hasTableFilter(): bool
    {
        return $this->config['options']['tableSettings']['tableFilter'] ?? false;
    }

    protected function hasTableExport(): bool
    {
        return $this->config['options']['tableSettings']['tableExport'] ?? false;
    }

    /* ======================== Getters ======================== */
    protected function getActivationRouteOption()
    {
        return $this->config['dashboardApi']['activation'] ?? false;
    }
    
    protected function getFields(): array
    {
        return collect(array_merge($this->config['fields'] ?? [], $this->appendActivationField()))->map(function ($field, $name) {
            $field['name'] = $name;
            return $field;
        })->toArray();
    }

    private function appendActivationField(): array
    {
        $fields = [];
        $activationRouteOption = $this->getActivationRouteOption();
        $activationColumn = $activationRouteOption['column'] ?? 'is_active';
        $activationDefault = $activationRouteOption['default'] ?? true;
        if ($activationRouteOption) {
            $fields[$activationColumn] = [
                'type' => 'boolean',
                'label' => 'Active',
                'default' => $activationDefault,
                'validation' => 'nullable|boolean',
            ];
        }
        return $fields;
    }

    protected function getNotHiddenFields(): array
    {
        return collect($this->getFields())->filter(fn ($field) => !Field::isHidden($field))->toArray();
    }

    protected function getBooleanRouteFields(): array
    {
        return collect($this->getFields())->filter(fn ($field) => Field::hasBooleanRouteFilter($field))->toArray();
    }

    protected function getBooleanFilterFields(): array
    {
        return collect($this->getFields())->filter(fn ($field) => Field::hasBooleanFilter($field))->toArray();
    }

    protected function getConstantFilterFields(): array
    {
        return collect($this->getFields())->filter(fn ($field) => Field::hasConstantFilter($field))->toArray();
    }

}