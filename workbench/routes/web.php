<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Workbench\App\Http\Controllers\Controller;

Route::get('/', function () {
    return view('welcome');
});

Route::post('/upload', [Controller::class, 'upload']);
