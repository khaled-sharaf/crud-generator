<?php

use Illuminate\Support\Facades\Route;
use Khaled\CrudSystem\Http\Controllers\CrudController;

Route::middleware('dashboard')->prefix('crud')->group(function () {
    Route::apiResource('cruds', CrudController::class);
});