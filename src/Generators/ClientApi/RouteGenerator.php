<?php

namespace W88\CrudSystem\Generators\ClientApi;

use W88\CrudSystem\Generators\Backend\RouteGenerator as BackendRouteGenerator;

class RouteGenerator extends BackendRouteGenerator
{

    protected $routeApiType = 'client';
    
    public function checkBeforeGenerate(): bool
    {
        return $this->hasClientApi();
    }

}
