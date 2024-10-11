<?php


return [
    // mandatoryy
    'email' => env('ITEXMO_EMAIL', ''),
    'password' => env('ITEXMO_PASSWORD', ''),
    'api_code' => env('ITEXMO_API_CODE', ''),

    // if not provided , default values will be used
    'sender_id' => env('ITEXMO_SENDER_ID', ''),
    'base_url' => env('ITEXMO_BASE_URL', 'https://api.itexmo.com/api/'),
    'max_message_length' => env('ITEXMO_MAX_MESSAGE_LENGTH', 160),
    'retry_attempts' => env('ITEXMO_RETRY_ATTEMPTS', 3),
    'retry_delay' => env('ITEXMO_RETRY_DELAY', 1000), 
];
