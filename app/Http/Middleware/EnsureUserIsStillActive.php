<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Class EnsureUserIsStillActive
 *
 * Middleware responsible for enforcing session validity and access control
 * based on the current state of the authenticated user.
 *
 * Responsibilities:
 * - ensuring the authenticated user still exists in the database
 * - forcing logout if the user account is blocked
 * - forcing logout if the user's role has changed since login
 *
 * This middleware guarantees that users cannot continue interacting with the
 * system if their permissions or account status have been modified during
 * an active session.
 */
class EnsureUserIsStillActive
{
    /**
     * Handle an incoming request.
     *
     * This method validates the current authenticated user on every request.
     *
     * Workflow:
     * - verify the user still exists in the database
     * - check if the user account is blocked
     * - compare the current role with the role stored at login
     *
     * If any of these validations fail, the user is immediately logged out,
     * their session is invalidated, and they are redirected to the login page
     * with an appropriate error message.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        \Log::info('MIDDLEWARE HIT - User: ' . auth()->id());

        if (Auth::check()) {
            $user = Auth::user()->fresh();

            /**
             * Case 1: User no longer exists in database
             */
            if (!$user) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect('/')->withErrors([
                    'auth' => 'Tu sesión ya no es válida.'
                ]);
            }

            /**
             * Case 2: User account has been blocked
             */
            if (in_array($user->status, ['Bloqueado', 'Blocked'])) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect('/')->withErrors([
                    'auth' => 'Tu cuenta ha sido bloqueada. No puedes acceder al sistema.'
                ]);
            }

            /**
             * Case 3: User role has changed since authentication
             *
             * The role stored in session represents the role at login time.
             * If the current database role differs, the session is no longer valid.
             */
            $authenticatedRole = $request->session()->get('authenticated_role');

            if ($authenticatedRole !== null && $authenticatedRole !== $user->role) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect('/')->withErrors([
                    'auth' => 'Tu rol cambió. Debes iniciar sesión nuevamente.'
                ]);
            }
        }

        return $next($request);
    }
}
