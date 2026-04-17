<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTermsAccepted
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return redirect()->route('saml.login');
        }

        $user = auth()->user();

        if (
            !$user->terms_accepted &&
            !$request->routeIs('terms.show') &&
            !$request->routeIs('terms.accept') &&
            !$request->routeIs('logout')
        ) {
            return redirect()->route('terms.show');
        }

        return $next($request);
    }
}