<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EmailController;
use App\Http\Controllers\EquipmentController;
use App\Http\Controllers\FacilityCostController;
use Illuminate\Support\Facades\Mail;
use App\Http\Controllers\PostController;
use App\Models\Post;
use App\Http\Controllers\TermsController;
use App\Http\Controllers\ChatController;
use App\Models\Chat;
use App\Http\Controllers\UserReportController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ReviewController;

Route::get('/', function () {
    return view('login');
});

Route::get('/welcome', function () {
    return view('welcome');
});

// Route::get('/my_profile', function () {
//     return view('my_profile');
// })->name('my_profile');

//Route::get('/kinventory', [EmailController::class, 'showForm'])->name('kinventory');

Route::get('/kinventory', [EquipmentController::class, 'kinventory'])
    ->name('kinventory');

Route::post('/kinventory/borrow', [EquipmentController::class, 'borrow'])
    ->name('kinventory.borrow');

Route::get('/terms_and_conditions', function () {
    return view('terms_and_conditions');
})->name('terms_and_conditions');


// Route::get('/kinventory', [EmailController::class, 'showForm'])->name('kinventory');
Route::post('/send-email', [EmailController::class, 'sendEmail']);

Route::post('/cart/add', [EquipmentController::class, 'addToCart'])->name('cart.add');

Route::get('/cart', [EquipmentController::class, 'cart'])->name('cart.index');
Route::delete('/cart/remove/{id}', [EquipmentController::class, 'removeFromCart'])->name('cart.remove');
Route::post('/cart/checkout', [EquipmentController::class, 'checkoutCart'])->name('cart.checkout');

Route::get('/search_user', [UserController::class, 'index'])->name('search_user');
Route::put('/users/{user}/role', [UserController::class, 'updateRole']);

// Route::get('/inventory_management', function () {
//     return view('inventory_management');
// })->name('inventory_management');//->middleware('role:super,inventory,user')

Route::get('/inventory_management', [EquipmentController::class, 'index'])
    ->name('inventory_management')->middleware('role:admin super,admin inventory,user');

Route::post('/inventory_management', [EquipmentController::class, 'store'])
    ->name('inventory.store');

Route::delete('/equipment/{id}', [EquipmentController::class, 'destroy'])
    ->name('equipment.destroy');

Route::put('/equipment/{id}', [EquipmentController::class, 'update'])
    ->name('equipment.update');

Route::get('/inventory_management/borrows', [EquipmentController::class, 'borrows'])
    ->name('inventory_management.borrows');

Route::post('/inventory_management/requests/{id}/approve', [EquipmentController::class, 'approveRequest'])
    ->name('inventory_management.requests.approve');

Route::post('/inventory_management/requests/{id}/reject', [EquipmentController::class, 'rejectRequest'])
    ->name('inventory_management.requests.reject');

Route::post('/inventory_management/requests/{id}/return', [EquipmentController::class, 'markReturned'])
    ->name('inventory_management.requests.return');

Route::post('/inventory_management/requests/{id}/return', [EquipmentController::class, 'markReturned'])
    ->name('inventory_management.requests.return');

Route::get('/inventory_management/inventory_statistics', [EquipmentController::class, 'statistics'])
    ->name('inventory_management.inventory_statistics');

Route::get('/inventory_management/inventory_statistics/export', [EquipmentController::class, 'exportStatistics'])
    ->name('inventory_management.inventory_statistics.export');

Route::get('/kinemarket', [PostController::class, 'index'])->name('kinemarket');

Route::get('/marketplace_management', function () {
    return view('/marketplace_management.reports_management');
})->name('marketplace_management');

// Route::get('/access_logs', function () {
//     return view('access_logs');
// })->name('access_logs');

Route::get('/access_logs', [EquipmentController::class, 'accessLogs'])
    ->name('access_logs');

// Route::get('/facility_management', function () {
//     return view('facility_management');
// })->name('facility_management');

Route::post('/posts', [PostController::class, 'store'])->name('posts.store');
Route::delete('/posts/{post}', [PostController::class, 'destroy'])->name('posts.destroy');
Route::get('/posts', function () {
    return Post::with('user')->latest()->get();
});
Route::get('/posts/{id}', function ($id) {
    return \App\Models\Post::with('user')->findOrFail($id);
});
// Route::get('/posts', function () {
//     return Post::with('user')->latest()->get();
// });

Route::get('/posts', [PostController::class, 'getPosts'])->name('posts.list');

// Route::get('/reports', [UserReportController::class, 'index']);

// Route::post('/reports/{report}/resolve', [UserReportController::class, 'resolve']);
// Route::post('/reports/{report}/ban', [UserReportController::class, 'ban']);
Route::get('/reports', [UserReportController::class, 'index']);
Route::post('/reports', [UserReportController::class, 'store']);
Route::post('/reports/{report}/resolve', [UserReportController::class, 'resolve']);
Route::post('/reports/{report}/ban', [UserReportController::class, 'ban']);

Route::delete('/posts/{post}', [PostController::class, 'destroy']);

Route::get('/my_messages', [ChatController::class, 'index'])->name('my_messages');
Route::get('/chat/{chatId}', [ChatController::class, 'show'])->name('chat.show');
Route::post('/chats/open', [ChatController::class, 'openOrCreate'])
    ->name('chat.open');
Route::post('/messages', [MessageController::class, 'store']);
Route::get('/messages/{chatId}', function ($chatId) {
    return \App\Models\Message::with('user')
        ->where('chat_id', $chatId)
        ->orderBy('created_at')
        ->get();
});

require __DIR__ . '\saml2.php';
/*Route::get('/kinemercado/reportar_usuario', function () {
    return view('kinemercado');
})->name('kinemercado.reportar_usuario');*/

Route::get('/kinemercado/mensaje', function () {
    return view('kinemercado_mensaje');
})->name('kinemercado_mensaje');

Route::get('/facility_management', [FacilityCostController::class, 'index'])->name('facility_management')->middleware('role:admin super,admin facilidades');
Route::post('/facility/rates', [FacilityCostController::class, 'saveRates'])->name('facility.rates.save');
Route::post('/facility/events', [FacilityCostController::class, 'storeEvent'])->name('facility.events.store');
Route::delete('/facility/events/{item}', [FacilityCostController::class, 'destroy'])->name('facility.events.destroy');

Route::get('/facility_management/export/csv', [FacilityCostController::class, 'exportCsv'])->name('facility.export.csv');
Route::get('/facility_management/export/pdf', [FacilityCostController::class, 'exportPdf'])->name('facility.export.pdf');

Route::get('/my_profile', [EquipmentController::class, 'profile'])->name('my_profile');

Route::get('/mock-eventflow/events', [FacilityCostController::class, 'mockExternalEvents'])
    ->name('facility.mock.events');

Route::post('/facility/import-mock-events', [FacilityCostController::class, 'importMockEvents'])
    ->name('facility.import.mock');

require __DIR__.'\saml2.php';

Route::middleware('auth')->post('/marketplace/{post}/review', [ReviewController::class, 'store'])
    ->name('marketplace.review.store');

Route::post('/terms-and-conditions/update', [TermsController::class, 'update'])
    ->name('terms.update');

// Temporary routes until user tables are connected
Route::get('/test-email/request-approved', [EmailController::class, 'requestApproved']);
Route::get('/test-email/request-denied', [EmailController::class, 'requestDenied']);
Route::get('/test-email/user-banned', [EmailController::class, 'userBanned']);
Route::get('/test-email/unread-messages-reminder', [EmailController::class, 'unreadMessagesReminder']);
