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
        ];
    }

    protected function getVueActions(): string
    {
        if (!($this->checkApiRoute('edit') || $this->checkApiRoute('show'))) return '';
        $viewBtnPermission = $this->hasPermissions() ? "\n\t\t\t\t\tv-if=\"\$can('view-{$this->modelNameKebab}')\"" : '';
        $editBtnPermission = $this->hasPermissions() ? "\n\t\t\t\t\tv-if=\"\$can('edit-{$this->modelNameKebab}')\"" : '';
        $viewBtn = $this->checkApiRoute('show') ? "\n\t\t\t\t<BtnViewTable{$viewBtnPermission}
                    :to=\"{name: '{$this->getShowRouteName()}', params: {id: props.row.id} }\"
                />" : '';
        $editBtn = $this->checkApiRoute('edit') ? "\n\t\t\t\t<BtnEdit{$editBtnPermission}
                    :to=\"{name: '{$this->getEditRouteName()}', params: {id: props.row.id} }\"
                />" : '';
        return"<!-- =========================== Actions =========================== -->
            <template v-slot:table-actions=\"props\">{$viewBtn}{$editBtn}\n\t\t\t</template>";
    }

    protected function getVueBodyCells(): string
    {
        // <!-- =========================== Body nameActive =========================== -->
        // <template v-slot:body-cell-nameActive="props">
        //     <q-td :props="props">
        //         <div>
        //             <q-toggle
        //                 :model-value="props.row.nameActive"
        //                 @update:model-value="$store.tableList.toggleBooleanInTables('posts', props.row, 'nameActive', `url/${props.row.id}/activation`, true)"
        //                 color="green-7"
        //                 size="32px"
        //                 :disable="!$can('activation-post')"
        //             />
        //         </div>
        //     </q-td>
        // </template>
        return '';
    }

    protected function getVueFilters(): string
    {
        // FILTERS
        // <FilterToggleBoolean
        //     filterName="nameActive"
        //     :label="$t('activation')"
        //     nullable
        //     :trueTitle="$t('activated')"
        //     :falseTitle="$t('deactivated')"
        //     :filters="filters.options"
        // />
        // <div>
        //     <div class="filter-section-label" v-text="$t('employees.employee_crud.table.user_role')"></div>
        //     <q-option-group
        //         v-model="filters.options.user_role"
        //         :options="userRoleSelectOptions"
        //         inline
        //         type="checkbox"
        //         dense
        //         class="px-2"
        //     />
        // </div>
        return '';
    }
    
    /* ================================ Js Replacers ================================ */

    protected function getJsReplacers(): array
    {
        return [
            'TABLE_ID' => $this->modelNameKebabPlural,
            'URL' => $this->getApiRouteName(),
            'TABLE_OPTIONS' => $this->getJsTableOptions(),
            'VARS_OF_FILTERS' => $this->getJsVarsOfFilters(),
            'COLUMNS' => $this->getJsColumns(),
        ];
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
            $options[] = "btnCreate: {{$createPermission}
                    to: {name: '{$this->getCreateRouteName()}'},
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
            $default = isset($field['default']) ? json_encode($field['default']) : 'null';
            $filters[] = "{$field['name']}: {$default},";
        }
        foreach ($this->getConstantFilterFields() as $field) {
            $filterType = $field['filter'] ?? 'single';
            $default = isset($field['default']) ? json_encode($field['default']) : null;
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
