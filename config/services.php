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

    'brevo' => [
        'key' => env('BREVO_API_KEY'),
        'sender_email' => env('BREVO_SENDER_EMAIL'),
        'sender_name' => env('BREVO_SENDER_NAME', env('APP_NAME')),
        // Inboxes can't load images from localhost, so default to public/images/Enrollment logo.png (the logo the
        // in-app OTP screens use) served from GitHub, pinned to a commit so it never changes or disappears with a branch.
        // Not Icon.png: its semi-transparent glow shows as a ragged halo in mail clients and it is 850 KB.
        'logo_url' => env('BREVO_LOGO_URL') ?: 'https://raw.githubusercontent.com/kristan-web/LearningManagementSystem/ff5e868b5db6a78db26bb66446fb424be771bba1/public/images/Enrollment%20logo.png',
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
