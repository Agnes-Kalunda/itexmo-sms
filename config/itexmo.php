<?php

return [
    'api_code' => function_exists('env') ? env('ITEXMO_API_CODE', '') : '',
    // Add any other configuration options here
];