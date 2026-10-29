<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('projects');
})->name('projects.index');
