<?php

use Illuminate\Support\Facades\Route;
use Khaled\CrudSystem\Http\Controllers\CrudController;
use Khaled\CrudSystem\Http\Controllers\ModuleController;

Route::middleware('dashboard')->prefix('crud')->group(function () {
    Route::apiResource('cruds', CrudController::class);
    Route::get('modules', [ModuleController::class, 'modules']);
});