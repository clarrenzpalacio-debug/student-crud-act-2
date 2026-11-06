<?php

use App\Http\Controllers\StudentController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Vue SPA Entry Route (Final Lab Activity 3)
Route::get('/', function () {
    return view('spa');
})->name('vue.app');

Route::get('/spa', function () {
    return view('spa');
});

// Existing Blade-based Student Resource routes (Preserved)
Route::resource('students', StudentController::class);
