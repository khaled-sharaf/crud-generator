<?php

namespace Khaled\CrudSystem\Http\Controllers;

use App\Http\Controllers\Controller;
use Khaled\CrudSystem\Services\CrudService;

class ModuleController extends Controller
{
    
    public function __construct(
        private CrudService $crudService
    ) {}

    public function modules()
    {
        return sendData($this->crudService->getModules());
    }

    /**
     * Get all model names from the application and modules
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function models()
    {
        return sendData($this->crudService->getModels());
    }


}
