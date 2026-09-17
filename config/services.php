<?php

if (! function_exists('google_oauth_env')) {
    function google_oauth_env(string $key, ?string $default = null): ?string
    {
        $value = env($key, $default);

        if (! is_string($value)) {
            return $default;
        }

        $value = trim($value, " \t\n\r\0\x0B\"'");

        return $value === '' ? null : $value;
    }
}

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Resend, Postmark, AWS, and more. This file provides the de facto
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

    'google' => [
        'client_id' => google_oauth_env('GOOGLE_CLIENT_ID'),
        'client_secret' => google_oauth_env('GOOGLE_CLIENT_SECRET'),
        'redirect' => google_oauth_env('GOOGLE_REDIRECT_URI') ?: google_oauth_env('GOOGLE_REDIRECT_URL') ?: '/auth/google/callback',
    ],

];
