<?php

use Illuminate\Support\Facades\Route;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\ActivityLog;


/**
* SAML Login Route
*
* Redirects the user to the Identity Provider (IdP) using Socialite.
* This starts the SAML authentication flow.
*/
Route::get('/auth/saml/login', function () {
    return Socialite::driver('saml2')->redirect();
})->name("saml.login");


/**
*
* SAML Callback Route
*
* Handles the response from the Identity Provider after login.
*
* Workflow:
* - retrieve user data from SAML response
* - create or update the user in the database
* - log the user into the application
* - enforce terms and conditions acceptance before granting access
*/
Route::any('/auth/callback', function () {

    //Retrieve authenticated user information from SAML provider
    $saml = Socialite::driver('saml2')->stateless()->user();

    /**
     * Create or update user record based on email.
     * Ensures users can log in repeatedly without duplication.
     */
    $user = User::updateOrCreate([
        'email' => $saml->getEmail(),
    ], [
        'name' => $saml->first_name . ' ' . $saml->last_name,
        'first_name' => $saml->first_name,
        'last_name' => $saml->last_name,
        'auth_type' => 'saml2',

        // Placeholder password since authentication is handled by SAML
        'password' => 'thisisatest',
    ]);


    // Log user into Laravel session
    Auth::login($user);
    session(['authenticated_role' => $user->role]);

    ActivityLog::create([
        'user_id'    => $user->id,
        'role'       => $user->role_label,
        'action'     => 'Inicio de sesión',
        'ip_address' => request()->ip(),
        'comment'    => "El usuario {$user->email} inició sesión mediante SAML",
        'created_at' => now(),
    ]);

    /**
     * Enforce Terms & Conditions acceptance.
     * Redirects user if they have not accepted yet.
     */
    if (!$user->terms_accepted) {
        return redirect()->route('terms.show');
    }

    // Redirect authenticated user to main system page
    return redirect()->route('kinemarket');
})->name('saml.callback');

/**
*
* SAML Metadata Route
*
* Provides Service Provider metadata required by the Identity Provider.
* Used during SAML configuration and integration setup.
*/
Route::get('/auth/saml/metadata', function () {
    return Socialite::driver('saml2')->getServiceProviderMetadata();
})->name("saml.metadata");
