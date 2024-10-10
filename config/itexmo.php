<?php

return [

    
//  This is your Itexmo API Code. This is used to authenticate your requests
// to the Itexmo SMS Gateway.
   
    'api_code' => function_exists('env') ? env('ITEXMO_API_CODE', '') : '',

    
    //  This is the default sender ID to be used when sending SMS messages.
    //  Leave it blank to use the default sender ID provided by Itexmo.
    
    'default_sender_id' => function_exists('env') ? env('ITEXMO_DEFAULT_SENDER_ID', '') : '',

 
    //  API Base URL
    'api_base_url' => 'https://api.itexmo.com/api/',


    // maximum length of a single SMS message. Messages longer than
    // this will be split into multiple messages.
    
    'max_message_length' => 160,

 
    // number of times to attempt resending a failed SMS before giving up.
    
    'retry_attempts' => 3,


    // no.of seconds to wait between retry attempts
    'retry_delay' => 5,
];