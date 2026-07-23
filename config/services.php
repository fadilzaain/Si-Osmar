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

    // API eksternal SIKAWAN RSUD Jombang — sumber data Bezetting SDM.
     // API eksternal SIKAWAN RSUD Jombang — sumber data Bezetting SDM.
    'sikawan' => [
        'base_url' => env('SIKAWAN_BASE_URL', 'https://new-sikawan.rsudjombang.id'),
        'bezetting_endpoint' => env('SIKAWAN_BEZETTING_ENDPOINT', '/api-monitoring-sdm'),
        'dokumen_endpoint' => env('SIKAWAN_DOKUMEN_ENDPOINT', '/api-monitoring-berlaku-dokumen'),
        'cuti_endpoint' => env('SIKAWAN_CUTI_ENDPOINT', '/api-monitoring-sisa-cuti'),
        'evkin_endpoint' => env('SIKAWAN_EVKIN_ENDPOINT', '/api-monitoring-evkin'),
        'storage_url' => env('SIKAWAN_STORAGE_URL'), // null = fallback ke base_url, lihat MonitoringDokumenService::getFileUrl()
        'timeout' => env('SIKAWAN_TIMEOUT', 10),
        'cache_ttl' => env('SIKAWAN_CACHE_TTL', 900),
        // Verifikasi SSL certificate API SI KAWAN. Default true (aman).
        // Set SIKAWAN_VERIFY_SSL=false di .env HANYA kalau ketauan dari
        // halaman diagnostic errornya soal SSL certificate (cURL error 60),
        // dan itu pun sebaiknya sementara sambil nunggu CA bundle server
        // hosting dibenerin.
        'verify_ssl' => env('SIKAWAN_VERIFY_SSL', true),
    ],

];
