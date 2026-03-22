<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EmailController;


Route::get('/', function () {
    return view('login');
});
Route::get('/welcome', function () {
    return view('welcome');
});


Route::get('/kinventory', [EmailController::class, 'showForm'])->name('kinventory');
Route::post('/send-email', [EmailController::class, 'sendEmail']);

Route::get('/search_user', function () {
    return view('search_user');
})->name('search_user');

Route::get('/inventory_management', function () {
    return view('inventory_management');
})->name('inventory_management');



require __DIR__.'\saml2.php';
