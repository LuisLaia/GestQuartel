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

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'preventech' => [
        'base_url' => env('PREVENTECH_BASE_URL'),
        'email' => env('PREVENTECH_EMAIL'),
        'nif' => env('PREVENTECH_NIF'),
        'api_key' => env('PREVENTECH_API_KEY'),
        'token' => env('PREVENTECH_TOKEN'),
    ],

    'cartrack' => [
        'base_url' => env('CARTRACK_BASE_URL'),
        'client_id' => env('CARTRACK_CLIENT_ID'),
        'client_secret' => env('CARTRACK_CLIENT_SECRET'),
        'username' => env('CARTRACK_USERNAME'),
        'password' => env('CARTRACK_PASSWORD'),
        'token' => env('CARTRACK_TOKEN'),
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
