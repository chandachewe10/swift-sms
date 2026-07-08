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
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'meta_whatsapp' => [
        'app_id' => env('META_APP_ID', '1573778724345266'),
        'app_secret' => env('META_APP_SECRET'),
        'config_id' => env('META_CONFIG_ID', '866319909357587'),
        'graph_version' => env('META_GRAPH_VERSION', 'v25.0'),
        'redirect_uri' => env('META_EMBEDDED_SIGNUP_REDIRECT_URI'),
        'onboard_url' => env('META_EMBEDDED_SIGNUP_URL'),
        'verify_token' => env('META_WHATSAPP_VERIFY_TOKEN'),
    ],

];
