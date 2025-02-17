<?php

namespace Khaled\CrudSystem\Http\Controllers;

use App\Http\Controllers\Controller;
use Khaled\CrudSystem\Models\Crud;
use App\Helpers\CrudHelpers\Facades\CrudHelper;
use Khaled\CrudSystem\Resources\CrudResource;

class CrudController extends Controller
{
    public function index()
    {
        $query = Crud::query();
        $cruds = CrudHelper::tableList($query, [
            \App\Filters\Sorting\SortBy::class,
			\App\Filters\Date\Date::class,
			\App\Filters\Date\Time::class,
			\App\Filters\Search\AdvancedSearch::class,
			\App\Filters\Search\TableSearchText::class,
			\App\Filters\Boolean\Trashed::class,
			// new \App\Filters\Boolean\ToggleBoolean('locked'),
        ]);
        CrudResource::collection($cruds);
        return sendData($cruds);
    }
}
