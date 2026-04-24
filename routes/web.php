<?php

use App\Http\Controllers\EmployeeProjectController;
use Illuminate\Support\Facades\Route;

Route::get('/', [EmployeeProjectController::class, 'show'])->name('employees.show');
Route::post('/', [EmployeeProjectController::class, 'process'])->name('employees.process');
