<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EmailController;
use Illuminate\Support\Facades\Mail;


Route::get('/', function () {
    return view('login');
});

Route::get('/welcome', function () {
    return view('welcome');
});

Route::get('/my_profile', function () {
    return view('my_profile');
})->name('my_profile');

Route::get('/terms_and_conditions', function () {
    return view('terms_and_conditions');
})->name('terms_and_conditions');


Route::get('/kinventory', [EmailController::class, 'showForm'])->name('kinventory');
Route::post('/send-email', [EmailController::class, 'sendEmail']);

Route::get('/search_user', function () {
    return view('search_user');
})->name('search_user');

Route::get('/inventory_management', function () {
    return view('inventory_management.admin_inventory');
})->name('inventory_management');

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
    return view('/marketplace_management.reports_management');
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

require __DIR__ . '\saml2.php';

// Temporary routes until user tables are connected
Route::get('/test-email/request-approved', [EmailController::class, 'requestApproved']);
Route::get('/test-email/request-denied', [EmailController::class, 'requestDenied']);
Route::get('/test-email/user-banned', [EmailController::class, 'userBanned']);
Route::get('/test-email/unread-messages-reminder', [EmailController::class, 'unreadMessagesReminder']);
