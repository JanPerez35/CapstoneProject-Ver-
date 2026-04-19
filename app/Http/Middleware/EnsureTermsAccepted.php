<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class EnsureTermsAccepted
 *
 * Handles terms and conditions validation within the application.
 *
 * Responsibilities:
 * - verifying that the user is authenticated
 * - ensuring the user has accepted the terms and conditions
 * - redirecting users who have not accepted the terms
 */
class EnsureTermsAccepted
{
    /**
     * Handles an incoming request.
     *
     * Checks if the user is authenticated. If not, redirects to login.
     * Then verifies if the user has accepted the terms and conditions.
     *
     * If the terms are not accepted, the user is redirected to the
     * terms view unless they are already accessing:
     * - terms.show
     * - terms.accept
     * - logout
     *
     * Otherwise, the request continues normally.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Redirect unauthenticated users to login
        if (!auth()->check()) {
            return redirect()->route('saml.login');
        }

        $user = auth()->user();

        // Redirect users who have not accepted terms
        if (
            !$user->terms_accepted &&
            !$request->routeIs('terms.show') &&
            !$request->routeIs('terms.accept') &&
            !$request->routeIs('logout')
        ) {
            return redirect()->route('terms.show');
        }

        // Continue request
        return $next($request);
    }
}