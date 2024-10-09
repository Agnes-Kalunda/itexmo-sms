<?php

require 'vendor/autoload.php'; 

use Agnes\ItexmoSms\ItexmoSmsServiceProvider;


$config = include 'config/itexmo.php'; 

// Instantiate the service provider
$serviceProvider = new ItexmoSmsServiceProvider($config);

// Get ItexmoSms instance
$itexmoSms = $serviceProvider->getItexmoSms();

// Use instance to send an SMS
try {
    $response = $itexmoSms->broadcast(['recipient_number'], 'Your message here.');
    print_r($response); 
} catch (\RuntimeException $e) {
    echo 'Error: ' . $e->getMessage(); 
}
