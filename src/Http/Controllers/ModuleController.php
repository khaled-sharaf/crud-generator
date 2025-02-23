<?php

namespace Khaled\CrudSystem\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\File;
use Nwidart\Modules\Facades\Module;
use Khaled\CrudSystem\Facades\Crud;
use Illuminate\Support\Str;

class ModuleController extends Controller
{
    
    public function modules()
    {
        $backendModules = collect(Module::all())->map(function ($module) {
            return [
                'label' => Str::title($module->getName()),
                'value' => $module->getName(),
            ];
        })->values()->all();
        $frontendPath = Crud::config('generator.frontend_path');
        $frontendModules = array_map('basename', File::directories(base_path($frontendPath . '/src/modules')));
        $frontendModules = collect($frontendModules)->filter(fn ($module) => $module != 'crud')->map(function ($module) {
            return [
                'label' => Str::title($module),
                'value' => $module,
            ];
        })->values()->all();
        return sendData([
            'backend' => $backendModules,
            'frontend' => $frontendModules,
        ]);
    }



}
