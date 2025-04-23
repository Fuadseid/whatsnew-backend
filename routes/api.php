<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\VideoController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register',[AuthController::class,'register']);
Route::post('/forget',[AuthController::class,'forget']);
Route::post('/logout',[AuthController::class,'logout']);
Route::post('/reset-password',[AuthController::class,'reset']);
Route::post('/post-video', [VideoController::class, 'store']);
Route::get('/showvideo',[VideoController::class,'show']);
// routes/api.php
/* Route::post('/forgot-password', [PasswordResetController::class, 'forgotPassword']);
Route::post('/reset-password', [PasswordResetController::class, 'resetPassword']); */