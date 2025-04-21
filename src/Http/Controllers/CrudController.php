<?php

namespace Khaled\CrudSystem\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Khaled\CrudSystem\Http\Requests\CrudBasicInfoRequest;
use Khaled\CrudSystem\Resources\CrudResource;
use Khaled\CrudSystem\Services\CrudService;

class CrudController extends Controller
{
    
    public function __construct(private CrudService $crudService)
    {
    }

    public function index()
    {
        $cruds = $this->crudService->tableList();
        CrudResource::collection($cruds);
        return sendData($cruds);
    }

    public function store(CrudBasicInfoRequest  $request)
	{
        $crud = $this->crudService->create($request->validated());
        return sendData(new CrudResource($crud), __('view.messages.created_success'));
    }

    public function updateConfig($id, Request $request)
	{
        $crud = $this->crudService->updateConfig($id, $request->all());
        return sendData(new CrudResource($crud), __('view.messages.updated_success'));
    }

    public function generate($id)
	{
        $this->crudService->generate($id);
        return sendData(__('view.messages.generated_success'));
        // return sendData($this->crudService->generate($id), __('view.messages.generated_success'));
    }

	public function show($id)
	{
        return sendData(new CrudResource($this->crudService->show($id)));
    }

	public function update($id, CrudBasicInfoRequest $request)
	{
        $crud = $this->crudService->update($id, $request->validated());
        return sendData(new CrudResource($crud), __('view.messages.updated_success'));
    }

	public function destroy($id)
	{
        return sendData($this->crudService->delete($id));
    }

    public function modules()
    {
        return sendData($this->crudService->modules());
    }
    
}
