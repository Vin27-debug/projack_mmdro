<?php

return [
    'muniresq' => [
        'speed' => [
            'yellow_over_limit_percent' => (float) env('MUNIRESQ_SPEED_YELLOW_OVER_LIMIT_PERCENT', 10),
            'red_over_limit_percent' => (float) env('MUNIRESQ_SPEED_RED_OVER_LIMIT_PERCENT', 20),
            'road_limits_kmh' => json_decode(env('MUNIRESQ_ROAD_SPEED_LIMITS', '{}'), true) ?: [],
        ],
    ],

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
