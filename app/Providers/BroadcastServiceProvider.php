<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Broadcast;

/**
 * Class BroadcastServiceProvider
 *
 * Service provider responsible for setting up broadcasting channels and routes.
 *
 * Responsibilities:
 * - registering broadcasting routes with appropriate middleware and prefix
 * - loading channel definitions from the channels.php file
 */
class BroadcastServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        Broadcast::routes([
            'middleware' => ['web'],
            'prefix' => 'broadcasting',
        ]);

        require base_path('routes/channels.php');
    }
}
