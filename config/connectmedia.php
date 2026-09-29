<?php

return [
    // 64-character API key from dashboard.connectmedia.co.ke (Profile, then API keys).
    'api_key' => env('CONNECTMEDIA_API_KEY'),

    // Default sender ID (11 characters or fewer). Leave empty to use your account default.
    'sender' => env('CONNECTMEDIA_SENDER'),

    'base_url' => env('CONNECTMEDIA_BASE_URL', 'https://dashboard.connectmedia.co.ke/api.php'),

    // Request timeout in seconds.
    'timeout' => (int) env('CONNECTMEDIA_TIMEOUT', 30),
];
