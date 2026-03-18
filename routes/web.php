<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('login');
});
Route::get('/welcome', function () {
    return view('welcome');
});

// Asi se le otorgamos accesos a los views filtrando por roles
Route::get('/permisos', function () {
    return view('permisos_ejemplo');
})->Middleware('role:super_admin,Inventory_admin');


require __DIR__.'\saml2.php';
