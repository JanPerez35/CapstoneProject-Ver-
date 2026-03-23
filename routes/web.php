<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EmailController;


Route::get('/', function () {
    return view('login');
});
Route::get('/welcome', function () {
    return view('welcome');
})->middleware(['role:super_admin']);

// Asi se le otorgamos accesos a los views filtrando por roles
Route::get('/permisos', function () {
    return view('permisos_ejemplo');
})->middleware('role:super_admin,Inventory_admin');

<<<<<<< HEAD
// Ver lista de usuarios
Route::get('/users', [UserController::class, 'index'])
    ->middleware(['role:super_admin'])
    ->name('users.index');
=======
Route::get('/kinventory', [EmailController::class, 'showForm'])->name('kinventory');
Route::post('/send-email', [EmailController::class, 'sendEmail']);

Route::get('/search_user', function () {
    return view('search_user');
})->name('search_user');

Route::get('/inventory_management', function () {
    return view('inventory_management');
})->name('inventory_management');

>>>>>>> origin/main

// Cambiar rol de un usuario
Route::post('/users/{user}/role', [UserController::class, 'updateRole'])
    ->middleware(['role:super_admin'])
    ->name('users.updateRole');

require __DIR__.'\saml2.php';
