<?php

namespace W88\CrudSystem\Generators\Frontend;

use W88\CrudSystem\Generators\FrontendGenerator;
use Touhidurabir\StubGenerator\StubGenerator;
use Illuminate\Support\Str;
use W88\CrudSystem\Facades\Field;

class ListGenerator extends FrontendGenerator
{

    public function generate(): void
    {
        $this->ensureVueStubExists('vue');
        $this->ensureVueStubExists('js');
        $this->ensureDirectoryExists();
        $this->generateFiles();
    }

    protected function getVueStubPath(): string
    {
        return __DIR__ . '/../../stubs/frontend/list/vue.stub';
    }

    protected function getJsStubPath(): string
    {
        return __DIR__ . '/../../stubs/frontend/list/js.stub';
    }

    protected function getGeneratorDirectory(): string
    {
        return $this->getFrontendCrudPath() . "/pages/{$this->getListFileName()}";
    }

    protected function generateFiles(): void
    {
        (new StubGenerator())->from($this->getVueStubPath(), true)
            ->to($this->getGeneratorDirectory())
            ->withReplacers($this->getVueReplacers())
            ->replace(true)
            ->as($this->getListFileName())
            ->ext('vue')
            ->save();

        (new StubGenerator())->from($this->getJsStubPath(), true)
            ->to($this->getGeneratorDirectory())
            ->withReplacers($this->getJsReplacers())
            ->replace(true)
            ->as(Str::camel($this->getListFileName()))
            ->ext('js')
            ->save();
    }

    /* ================================ Vue Replacers ================================ */

    protected function getVueReplacers(): array
    {
        return [
            'CLASS_PAGE' => "{$this->modelNameKebab}-list-page",
            'JS_FILE_NAME' => Str::camel($this->getListFileName()),
            'ACTIONS' => $this->getVueActions(),
            'BODY_CELLS' => $this->getVueBodyCells(),
            'FILTERS' => $this->getVueFilters(),
            'USE_COMPONENTS' => $this->getVueUseComponents(),
        ];
    }

    protected function getVueActions(): string
    {
        if (!($this->checkApiRoute('edit') || $this->checkApiRoute('show'))) return '';
        $viewBtnPermission = $this->hasPermissions() ? "\n\t\t\t\t\tv-if=\"\$can('view-{$this->modelNameKebab}')\"" : '';
        $editBtnPermission = $this->hasPermissions() ? "\n\t\t\t\t\tv-if=\"\$can('edit-{$this->modelNameKebab}')\"" : '';
        $viewAction = $this->hasShowPopup() ? "@click=\"() => \$store.modals.showModal('view{$this->modelName}', {id: props.row.id})\"" : ":to=\"{name: '{$this->getShowRouteName()}', params: {id: props.row.id} }\"";
        $editAction = $this->hasFormPopup() ? "@click=\"() => \$store.modals.showModal('edit{$this->modelName}', {id: props.row.id})\"" : ":to=\"{name: '{$this->getEditRouteName()}', params: {id: props.row.id} }\"";
        $viewBtn = $this->checkApiRoute('show') ? "\n\t\t\t\t<BtnViewTable{$viewBtnPermission}\n\t\t\t\t\t{$viewAction}\n\t\t\t\t/>" : '';
        $editBtn = $this->checkApiRoute('edit') ? "\n\t\t\t\t<BtnEdit{$editBtnPermission}\n\t\t\t\t\t{$editAction}\n\t\t\t\t/>" : '';
        return"<!-- =========================== Actions =========================== -->
            <template v-slot:table-actions=\"props\">{$viewBtn}{$editBtn}\n\t\t\t</template>";
    }

    protected function getVueBodyCells(): string
    {
        $cells = [];
        $booleanFields = $this->getBooleanFields();
        $activationRouteOption = $this->getActivationRouteOption();
        if ($activationRouteOption) {
            $name = $activationRouteOption['column'] ?? 'is_active';
            $booleanFields[$name] = [
                'name' => $name,
                'type' => 'boolean',
                'route' => 'activation',
                'filter' => true,
            ];
        }
        foreach ($booleanFields as $field) {
            $cells[] = $this->handleBodyCellBooleanField($field);
        }
        foreach ($this->getConstantFields() as $field) {
            $cells[] = $this->handleBodyCellConstantField($field);
        }
        return count($cells) ? collect($cells)->implode("\n") . "\n" : '';
    }

    protected function getVueFilters(): string
    {
        $filters = [];
        $booleanFields = $this->getBooleanFilterFields();
        $activationRouteOption = $this->getActivationRouteOption();
        if ($activationRouteOption) {
            $name = $activationRouteOption['column'] ?? 'is_active';
            $booleanFields[$name] = [
                'name' => $name,
                'nullable' => true,
                'customLabel' => 'activation',
                'trueTitle' => 'activated',
                'falseTitle' => 'deactivated',
            ];
        }
        foreach ($booleanFields as $field) {
            $filters[] = $this->handleFilterBooleanField($field);
        }
        foreach ($this->getConstantFilterFields() as $field) {
            $filters[] = $this->handleFilterConstantField($field);
        }
        return count($filters) ? collect($filters)->filter(fn ($filter) => !empty($filter))->implode("\n") . "\n" : '';
    }

    protected function handleBodyCellBooleanField(array $field): string
    {
        $hasFilter = Field::isFilterable($field) ? ', true' : '';
        $route = Field::hasBooleanRouteFilter($field) ? "\n\t\t\t\t\t\t@update:model-value=\"\$store.tableList.toggleBooleanInTables('{$this->modelNameCamelPlural}', props.row, '{$field['name']}', `{$this->getApiRouteName()}/\${props.row.id}/{$field['route']}`{$hasFilter})\"" : '';
        $disable = $this->hasPermissions() ? "\n\t\t\t\t\t\t:disable=\"!\$can('{$field['route']}-{$this->modelNameKebab}')\"" : '';
        return "\n\t\t\t<!-- =========================== Body {$field['name']} =========================== -->
            <template v-slot:body-cell-{$field['name']}=\"props\">
                <q-td :props=\"props\">
                    <q-toggle
                        :model-value=\"props.row.{$field['name']}\"
                        color=\"green-7\"
                        size=\"32px\"{$route}{$disable}
                    />
                </q-td>
            </template>";
    }

    protected function handleBodyCellConstantField(array $field): string
    {
        $lookupName = $this->getLookupName($field['name']);
        $hasLookupFrontend = Field::hasLookupFrontend($field);
        $singleValue = $hasLookupFrontend ? "{$lookupName}.getByValue(props.row.{$field['name']})" : "props.row.{$field['name']}";
        $multiValue = $hasLookupFrontend ? "{$lookupName}.getByValue(item)" : 'item';
        $addLabel = Field::isSingleConstant($field) ? ":label=\"{$singleValue}\"" :
        "v-for=\"(item, index) in props.row.{$field['name']}\"\n\t\t\t\t\t\t:key=\"index\"\n\t\t\t\t\t\t:label=\"{$multiValue}\"";
        return "\n\t\t\t<!-- =========================== Body {$field['name']} =========================== -->
            <template v-slot:body-cell-{$field['name']}=\"props\">
                <q-td :props=\"props\">
                    <q-badge
                        {$addLabel}
                        class=\"px-2 py-1 bg-primary\"
                        rounded
                    />
                </q-td>
            </template>";
    }

    protected function handleFilterBooleanField(array $field): string
    {
        $name = $field['name'];
        $label = $field['customLabel'] ?? $this->getLangPath("table.{$name}");
        $trueTitle = isset($field['trueTitle']) ? "\n\t\t\t\t\t:trueTitle=\"\$t('{$field['trueTitle']}')\"" : '';
        $falseTitle = isset($field['falseTitle']) ? "\n\t\t\t\t\t:falseTitle=\"\$t('{$field['falseTitle']}')\"" : '';
        $nullable = Field::isNullable($field) ? "\n\t\t\t\t\tnullable" : '';
        return "\n\t\t\t\t<!-- ================= Filter By {$name} ================= -->
                <FilterToggleBoolean
                    :filters=\"filters.options\"
                    filterName=\"{$name}\"
                    :label=\"\$t('{$label}')\"{$trueTitle}{$falseTitle}{$nullable}
                />";
    }

    protected function handleFilterConstantField(array $field): string
    {
        if (!Field::hasLookupFrontend($field) && !Field::hasLookup($field)) return '';
        return Field::getFilter($field) === 'single' ? $this->handleFilterSingleConstantField($field) : $this->handleFilterMultiConstantField($field);
    }

    protected function handleFilterSingleConstantField(array $field): string
    {
        $name = $field['name'];
        $lookupName = $this->getLookupName($name);
        $lookupName = Field::hasLookupFrontend($field) ? $lookupName : Str::camel($lookupName);
        $label = $this->getLangPath("table.{$name}");
        return "\n\t\t\t\t<!-- ================= Filter By {$name} ================= -->
                <q-select
                    v-model=\"filters.options.{$name}\"
                    :label=\"\$t('{$label}')\"
                    :options=\"{$lookupName}\"
                    outlined
                    dense
                    emit-value
                    map-options
                    clearable
                />";
    }

    protected function handleFilterMultiConstantField(array $field): string
    {
        $name = $field['name'];
        $lookupName = $this->getLookupName($name);
        $lookupName = Field::hasLookupFrontend($field) ? $lookupName : Str::camel($lookupName);
        $label = $this->getLangPath("table.{$name}");
        return "\n\t\t\t\t<!-- ================= Filter By {$name} ================= -->
                <div>
                    <div class=\"filter-section-label\" v-text=\"\$t('{$label}')\"></div>
                    <q-option-group
                        v-model=\"filters.options.{$name}\"
                        :options=\"{$lookupName}\"
                        dense
                        inline
                        type=\"checkbox\"
                        class=\"px-2\"
                    />
                </div>";
    }

    protected function getModalComponents(): array
    {
        $components = [];
        if ($this->checkApiRoute('create') && $this->hasFormPopup()) $components[] = $this->getCreateFileName();
        if ($this->checkApiRoute('edit') && $this->hasFormPopup()) $components[] = $this->getEditFileName();
        if ($this->checkApiRoute('show') && $this->hasShowPopup()) $components[] = $this->getShowFileName();
        return $components;
    }

    protected function getVueUseComponents(): string
    {
        $components = $this->getModalComponents();
        return count($components) ? collect($components)->map(fn ($component) => "\n\t\t<{$component} />")->implode('') . "\n" : '';
    }

    /* ================================ Js Replacers ================================ */

    protected function getJsReplacers(): array
    {
        return [
            'IMPORT_COMPONENTS' => $this->getJsImportComponents(),
            'DECLARED_COMPONENTS' => $this->getJsDeclaredComponents(),
            'TABLE_ID' => $this->getTableId(),
            'URL' => $this->getApiRouteName(),
            'TABLE_OPTIONS' => $this->getJsTableOptions(),
            'VARS_OF_FILTERS' => $this->getJsVarsOfFilters(),
            'COLUMNS' => $this->getJsColumns(),
            'DECLARED_LOOKUPS' => $this->getJsDeclaredLookups(),
            'GET_LOOKUPS' => $this->getJsGetLookups(),
        ];
    }

    protected function getJsDeclaredLookups(): string
    {
        return collect($this->getFieldsHasBackendLookupOnly())->map(function ($field) {
            $lookupName = Str::camel($this->getLookupName($field['name']));
            return "\n\t\t\t{$lookupName}: []";
        })->implode(",\n");
    }

    protected function getJsGetLookups(): string
    {
        return collect($this->getFieldsHasBackendLookupOnly())->map(function ($field) {
            $lookupName = Str::camel($this->getLookupName($field['name']));
            return "\n\t\tthis.{$lookupName} = await this.\$getLookup('{$this->getLookupApiRouteName($field['name'])}')";
        })->implode(",\n");
    }

    protected function getJsImportComponents(): string
    {
        $components = $this->getModalComponents();
        return count($components) ? collect($components)->map(fn ($component) => $this->handleImportComponentLine($component))->implode("\n") . "\n\n" : '';
    }

    protected function handleImportComponentLine(string $component): string
    {
        return "import {$component} from '../../components/{$component}/{$component}.vue'";
    }

    protected function getJsDeclaredComponents(): string
    {
        $components = $this->getModalComponents();
        return count($components) ? "\n\tcomponents: {" . collect($components)->map(fn ($component) => "\n\t\t{$component}")->implode(',') . "\n\t}," : '';
    }

    protected function getJsTableOptions(): string
    {
        $options = [];
        $hasPermissions = $this->hasPermissions();
        $hasSoftDeletes = $this->hasSoftDeletes();

        $options[] = !$this->checkApiRoute('delete') ? 'hasDelete: false,' : '';
        $options[] = !$this->hasMultiSelection() ? 'multiDelete: false,' : '';
        $options[] = !$this->hasTableSearch() ? 'noSearch: true,' : '';
        $options[] = !$this->hasTableFilter() ? 'noFilter: true,' : 'advancedSearch: true,';
        $options[] = !$this->hasTableExport() ? 'noExport: true,' : '';

        if ($hasSoftDeletes) {
            $options[] = 'softDelete: true,';
            $options[] = "titleOfTrashList: this.\$t('{$this->frontendModuleName}.{$this->modelNameSnake}_crud.trash_label'),";
            $options[] = $hasPermissions ? "showIfTrashedList: this.\$can('view-trashed-{$this->modelNameKebab}-list')," : '';
        }
        if ($hasPermissions) {
            $deletePermission = "\n\t\t\t\t\tdelete: this.\$can('delete-{$this->modelNameKebab}'),";
            $forceDeletePermission = $hasSoftDeletes ? "\n\t\t\t\t\tforceDelete: this.\$can('force-delete-{$this->modelNameKebab}')," : '';
            $restorePermission = $hasSoftDeletes ? "\n\t\t\t\t\trestore: this.\$can('restore-{$this->modelNameKebab}')," : '';
            $options[] = "showIfExport: this.\$can('export-list-{$this->modelNameKebab}'),";
            $options[] = "showIfDeleteActions: {{$deletePermission}{$forceDeletePermission}{$restorePermission}\n\t\t\t\t},";
        }
        if ($this->checkApiRoute('create')) {
            $createPermission = $hasPermissions ? "\n\t\t\t\t\tshowIf: () => this.\$can('create-{$this->modelNameKebab}')," : '';
            $createAction = $this->hasFormPopup() ? "click: () => this.\$store.modals.showModal('create{$this->modelName}')" : "to: {name: '{$this->getCreateRouteName()}'}";
            $options[] = "btnCreate: {{$createPermission}
                    {$createAction},
                    label: '{$this->getLangPath("create_{$this->modelNameSnake}")}',
                },";
        }
        $options = collect($options)->filter(fn ($option) => !empty($option))->implode("\n\t\t\t\t");
        return $options ? "\n\t\t\t\t" . $options . "\n" : '';
    }

    protected function getJsVarsOfFilters(): string
    {
        $filters = [];
        $activationRouteOption = $this->getActivationRouteOption();
        $activationColumn = $activationRouteOption['column'] ?? 'is_active';
        $activationValue = isset($activationRouteOption['default']) ? json_encode($activationRouteOption['default']) : 'true';
        $filters[] = $activationRouteOption ? "{$activationColumn}: {$activationValue}," : '';
        foreach ($this->getBooleanFilterFields() as $field) {
            $default = Field::hasDefault($field) ? json_encode($field['default']) : 'null';
            $filters[] = "{$field['name']}: {$default},";
        }
        foreach ($this->getConstantFilterFields() as $field) {
            $filterType = $field['filter'] ?? 'single';
            $default = Field::hasDefault($field) ? json_encode($field['default']) : null;
            $default = $filterType === 'single' ? ($default ?? 'null') : ($default ? "[{$default}]" : '[]');
            $filters[] = "{$field['name']}: {$default},";
        }
        $filters[] = '// add your filters here...';
        return "\n\t\t\t\t\t\t\t" . collect($filters)->filter(fn ($filter) => !empty($filter))->implode("\n\t\t\t\t\t\t\t");
    }

    protected function getJsColumns(): string
    {
        $columns = [$this->getIdColumn()];
        foreach ($this->getFieldsVisibleInList() as $field) {
            $columns[] = $this->handleFormatColumn($field);
        }
        $columns = array_merge($columns, [$this->getCreatedAtColumn(), $this->getUpdatedAtColumn(), $this->getActionsColumn()]);
        return collect($columns)->filter(fn ($column) => !empty($column))->implode("\n\t\t\t\t\t");
    }

    protected function getIdColumn(): string
    {
        $searchable = $this->hasTableSearch() ? "\n\t\t\t\t\t\tsearchable: true," : '';
        $advancedSearchable = $this->hasTableFilter() ? "\n\t\t\t\t\t\tadvancedSearchable: true," : '';
        return"\n\t\t\t\t\t{
                        name: 'id',
                        label: this.\$filters.title(this.\$t('id')),
                        field: 'id',
                        sortable: true,{$searchable}{$advancedSearchable}
                        required: true,
                        align: 'left',
                    },";
    }

    protected function getCreatedAtColumn(): string
    {
        return"{
                        name: 'created_at',
                        label: this.\$filters.title(this.\$t('created_at')),
                        field: 'created_at',
                        sortable: true,
                        hidden: true,
                        align: 'left',
                    },";
    }

    protected function getUpdatedAtColumn(): string
    {
        return"{
                        name: 'updated_at',
                        label: this.\$filters.title(this.\$t('updated_at')),
                        field: 'updated_at',
                        sortable: true,
                        hidden: true,
                        align: 'left',
                    },";
    }

    protected function getActionsColumn(): string
    {
        if (!($this->checkApiRoute('edit') || $this->checkApiRoute('show') || $this->checkApiRoute('delete'))) return '';
        return"{
                        name: 'actions',
                        label: this.\$filters.title(this.\$t('actions')),
                        align: 'center',
                        exporting: false
                    },";
    }

    protected function handleFormatColumn(array $field): string
    {
        $name = $field['name'];
        $label = $this->getLangPath("table.{$name}");
        if ($name === 'is_active') {
            $field['visibleList'] = true;
            $field['sortable'] = true;
            $label = 'activation';
        }
        $columnProperties = [
            !Field::isVisibleList($field) ? "hidden: true," : '',
            Field::isSortable($field) ? "sortable: true," : '',
            Field::isExportable($field) ? "exportable: true," : '',
            Field::isSearchable($field) ? "searchable: true," : '',
            Field::isSearchable($field) && isset($field['searchableName']) ? "searchableName: '{$field['searchableName']}'," : '',
            Field::isAdvancedSearchable($field) ? "advancedSearchable: true," : '',
            Field::isAdvancedSearchable($field) && isset($field['advancedSearchName']) ? "advancedSearchName: '{$field['advancedSearchName']}'," : '',
            "align: 'left',",
            "// required: true"
        ];
        $columnProperties = collect($columnProperties)->filter(fn ($property) => !empty($property))->implode("\n\t\t\t\t\t\t");
        return "{
                        name: '{$name}',
                        label: this.\$filters.title(this.\$t('{$label}')),
                        field: row => row.{$name},
                        {$columnProperties}
                    },";
    }

}
