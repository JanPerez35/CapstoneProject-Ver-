<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EmailController;


Route::get('/', function () {
    return view('login');
});
Route::get('/welcome', function () {
    return view('welcome');
});


Route::get('/kinventory', [EmailController::class, 'showForm']);
Route::post('/send-email', [EmailController::class, 'sendEmail']);


require __DIR__.'\saml2.php';
