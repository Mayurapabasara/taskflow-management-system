<?php

use App\Models\Project;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('projects');
})->name('projects.index');

Route::get('/backups', function () {
    return view('projects', ['backupManager' => true]);
})->name('backups.index');

Route::get('/projects/{project}/tasks', function (Project $project) {
    return view('projects', ['project' => $project]);
})->name('projects.tasks');
