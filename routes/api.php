<?php
use App\Http\Controllers\AuthController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register'])->name('register');
Route::post('/login', [AuthController::class, 'login'])->name('login');
Route::get('/activate/{token}', [AuthController::class, 'activate'])->name('activate');

Route::middleware('auth:api')->group(function () {
    Route::get('/user', [UserController::class, 'getUser'])->name('user.get');
    Route::put('/user/update', [UserController::class, 'update'])->name('user.update');
    Route::post('/user/upload', [UserController::class, 'upload'])->name('user.upload');
    Route::post('/user/password', [UserController::class, 'changePassword'])->name('user.password');

    Route::post('/upload', [EmployeeController::class, 'upload']);
    Route::get('/employees', [EmployeeController::class, 'getEmployees']);
});
