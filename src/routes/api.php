<?php

use Illuminate\Support\Facades\Route;
use Khaled\CrudSystem\Http\Controllers\CrudController;
use Khaled\CrudSystem\Http\Controllers\ModuleController;

Route::middleware('dashboard')->prefix('crud')->group(function () {
    Route::put('cruds/{id}/config', [CrudController::class, 'updateConfig']);
    Route::post('cruds/{id}/generate', [CrudController::class, 'generate']);
    Route::apiResource('cruds', CrudController::class);
    Route::get('modules', [ModuleController::class, 'modules']);
    Route::get('models', [ModuleController::class, 'models']);
});