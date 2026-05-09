<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        api: __DIR__.'/../routes/api.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {

        $middleware->alias([
            'user.active' => \App\Http\Middleware\EnsureUserIsStillActive::class,
            'role' => \App\Http\Middleware\RoleMiddleware::class,
            'terms.accepted' => \App\Http\Middleware\EnsureTermsAccepted::class,
        ]);

        $middleware->redirectGuestsTo(fn () => route('saml.login'));

        $middleware->validateCsrfTokens(except: [
            '/auth/callback',
            '/broadcasting/auth',
            '/test-concurrency',
        ]);


    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
