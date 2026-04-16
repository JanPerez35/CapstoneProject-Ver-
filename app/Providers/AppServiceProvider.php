<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\View;
use Illuminate\Pagination\Paginator;
use App\Models\User;
use App\Models\Message;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrapFive();

        View::composer('*', function ($view) {

            $userId = auth()->id();

            $totalUnread = 0;

            if ($userId) {
                $totalUnread = Message::whereHas('chat', function ($q) use ($userId) {
                    $q->where('buyer_user_id', $userId)
                    ->orWhere('seller_user_id', $userId);
                })
                ->where('user_id', '!=', $userId)
                ->whereNull('read_at')
                ->count();
            }
            $superAdminuser = User::where('role', 'Admin Super')->get();
            $marketAdminuser = User::where('role', 'Admin Mercado')->get();
            $inventoryAdminuser = User::where('role', 'Admin Inventario')->get();

            $view->with('totalUnread', $totalUnread);
            $view->with('cart', session('cart', []));
            $view->with('superAdmin', $superAdminuser);
            $view->with('marketAdmin', $marketAdminuser);
            $view->with('inventoryAdmin', $inventoryAdminuser);
        });

        Event::listen(function (\SocialiteProviders\Manager\SocialiteWasCalled $event) {
            $event->extendSocialite('saml2', \SocialiteProviders\Saml2\Provider::class);
        });
    }
}