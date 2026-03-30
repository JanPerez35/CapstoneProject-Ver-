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

//Route::get('/kinventory', [EmailController::class, 'showForm'])->name('kinventory');

Route::get('/kinventory', [EquipmentController::class, 'kinventory'])
    ->name('kinventory');

Route::post('/kinventory/borrow', [EquipmentController::class, 'borrow'])
    ->name('kinventory.borrow');

Route::post('/send-email', [EmailController::class, 'sendEmail']);

Route::post('/cart/add', [EquipmentController::class, 'addToCart'])->name('cart.add');

Route::get('/cart', [EquipmentController::class, 'cart'])->name('cart.index');
Route::delete('/cart/remove/{id}', [EquipmentController::class, 'removeFromCart'])->name('cart.remove');
Route::post('/cart/checkout', [EquipmentController::class, 'checkoutCart'])->name('cart.checkout');

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

Route::get('/inventory_management/borrows', function () {
    return view('inventory_management.borrows');
})->name('inventory_management.borrows');

Route::get('/inventory_management/inventory_statistics', function () {
    return view('inventory_management.inventory_statistics');
})->name('inventory_management.inventory_statistics');

Route::get('/kinemarket', function () {
    return view('kinemarket');
})->name('kinemarket');

Route::get('/marketplace_management', function () {
    return view('marketplace_management');
})->name('marketplace_management');

Route::get('/access_logs', function () {
    return view('access_logs');
})->name('access_logs');

Route::get('/facility_management', function () {
    return view('facility_management');
})->name('facility_management');

Route::get('/my_messages', function () {
    return view('my_messages');
})->name('my_messages');

/*Route::get('/kinemercado/reportar_usuario', function () {
    return view('kinemercado');
})->name('kinemercado.reportar_usuario');*/

Route::get('/kinemercado/mensaje', function () {
    return view('kinemercado_mensaje');
})->name('kinemercado_mensaje');

require __DIR__.'\saml2.php';
