<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MarketplaceController;
use App\Http\Controllers\FacilityCostController;

Route::get('/', function () {
    return view('login');
});
Route::get('/welcome', function () {
    return view('welcome');
});
Route::get('/facility', function () {
    return view('Facility_Form');
});

Route::post('/facility/export-csv', [FacilityCostController::class, 'exportCsv'])
    ->name('facility.export.csv');

Route::get('/facility', [FacilityCostController::class, 'showForm'])->name('facility.form');
Route::post('/facility/calculate', [FacilityCostController::class, 'calculate'])->name('facility.calculate');

Route::get('/marketplace', [MarketplaceController::class, 'index'])->name('marketplace.index');
Route::get('/marketplace/create', [MarketplaceController::class, 'create'])->name('marketplace.create');
Route::post('/marketplace', [MarketplaceController::class, 'store'])->name('marketplace.store');

// Inventory
Route::get('/inventory', function () {
    return view('inventory.index');
})->name('inventory.index');

// Search users
Route::get('/users/search', function () {
    return view('users.search');
})->name('users.search');

require __DIR__.'\saml2.php';
