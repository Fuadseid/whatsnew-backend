<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\SignupController;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;

// Route::get('/', function () {
//     return view('welcome');
// });


// Protected Home/Dashboard
Route::post('/', function () {
    return view('welcome');
})->middleware('auth');


/* Route::get('/test-mail', function() {
    try {
        Mail::raw('Test email from Laravel', function($message) {
            $message->to('fuaddbus@gmal.com')->subject('Test Email');
        });
        return "Email sent! Check your inbox.";
    } catch (\Exception $e) {
        return "Error: " . $e->getMessage();
    }
});
 */