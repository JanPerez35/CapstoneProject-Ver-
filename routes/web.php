<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Models\ActivityLog;
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


/**
 *Authentication and main public routes
 *
 * Used for introducing the user to the website.
 *
 */


/**
 * Logout route, redirects to the origin page.
 */
Route::post('/logout', function () {
    $user = Auth::user();

    if ($user) {
        ActivityLog::create([
            'user_id'    => $user->id,
            'role'       => $user->role_label,
            'action'     => 'Cierre de sesión',
            'ip_address' => request()->ip(),
            'comment'    => "El usuario {$user->email} cerró sesión",
            'created_at' => now(),
        ]);
    }

    Auth::logout();

    request()->session()->invalidate();
    request()->session()->regenerateToken();

    return redirect('/');
})->name('logout');

/**
 * Login Page
 */
Route::get('/', function () {
    return view('login');
});

Route::get('/debug-user', function () {
    return [
        'auth_user' => auth()->user(),
        'session_id' => session()->getId(),
    ];
});

/**
 * SAML Authentication
 *
 * Loads external SAML authentication routes
 * from saml2.php file.
 */
require __DIR__ . '\saml2.php';


/**
 * Authenticated Routes (IMPORTANT)
 *
 * All routes inside this group REQUIRE a logged-in user.
 */
Route::middleware(['auth', 'user.active'])->group(function () {
    /**
     * Kinventory (User Inventory)
     *
     * Equipment browsing, borrowing, and cart management.
     */
    Route::get('/kinventory', [EquipmentController::class, 'kinventory'])
        ->name('kinventory');

Route::post('/cart/add', [LendingController::class, 'addToCart'])
        ->name('cart.add');

    Route::get('/cart', [LendingController::class, 'cart'])
        ->name('cart.index');

    Route::delete('/cart/remove/{id}', [LendingController::class, 'removeFromCart'])
        ->name('cart.remove');

    Route::post('/cart/checkout', [LendingController::class, 'checkoutCart'])
        ->name('cart.checkout');

    /**
     * User administration routes.
     *
     * Used for user search, role updates,
     * and account status changes.
     */
    Route::get('/search_user', [UserController::class, 'index'])
        ->name('search_user')->middleware('role:Super Administrador');

    Route::put('/users/{user}/role', [UserController::class, 'updateRole'])
        ->middleware('role:Super Administrador');

    Route::put('/users/{user}/status', [UserController::class, 'updateStatus'])
        ->name('users.updateStatus');


    /**
     * Inventory administration routes.
     *
     * Restricted to Super Admin and Inventory Admin users.
     * Includes inventory CRUD, borrow request review,
     * and statistics export by csv and pdf.
     */

    Route::get('/inventory_management', [EquipmentController::class, 'index'])
        ->name('inventory_management')
        ->middleware('role:Super Administrador,Administrador de Inventario');

    Route::post('/inventory_management', [EquipmentController::class, 'store'])
        ->name('inventory.store')
        ->middleware('role:Super Administrador,Administrador de Inventario');

    Route::put('/equipment/{id}', [EquipmentController::class, 'update'])
        ->name('equipment.update')
        ->middleware('role:Super Administrador,Administrador de Inventario');

    Route::delete('/equipment/{id}', [EquipmentController::class, 'destroy'])
        ->name('equipment.destroy')
        ->middleware('role:Super Administrador,Administrador de Inventario');

    Route::get('/inventory_management/borrows', [LendingController::class, 'borrows'])
        ->name('inventory_management.borrows')
        ->middleware('role:Super Administrador,Administrador de Inventario');

    Route::post('/inventory_management/requests/{id}/approve', [LendingController::class, 'approveRequest'])
        ->name('inventory_management.requests.approve')
        ->middleware('role:Super Administrador,Administrador de Inventario');

    Route::post('/inventory_management/requests/{id}/reject', [LendingController::class, 'rejectRequest'])
        ->name('inventory_management.requests.reject')
        ->middleware('role:Super Administrador,Administrador de Inventario');

    Route::post('/inventory_management/requests/{id}/return', [LendingController::class, 'markReturned'])
        ->name('inventory_management.requests.return')
        ->middleware('role:Super Administrador,Administrador de Inventario');

    Route::get('/inventory_management/inventory_statistics', [InventoryStatisticsController::class, 'statistics'])
        ->name('inventory_management.inventory_statistics')
        ->middleware('role:Super Administrador,Administrador de Inventario');

    Route::get('/inventory_management/inventory_statistics/export', [InventoryStatisticsController::class, 'exportStatistics'])
        ->name('inventory_management.inventory_statistics.export')
        ->middleware('role:Super Administrador,Administrador de Inventario');


    /**
     * Marketplace routes.
     *
     * Includes post listing, creation, deletion,
     * detail view, reviews, and moderation reports.
     */

    Route::get('/kinemarket', [PostController::class, 'index'])
        ->name('kinemarket');

    Route::post('/posts', [PostController::class, 'store'])
        ->name('posts.store');

    Route::delete('/posts/{post}', [PostController::class, 'destroy'])
        ->name('posts.destroy');

    Route::get('/posts', [PostController::class, 'getPosts'])
        ->name('posts.list');

    Route::get('/posts/{id}', [PostController::class, 'show'])
        ->name('posts.show');

    Route::post('/marketplace/{post}/review', [ReviewController::class, 'store'])
        ->name('marketplace.review.store');

    Route::get('/marketplace_management', [UserReportController::class, 'index'])
        ->name('marketplace_management')
        ->middleware('role:Super Administrador,Administrador de Mercado');

    Route::get('/reports', [UserReportController::class, 'index'])
        ->middleware('role:Super Administrador,Administrador de Mercado');

    Route::post('/reports', [UserReportController::class, 'store']);
    Route::get('/reports/data', [UserReportController::class, 'getReports']);
    Route::post('/reports/{report}/resolve', [UserReportController::class, 'resolve']);
    Route::post('/reports/{report}/ban', [UserReportController::class, 'ban']);
    Route::get('/user/post-limit', [PostController::class, 'getUserPostLimit']);


    /**
     * Messaging routes.
     *
     * Handles chats, chat creation,
     * message sending, and message retrieval.
     */

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

    /**
     * Facility management routes.
     *
     * Includes rates, events, classrooms,
     * imports, and exports for facility administration.
     */

    Route::get('/facility_management', [FacilityCostController::class, 'index'])
        ->name('facility_management')
        ->middleware('role:Super Administrador,Administrador de Instalaciones');

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

    Route::put('/facility/events/{item}', [FacilityCostController::class, 'updateEvent'])
        ->name('facility.events.update');

    Route::post('/facility/events/{item}/related', [FacilityCostController::class, 'storeRelatedEvent'])
        ->name('facility.events.related');

    Route::post('/facility/events/{item}/customize-days', [FacilityCostController::class, 'customizeDays'])
        ->name('facility.events.customize-days');

    Route::put('/facility/events/{item}/sub-event', [FacilityCostController::class, 'updateSubEvent'])
        ->name('facility.events.sub-event.update');

    Route::put('/facility/events/{item}/schedule', [FacilityCostController::class, 'updateEventSchedule'])
        ->name('facility.events.schedule.update');

    /**
     * Profile
     *
     * Showcases the individual user profile.
     */
    Route::get('/my_profile', [ProfileController::class, 'profile'])
        ->name('my_profile');

    /**
     * Access Logs
     *
     * Keeps logs of what users do in the platform (ONLY for Super Admin)
     */
    Route::get('/access_logs', [AccessLogController::class, 'index'])
        ->name('access_logs')
        ->middleware('role:Super Administrador');

    /**
     * Terms and Conditions
     *
     * Terms and conditions view for updates (ONLY for Super Admin)
     */
    Route::middleware('auth')->group(function () {
    Route::get('/terms-and-conditions', [TermsController::class, 'show'])->name('terms.show');
    Route::get('/terms-and-conditions/footer', [TermsController::class, 'showFromFooter'])->name('terms.footer');
    Route::post('/terms-and-conditions/accept', [TermsController::class, 'accept'])->name('terms.accept');
    Route::post('/admin/terms-and-conditions/update', [TermsController::class, 'update'])
    ->name('terms.update')
    ->middleware('role:Super Administrador');
});

    /**
     * Email and temporary testing routes.
     *
     * Includes generic email sending and
     * a temporary activity log test route.
     */
    Route::post('/send-email', [EmailController::class, 'sendEmail']);

    Route::get('/test-log-ipv6', function () {
        \App\Models\ActivityLog::create([
            'user_id' => 3,
            'role' => 'Super Administrador',
            'action' => 'IPv4 test',
            'ip_address' => '2345:0425:2CA1:0000:0000:0567:5673:23b5',
            'comment' => 'IPv4 test My IPv4',
        ]);

        return 'IPv6 test';
    });
});


/**
 * Temporary email testing routes.
 *
 * Used to manually trigger email notifications during development and testing.
 */
Route::get('/test-email/request-approved', [EmailController::class, 'requestApproved']);
Route::get('/test-email/request-denied', [EmailController::class, 'requestDenied']);
Route::get('/test-email/user-banned', [EmailController::class, 'userBanned']);
Route::get('/test-email/user-unbanned', [EmailController::class, 'userUnbanned']);

/**
 * Temporary concurrency testing route.
 *
 * Used for load testing post creation with tools like k6.
 */
Route::post('/test-concurrency', function () {
    \App\Models\Post::create([
        'user_id' => 1,
        'title' => 'k6 test',
        'description' => 'concurrency',
        'cost' => 10,
        'category' => 'test',
        'condition' => 'nuevo',
        'status' => 'Disponible',
    ]);

    return response()->json(['ok' => true]);
});


/**
 * Raw email testing route.
 *
 * Sends a simple test email using Laravel Mail.
 */
Route::get('/test-email', function () {
    Mail::raw('Esto es un test desde MAIKINE', function ($message) {
        $message->to('test@test.com')
            ->subject('TEST MAIKINE');
    });

    return 'Email enviado';
});
