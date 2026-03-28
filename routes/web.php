<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EmailController;
use App\Http\Controllers\EquipmentController;

Route::get('/', function () {
    return view('login');
});
Route::get('/welcome', function () {
    return view('welcome');
});

Route::get('/my_profile', function () {
    return view('my_profile');
})->name('my_profile');


Route::get('/kinventory', [EmailController::class, 'showForm'])->name('kinventory');
Route::post('/send-email', [EmailController::class, 'sendEmail']);

Route::get('/search_user', function () {
    return view('search_user');
})->name('search_user');

// Route::get('/inventory_management', function () {
//     return view('inventory_management');
// })->name('inventory_management');//->middleware('role:super,inventory,user')

Route::get('/inventory_management', [EquipmentController::class, 'index'])
    ->name('inventory_management');

Route::post('/inventory_management', [EquipmentController::class, 'store'])
    ->name('inventory.store');

Route::delete('/equipment/{id}', [EquipmentController::class, 'destroy'])
    ->name('equipment.destroy');

Route::put('/equipment/{id}', [EquipmentController::class, 'update'])
    ->name('equipment.update');

Route::get('/kinemercado', function () {
    return view('kinemercado');
})->name('kinemercado');

Route::get('/marketplace_management', function () {
    return view('marketplace_management');
})->name('marketplace_management');

Route::get('/access_logs', function () {
    return view('access_logs');
})->name('access_logs');

Route::get('/facility_management', function () {
    return view('facility_management');
})->name('facility_management');


/*Route::get('/kinemercado/reportar_usuario', function () {
    return view('kinemercado');
})->name('kinemercado.reportar_usuario');*/

Route::get('/kinemercado/mensaje', function () {
    return view('kinemercado_mensaje');
})->name('kinemercado_mensaje');

require __DIR__.'\saml2.php';