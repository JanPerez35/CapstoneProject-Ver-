<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Livewire\Volt\Volt;

/**
 * Class VoltServiceProvider
 *
 * Service provider responsible for setting up Livewire Volt view paths.
 *
 * Responsibilities:
 * - registering additional view paths for Livewire Volt components
 */
class VoltServiceProvider extends ServiceProvider
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
        Volt::mount([
            config('livewire.view_path', resource_path('views/livewire')),
            resource_path('views/pages'),
        ]);
    }
}
