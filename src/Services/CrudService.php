<?php

namespace Khaled\CrudSystem\Services;

use Khaled\CrudSystem\Models\Crud;
use App\Helpers\CrudHelpers\Facades\CrudHelper;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Nwidart\Modules\Facades\Module;

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

    /**
     * Get backend and frontend modules
     * 
     * @return array
     */
    public function getModules(): array
    {
        $backendModules = collect(Module::all())->map(function ($module) {
            return [
                'label' => Str::title($module->getName()),
                'value' => $module->getName(),
            ];
        })->values()->all();

        $frontendPath = \Khaled\CrudSystem\Facades\Crud::config('generator.frontend_path');
        $frontendModules = array_map('basename', File::directories(base_path($frontendPath . '/src/modules')));
        $frontendModules = collect($frontendModules)
            ->filter(fn ($module) => $module != 'crud')
            ->map(function ($module) {
                return [
                    'label' => Str::title($module),
                    'value' => $module,
                ];
            })->values()->all();

        return [
            'backend' => $backendModules,
            'frontend' => $frontendModules,
        ];
    }

    /**
     * Get all models from the application and modules
     * 
     * @return array
     */
    public function getModels()
    {
        $models = [];
        
        // Get models from App directory
        $appModels = $this->getModelsFromDirectory(app_path('/Models'));
        $models = array_merge($models, $appModels);
        
        // Get models from Modules
        $modules = Module::all();
        foreach ($modules as $module) {
            $modulePath = $module->getPath() . '/app/Models';
            if (File::isDirectory($modulePath)) {
                $moduleModels = $this->getModelsFromDirectory($modulePath, "Modules\\{$module->getName()}\\app\\Models");
                $models = array_merge($models, $moduleModels);
            }
        }
        
        // Format the models array
        $formattedModels = collect($models)->map(function ($model) {
            return [
                'label' => $model,
                'value' => $model,
            ];
        })->sortBy('label')->values()->all();
        return $formattedModels;
    }

    /**
     * Get all model classes from a directory
     * 
     * @param string $directory The directory to scan
     * @param string $namespace The namespace prefix for the models
     * @return array
     */
    public function getModelsFromDirectory($directory, $namespace = 'App')
    {
        $models = [];
        
        if (!File::isDirectory($directory)) {
            return $models;
        }
        
        $files = File::allFiles($directory);
        
        foreach ($files as $file) {
            if ($file->getExtension() === 'php') {
                $relativePath = str_replace([$directory, '.php'], ['', ''], $file->getPathname());
                $relativePath = trim(str_replace('/', '\\', $relativePath), '\\');
                
                if ($relativePath) {
                    $class = $namespace . '\\' . $relativePath;
                } else {
                    $class = $namespace . '\\' . $file->getFilenameWithoutExtension();
                }
                
                // Check if the class exists and is a model
                if (class_exists($class)) {
                    $reflection = new \ReflectionClass($class);
                    if ($reflection->isSubclassOf('Illuminate\Database\Eloquent\Model') && !$reflection->isAbstract()) {
                        $models[] = $class;
                    }
                }
            }
        }
        
        return $models;
    }

}
    