<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class RoleMiddleware
 *
 * Middleware responsible for handling role-based access control.
 *
 * Responsibilities:
 * - verifying user authentication
 * - validating user roles against allowed roles
 * - preventing unauthorized access to protected routes
 */
class RoleMiddleware
{
    /**
     * Handles an incoming HTTP request.
     *
     * Parameters:
     * - request: current HTTP request instance
     * - next: closure to pass request to the next middleware/controller
     * - roles: list of allowed roles for the route
     *
     * Flow:
     * 1. Checks if the user is authenticated
     *    - redirects to login if not authenticated
     *
     * 2. Retrieves authenticated user
     *
     * 3. Validates user role
     *    - compares user's role against allowed roles
     *    - aborts with 403 if unauthorized
     *
     * 4. Allows request to proceed if validation passes
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!auth()->check()) {
            return redirect()->route('saml.login');
        }

        $user = auth()->user();

        if (!in_array($user->role, $roles)) {
            abort(404);        }

        return $next($request);
    }
}
