<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */
    'saml2' => [
        'metadata' => 'https://idp.uprm.edu/saml2/idp/metadata.php',
        'sp_default_binding_method' => \LightSaml\SamlConstants::BINDING_SAML2_HTTP_POST,
        'attribute_map' => [
            'email' => 'email',
            'first_name' => 'real_name',
            'last_name' => 'surname',
            'upn' => 'email',
            'name' => 'real_name',
        ],
    ],


    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'eventflow' => [
        'api_key' => env('EVENTFLOW_API_KEY'),
        'export_url' => env('EVENTFLOW_EXPORT_URL', 'https://eventflow.uprm.edu/api/export-data'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

];
