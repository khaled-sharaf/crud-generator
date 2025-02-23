<?php

namespace Khaled\CrudSystem\Services;

use Khaled\CrudSystem\Models\Crud;
use App\Helpers\CrudHelpers\Facades\CrudHelper;

class CrudService
{
    public function tableList()
	{
        $query = Crud::select(['id', 'name', 'module', 'frontend_module', 'generated_at', 'created_at', 'updated_at']);
        return CrudHelper::tableList($query, [
            \App\Filters\Sorting\SortBy::class,
			\App\Filters\Date\Date::class,
			\App\Filters\Date\Time::class,
			\App\Filters\Search\AdvancedSearch::class,
			\App\Filters\Search\TableSearchText::class,
			\App\Filters\Boolean\Trashed::class,
			// new \App\Filters\Boolean\ToggleBoolean('locked'),
        ]);
    }

	public function create($data)
	{
        $crud = Crud::create($data);
        return $crud;
    }

	public function show($id)
	{
		return Crud::findOrFail($id);
	}

	public function update($id, $data)
	{
        $crud = $this->show($id);
        $this->checkIfCrudIsGenerated($crud);
        $crud->update($data);
        return $crud;
    }

	public function updateConfig($id, $config)
	{
        $crud = $this->show($id);
        $this->checkIfCrudIsGenerated($crud);
        $crudConfigTransformService = new CrudConfigTransformService();
        $config =  $crudConfigTransformService->convertConfigToModel($config);
        $crud->update(['current_config' => $config]);
        return $crud;
    }

	public function generate($id, $config)
	{
        $crud = $this->updateConfig($id, $config);
        $this->generateCrud($crud);
        return true;
    }

	public function generateCrud($crud)
	{
        // generate the crud
    }

	public function delete($id)
	{
        $cruds = Crud::whereIn('id', request()->ids)->get();
        foreach ($cruds as $crud) {
            if ($crud->generated_at) throwError('Cannot delete crud that is already generated', 400);
        }
        return CrudHelper::deleteActions($id, new Crud, 'crud');
    }

	public function checkIfCrudIsGenerated($crud)
	{
        if ($crud->generated_at) throwError('Crud is already generated', 400);
    }

}
    