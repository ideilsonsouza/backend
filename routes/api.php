<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

Route::group(['middleware' => 'api','prefix' => 'auth'], function ($router) {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/logout', [AuthController::class, 'logout'])->middleware('jwt.auth:user')->name('logout');
    Route::post('/refresh', [AuthController::class, 'refresh'])->middleware('jwt.auth:user')->name('refresh');
    Route::post('/me', [AuthController::class, 'me'])->middleware('jwt.auth:user')->name('me');
});
