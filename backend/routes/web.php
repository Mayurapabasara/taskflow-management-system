<?php

use App\Models\Project;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('projects');
})->name('projects.index');

Route::get('/projects/{project}/tasks', function (Project $project) {
    return view('projects', ['project' => $project]);
})->name('projects.tasks');
