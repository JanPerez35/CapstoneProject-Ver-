<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EmailController;
use App\Http\Controllers\ChatController;

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

Route::get('/inventory_management', function () {
    return view('inventory_management');
})->name('inventory_management')->middleware('role:super,inventory');

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

Route::middleware('auth')->group(function () {
    Route::get('/conversations', [ChatController::class, 'getConversations']);
    Route::get('/chat/{receiverId}', [ChatController::class, 'fetchMessages']);
    Route::post('/send-message', [ChatController::class, 'sendMessage']);
    Route::get('/chat-ui', function () {
        return view('chat');
    });
});
require __DIR__.'\saml2.php';