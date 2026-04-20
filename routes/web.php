<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

use App\Http\Controllers\EmailController;
use App\Http\Controllers\EquipmentController;
use App\Http\Controllers\LendingController;
use App\Http\Controllers\InventoryStatisticsController;
use App\Http\Controllers\AccessLogController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\FacilityCostController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\TermsController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\UserReportController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ReviewController;

Route::post('/logout', function () {
    Auth::logout();

    request()->session()->invalidate();
    request()->session()->regenerateToken();

    return redirect('/');
})->name('logout');

Route::get('/', function () {
    return view('login');
});

Route::get('/welcome', function () {
    return view('welcome');
});

require __DIR__ . '\saml2.php';

Route::middleware('auth')->group(function () {

    Route::get('/kinventory', [EquipmentController::class, 'kinventory'])
        ->name('kinventory');

    Route::post('/kinventory/borrow', [LendingController::class, 'borrow'])
        ->name('kinventory.borrow');

    Route::post('/cart/add', [LendingController::class, 'addToCart'])
        ->name('cart.add');

    Route::get('/cart', [LendingController::class, 'cart'])
        ->name('cart.index');

    Route::delete('/cart/remove/{id}', [LendingController::class, 'removeFromCart'])
        ->name('cart.remove');

    Route::post('/cart/checkout', [LendingController::class, 'checkoutCart'])
        ->name('cart.checkout');

    Route::get('/search_user', [UserController::class, 'index'])
        ->name('search_user');

    Route::put('/users/{user}/role', [UserController::class, 'updateRole']);

    Route::put('/users/{user}/status', [UserController::class, 'updateStatus'])
        ->name('users.updateStatus');

    Route::get('/inventory_management', [EquipmentController::class, 'index'])
        ->name('inventory_management')
        ->middleware('role:Admin Super,Admin Inventario');

    Route::post('/inventory_management', [EquipmentController::class, 'store'])
        ->name('inventory.store')
        ->middleware('role:Admin Super,Admin Inventario');

    Route::put('/equipment/{id}', [EquipmentController::class, 'update'])
        ->name('equipment.update')
        ->middleware('role:Admin Super,Admin Inventario');

    Route::delete('/equipment/{id}', [EquipmentController::class, 'destroy'])
        ->name('equipment.destroy')
        ->middleware('role:Admin Super,Admin Inventario');

    Route::get('/inventory_management/borrows', [LendingController::class, 'borrows'])
        ->name('inventory_management.borrows')
        ->middleware('role:Admin Super,Admin Inventario');

    Route::post('/inventory_management/requests/{id}/approve', [LendingController::class, 'approveRequest'])
        ->name('inventory_management.requests.approve')
        ->middleware('role:Admin Super,Admin Inventario');

    Route::post('/inventory_management/requests/{id}/reject', [LendingController::class, 'rejectRequest'])
        ->name('inventory_management.requests.reject')
        ->middleware('role:Admin Super,Admin Inventario');

    Route::post('/inventory_management/requests/{id}/return', [LendingController::class, 'markReturned'])
        ->name('inventory_management.requests.return')
        ->middleware('role:Admin Super,Admin Inventario');

    Route::get('/inventory_management/inventory_statistics', [InventoryStatisticsController::class, 'statistics'])
        ->name('inventory_management.inventory_statistics')
        ->middleware('role:Admin Super,Admin Inventario');

    Route::get('/inventory_management/inventory_statistics/export', [InventoryStatisticsController::class, 'exportStatistics'])
        ->name('inventory_management.inventory_statistics.export')
        ->middleware('role:Admin Super,Admin Inventario');

    Route::get('/kinemarket', [PostController::class, 'index'])
        ->name('kinemarket');

    Route::post('/posts', [PostController::class, 'store'])
        ->name('posts.store');

    Route::delete('/posts/{post}', [PostController::class, 'destroy'])
        ->name('posts.destroy');

    Route::get('/posts/{id}', [PostController::class, 'show'])
        ->name('posts.show');

    Route::get('/posts', [PostController::class, 'getPosts'])
        ->name('posts.list');

    Route::post('/marketplace/{post}/review', [ReviewController::class, 'store'])
        ->name('marketplace.review.store');

    Route::get('/marketplace_management', [UserReportController::class, 'index'])
        ->name('marketplace_management')
        ->middleware('role:Admin Super,Admin Mercado');

    Route::get('/reports', [UserReportController::class, 'index']);
    Route::post('/reports', [UserReportController::class, 'store']);
    Route::get('/reports/data', [UserReportController::class, 'getReports']);
    Route::post('/reports/{report}/resolve', [UserReportController::class, 'resolve']);
    Route::post('/reports/{report}/ban', [UserReportController::class, 'ban']);

    Route::get('/my_messages', [ChatController::class, 'index'])
        ->name('my_messages');

    Route::get('/chat/{chatId}', [ChatController::class, 'show'])
        ->name('chat.show');

    Route::post('/chats/open', [ChatController::class, 'openOrCreate'])
        ->name('chat.open');

    Route::post('/messages', [MessageController::class, 'store']);
    Route::get('/messages/{chatId}', [MessageController::class, 'getMessages']);

    Route::get('/kinemercado/mensaje', function () {
        return view('kinemercado_mensaje');
    })->name('kinemercado_mensaje');

    Route::get('/facility_management', [FacilityCostController::class, 'index'])
        ->name('facility_management')
        ->middleware('role:Admin Super,Admin Facilidades');

    Route::post('/facility/rates', [FacilityCostController::class, 'saveRates'])
        ->name('facility.rates.save');

    Route::post('/facility/events', [FacilityCostController::class, 'storeEvent'])
        ->name('facility.events.store');

    Route::delete('/facility/events/{item}', [FacilityCostController::class, 'destroy'])
        ->name('facility.events.destroy');

    Route::post('/facility/classrooms', [FacilityCostController::class, 'storeClassroom'])
        ->name('facility.classrooms.store');

    Route::delete('/facility/classrooms', [FacilityCostController::class, 'destroyClassrooms'])
        ->name('facility.classrooms.destroy');

    Route::get('/facility_management/export/csv', [FacilityCostController::class, 'exportCsv'])
        ->name('facility.export.csv');

    Route::get('/facility_management/export/pdf', [FacilityCostController::class, 'exportPdf'])
        ->name('facility.export.pdf');

    Route::get('/mock-eventflow/events', [FacilityCostController::class, 'mockExternalEvents'])
        ->name('facility.mock.events');

    Route::post('/facility/import-mock-events', [FacilityCostController::class, 'importMockEvents'])
        ->name('facility.import.mock');

    Route::get('/my_profile', [ProfileController::class, 'profile'])
        ->name('my_profile');

    Route::get('/access_logs', [AccessLogController::class, 'index'])
        ->name('access_logs')
        ->middleware('role:Admin Super');

    Route::middleware('auth')->group(function () {
    Route::get('/terms-and-conditions', [TermsController::class, 'show'])->name('terms.show');
    Route::post('/terms-and-conditions/accept', [TermsController::class, 'accept'])->name('terms.accept');
    Route::post('/admin/terms-and-conditions/update', [TermsController::class, 'update'])
    ->name('terms.update')
    ->middleware('role:Admin Super');
});

    Route::post('/send-email', [EmailController::class, 'sendEmail']);

    Route::get('/test-log-ipv6', function () {
        \App\Models\ActivityLog::create([
            'user_id' => 3,
            'role' => 'Admin Super',
            'action' => 'IPv4 test',
            'ip_address' => '2345:0425:2CA1:0000:0000:0567:5673:23b5',
            'comment' => 'IPv4 test My IPv4',
        ]);

        return 'IPv6 test';
    });
});

/*
| Temporary test routes
*/

Route::get('/test-email/request-approved', [EmailController::class, 'requestApproved']);
Route::get('/test-email/request-denied', [EmailController::class, 'requestDenied']);
Route::get('/test-email/user-banned', [EmailController::class, 'userBanned']);
Route::get('/test-email/user-unbanned', [EmailController::class, 'userUnbanned']);

Route::get('/test-email', function () {
    Mail::raw('Esto es un test desde MAIKINE', function ($message) {
        $message->to('test@test.com')
            ->subject('TEST MAIKINE');
    });

    return 'Email enviado';
});