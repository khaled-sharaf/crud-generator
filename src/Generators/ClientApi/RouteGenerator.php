<?php

namespace Khaled\CrudSystem\Generators\ClientApi;

use Khaled\CrudSystem\Generators\Backend\RouteGenerator as BackendRouteGenerator;

class RouteGenerator extends BackendRouteGenerator
{

    protected $routeApiType = 'client';
    
    public function checkBeforeGenerate(): bool
    {
        return $this->hasClientApi();
    }

}
